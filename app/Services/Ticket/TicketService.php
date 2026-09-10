<?php

namespace App\Services\Ticket;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\AuditLog;
use App\Models\OperatingHour;
use App\Models\Ticket;
use App\Models\TiketNote;
use App\Models\User;
use App\Services\Audit\AuditRecorder;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * M10 domain service (SDD §2, principle 2): every business rule of the
 * ticket life cycle — status transitions, SLA computation and pause
 * handling — lives here and nowhere else. Controllers only translate
 * HTTP into these calls.
 */
class TicketService
{
    private const MAX_NUMBER_RETRIES = 5;

    public function __construct(
        private readonly AuditRecorder $audit,
        private readonly TicketNotifier $notifier,
        private readonly TicketNumberGenerator $numbers,
    ) {}

    // ------------------------------------------------------------------
    // Opening (FR-TKT-01 to FR-TKT-07)
    // ------------------------------------------------------------------

    /**
     * Open a ticket. $data must already be validated by StoreTicketRequest.
     * E-mail failures after this point never undo the ticket (UC-10, 11a).
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $files
     */
    public function buka(User $pelapor, array $data, array $files = []): Ticket
    {
        $dibuka = now();
        $calculator = new SlaCalculator($this->calendar());
        $priority = TicketPriority::from($data['keutamaan']);

        $sasaran = $calculator->kiraSasaran(
            $dibuka,
            $this->minitTindakBalas($priority),
            $this->minitPemulihan($priority),
        );

        $ticket = null;
        $lastException = null;

        // The unique index on no_tiket resolves the race between two
        // simultaneous openings; draw a fresh number and retry.
        for ($attempt = 0; $attempt < self::MAX_NUMBER_RETRIES; $attempt++) {
            try {
                $ticket = Ticket::create([
                    ...$data,
                    'no_tiket' => $this->numbers->next(),
                    'pelapor_id' => $pelapor->id,
                    'status' => TicketStatus::Baharu,
                    'masa_dibuka' => $dibuka,
                    'sasaran_tindak_balas' => $sasaran['sasaran_tindak_balas'],
                    'sasaran_pemulihan' => $sasaran['sasaran_pemulihan'],
                    'minit_jeda_sla' => 0,
                    'penutupan_automatik' => false,
                    'lampiran' => $this->simpanLampiran($files),
                ]);

                break;
            } catch (UniqueConstraintViolationException $exception) {
                $lastException = $exception;
            }
        }

        if ($ticket === null) {
            throw $lastException ?? new \RuntimeException('Gagal menjana nombor tiket.');
        }

        $this->audit->record($pelapor, 'ticket.created', $ticket, after: $ticket->refresh()->only([
            'no_tiket', 'keutamaan', 'status', 'sasaran_pemulihan',
        ]));

        $this->notifier->hantar(
            $pelapor,
            $ticket,
            'tiket.dibuka',
            "Tiket {$ticket->no_tiket} telah diterima",
            'Aduan anda telah diterima. Anggaran masa penyelesaian: '
                .$sasaran['sasaran_pemulihan']->format('d/m/Y H:i').'.',
            [
                'nama_pelapor' => $pelapor->name,
                'nombor_tiket' => $ticket->no_tiket,
                'kategori_masalah' => $ticket->kategori_masalah,
                'anggaran_selesai' => $sasaran['sasaran_pemulihan']->format('d/m/Y H:i'),
                'nama_aset' => '(tidak dinyatakan)',
            ],
        );

        $this->maklumPenyelia(
            $ticket,
            'tiket.baharu',
            "Tiket baharu keutamaan {$priority->label()} menunggu agihan.",
        );

        return $ticket;
    }

    // ------------------------------------------------------------------
    // Assignment (FR-TKT-09, FR-TKT-10)
    // ------------------------------------------------------------------

    public function agih(Ticket $ticket, User $juruteknik, User $actor): Ticket
    {
        $this->perluStatus($ticket, [TicketStatus::Baharu, TicketStatus::Diagih], 'agih');

        $ticket->update([
            'juruteknik_id' => $juruteknik->id,
            'status' => TicketStatus::Diagih,
        ]);

        $this->audit->record($actor, 'ticket.assigned', $ticket, context: [
            'juruteknik_id' => $juruteknik->id,
        ]);

        $this->notifier->hantar(
            $juruteknik,
            $ticket,
            'tiket.diagih',
            "Tiket {$ticket->no_tiket} diagihkan kepada anda",
            'Tiket baharu telah diagihkan kepada anda untuk tindakan.',
            ['nombor_tiket' => $ticket->no_tiket, 'nama_juruteknik' => $juruteknik->name],
        );

        return $ticket;
    }

    /**
     * Bulk assignment (UC-11, 4a). Returns how many moved and how many were
     * skipped because their status no longer accepts a technician.
     *
     * @param  array<int, int>  $ids
     * @return array{berjaya: int, gagal: int}
     */
    public function agihPukal(array $ids, User $juruteknik, User $actor): array
    {
        $berjaya = 0;
        $gagal = 0;

        foreach (Ticket::query()->whereIn('id', $ids)->get() as $ticket) {
            try {
                $this->agih($ticket, $juruteknik, $actor);
                $berjaya++;
            } catch (ValidationException) {
                $gagal++;
            }
        }

        return ['berjaya' => $berjaya, 'gagal' => $gagal];
    }

    // ------------------------------------------------------------------
    // Priority (FR-TKT-08, BRL-11)
    // ------------------------------------------------------------------

    public function ubahKeutamaan(Ticket $ticket, TicketPriority $baru, string $sebab, User $actor): Ticket
    {
        $this->perluTerbuka($ticket, 'ubah keutamaan');

        $lama = $ticket->keutamaan;
        $calculator = new SlaCalculator($this->calendar());

        $sasaran = $calculator->kiraSasaran(
            $ticket->masa_dibuka,
            $this->minitTindakBalas($baru),
            $this->minitPemulihan($baru),
        );

        $ticket->update([
            'keutamaan' => $baru,
            'sebab_keutamaan' => $sebab,
            'sasaran_tindak_balas' => $sasaran['sasaran_tindak_balas'],
            'sasaran_pemulihan' => $sasaran['sasaran_pemulihan'],
            'amaran_80_dihantar_pada' => null,
            'amaran_100_dihantar_pada' => null,
        ]);

        $this->audit->record($actor, 'ticket.priority_changed', $ticket, before: [
            'keutamaan' => $lama->value,
        ], after: [
            'keutamaan' => $baru->value,
        ], context: ['sebab' => $sebab]);

        if ($ticket->juruteknik !== null) {
            $this->notifier->hantar(
                $ticket->juruteknik,
                $ticket,
                'tiket.keutamaan_diubah',
                "Keutamaan {$ticket->no_tiket} diubah kepada {$baru->label()}",
                'Sasaran SLA telah dikira semula.',
                ['nombor_tiket' => $ticket->no_tiket],
            );
        }

        return $ticket;
    }

    // ------------------------------------------------------------------
    // Technician work (FR-TKT-12, FR-TKT-13, UC-12)
    // ------------------------------------------------------------------

    public function mulaKerja(Ticket $ticket, User $actor): Ticket
    {
        $this->perluStatus($ticket, [TicketStatus::Diagih], 'mula kerja');

        $ticket->update([
            'status' => TicketStatus::DalamTindakan,
            'masa_tindak_balas_pertama' => $ticket->masa_tindak_balas_pertama ?? now(),
        ]);

        $this->audit->record($actor, 'ticket.work_started', $ticket);

        return $ticket;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function simpanKerja(Ticket $ticket, array $data, User $actor): Ticket
    {
        $this->perluStatus($ticket, [TicketStatus::DalamTindakan, TicketStatus::MenungguVendor], 'mereka kerja');

        $ticket->update([
            'diagnosis' => $data['diagnosis'] ?? $ticket->diagnosis,
            'tindakan' => $data['tindakan'] ?? $ticket->tindakan,
            'kos_pembaikan' => $data['kos_pembaikan'] ?? $ticket->kos_pembaikan,
            'masa_kerja_minit' => $data['masa_kerja_minit'] ?? $ticket->masa_kerja_minit,
        ]);

        $this->audit->record($actor, 'ticket.work_updated', $ticket, after: $ticket->refresh()->only([
            'diagnosis', 'tindakan', 'kos_pembaikan', 'masa_kerja_minit',
        ]));

        return $ticket;
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    public function catatNota(Ticket $ticket, User $actor, string $catatan, bool $bolehDilihatPelapor, array $files = []): TiketNote
    {
        $note = $ticket->notes()->create([
            'user_id' => $actor->id,
            'catatan' => $catatan,
            'boleh_dilihat_pelapor' => $bolehDilihatPelapor,
            'lampiran' => $this->simpanLampiran($files),
        ]);

        $this->audit->record($actor, 'ticket.note_added', $ticket, context: [
            'note_id' => $note->id,
            'boleh_dilihat_pelapor' => $bolehDilihatPelapor,
        ]);

        if ($bolehDilihatPelapor && $ticket->pelapor_id !== $actor->id) {
            $this->notifier->hantar(
                $ticket->pelapor,
                $ticket,
                'tiket.kemas_kini',
                "Kemas kini tiket {$ticket->no_tiket}",
                'Terdapat catatan kemajuan baharu pada aduan anda.',
                ['nombor_tiket' => $ticket->no_tiket],
            );
        }

        return $note;
    }

    // ------------------------------------------------------------------
    // Vendor referral (FR-TKT-15) — SLA pause
    // ------------------------------------------------------------------

    public function rujukVendor(Ticket $ticket, string $noRujukan, string $namaVendor, User $actor): Ticket
    {
        $this->perluStatus($ticket, [TicketStatus::DalamTindakan], 'rujuk kepada vendor');

        $ticket->update([
            'status' => TicketStatus::MenungguVendor,
            'no_rujukan_vendor' => $noRujukan,
            'vendor_nama' => $namaVendor,
            'jeda_mula_pada' => now(),
        ]);

        $this->audit->record($actor, 'ticket.vendor_referral', $ticket, after: [
            'no_rujukan_vendor' => $noRujukan,
        ], context: ['vendor_nama' => $namaVendor]);

        $this->maklumPenyelia($ticket, 'tiket.rujuk_vendor', "Tiket {$ticket->no_tiket} dirujuk kepada vendor.");

        return $ticket;
    }

    /**
     * Vendor finished: the SLA clock resumes and the paused minutes shift the
     * targets forward so the pause is truly excluded from the calculation.
     */
    public function sambungSelepasVendor(Ticket $ticket, User $actor): Ticket
    {
        $this->perluStatus($ticket, [TicketStatus::MenungguVendor], 'sambung selepas vendor');

        $this->sambungSemulaSla($ticket);

        $ticket->update(['status' => TicketStatus::DalamTindakan]);

        $this->audit->record($actor, 'ticket.vendor_resumed', $ticket);

        return $ticket;
    }

    // ------------------------------------------------------------------
    // Completion and closure (FR-TKT-17 to FR-TKT-19, BRL-10)
    // ------------------------------------------------------------------

    public function tandaSelesai(Ticket $ticket, User $actor): Ticket
    {
        $this->perluStatus($ticket, [TicketStatus::DalamTindakan], 'tanda selesai');

        $ticket->update([
            'status' => TicketStatus::MenungguPengesahan,
            'masa_kerja_selesai' => now(),
        ]);

        $this->audit->record($actor, 'ticket.completed', $ticket);

        $this->notifier->hantar(
            $ticket->pelapor,
            $ticket,
            'tiket.selesai',
            "Tiket {$ticket->no_tiket} menunggu pengesahan anda",
            'Juruteknik telah menanda kerja selesai. Sila sahkan sama ada masalah benar-benar selesai.',
            ['nombor_tiket' => $ticket->no_tiket],
        );

        return $ticket;
    }

    public function sahkanPenutupan(Ticket $ticket, User $actor): Ticket
    {
        $this->perluStatus($ticket, [TicketStatus::MenungguPengesahan], 'sahkan penutupan');

        $ticket->update([
            'status' => TicketStatus::Ditutup,
            'masa_ditutup' => now(),
            'sla_dipatuhi' => $this->slaDipatuhi($ticket, $ticket->masa_kerja_selesai ?? now()),
        ]);

        $this->audit->record($actor, 'ticket.confirmed', $ticket, after: [
            'status' => TicketStatus::Ditutup->value,
            'sla_dipatuhi' => $ticket->sla_dipatuhi,
        ]);

        return $ticket;
    }

    public function bukaSemula(Ticket $ticket, User $actor, string $alasan): Ticket
    {
        $this->perluStatus($ticket, [TicketStatus::MenungguPengesahan], 'buka semula');

        if (trim($alasan) === '') {
            throw ValidationException::withMessages([
                'alasan' => 'Nyatakan masalah yang masih berlaku supaya juruteknik tahu apa yang perlu diteruskan.',
            ]);
        }

        $this->sambungSemulaSla($ticket, $ticket->masa_kerja_selesai);

        $ticket->update(['status' => TicketStatus::DalamTindakan]);

        $ticket->notes()->create([
            'user_id' => $actor->id,
            'catatan' => 'Tiket dibuka semula oleh pelapor: '.$alasan,
            'boleh_dilihat_pelapor' => true,
        ]);

        $this->audit->record($actor, 'ticket.reopened', $ticket, context: ['alasan' => $alasan]);

        if ($ticket->juruteknik !== null) {
            $this->notifier->hantar(
                $ticket->juruteknik,
                $ticket,
                'tiket.buka_semula',
                "Tiket {$ticket->no_tiket} dibuka semula",
                'Pelapor melaporkan masalah masih berlaku. Sila teruskan kerja.',
                ['nombor_tiket' => $ticket->no_tiket],
            );
        }

        $this->maklumPenyelia($ticket, 'tiket.buka_semula', "Tiket {$ticket->no_tiket} dibuka semula oleh pelapor.");

        return $ticket;
    }

    /**
     * Close without reporter confirmation after three working days of
     * silence (FR-TKT-18, UC-13, 1a). Runs as the system actor; the audit
     * row carries no user, which AuditLog allows. Idempotent: it only ever
     * touches tickets still waiting for confirmation (NFR-A07).
     */
    public function tutupAutomatik(): int
    {
        $calendar = $this->calendar();
        $tutup = 0;

        Ticket::query()
            ->where('status', TicketStatus::MenungguPengesahan->value)
            ->chunkById(100, function (Collection $tickets) use ($calendar, &$tutup): void {
                foreach ($tickets as $ticket) {
                    $deadline = $calendar->addWorkingMinutes(
                        $ticket->masa_kerja_selesai ?? $ticket->masa_dibuka,
                        3 * $this->hariKerjaMinit(),
                    );

                    if (now()->lt($deadline)) {
                        continue;
                    }

                    $ticket->update([
                        'status' => TicketStatus::Ditutup,
                        'masa_ditutup' => now(),
                        'sla_dipatuhi' => $this->slaDipatuhi($ticket, $ticket->masa_kerja_selesai ?? now()),
                        'penutupan_automatik' => true,
                    ]);

                    AuditLog::create([
                        'user_id' => null,
                        'action' => 'ticket.auto_closed',
                        'status' => 'success',
                        'record_type' => 'ticket',
                        'record_id' => $ticket->id,
                        'metadata' => ['no_tiket' => $ticket->no_tiket],
                    ]);

                    $tutup++;
                }
            });

        return $tutup;
    }

    /**
     * Withdraw an invalid report (status chart: Baharu → Dibatalkan). Only
     * penyelia may do this, enforced by route permission.
     */
    public function batal(Ticket $ticket, string $sebab, User $actor): Ticket
    {
        $this->perluStatus($ticket, [TicketStatus::Baharu], 'batalkan');

        if (trim($sebab) === '') {
            throw ValidationException::withMessages([
                'sebab_batal' => 'Sebab pembatalan wajib diisi.',
            ]);
        }

        $ticket->update([
            'status' => TicketStatus::Dibatalkan,
            'sebab_batal' => $sebab,
        ]);

        $this->audit->record($actor, 'ticket.cancelled', $ticket, after: [
            'status' => TicketStatus::Dibatalkan->value,
        ], context: ['sebab' => $sebab]);

        $this->notifier->hantar(
            $ticket->pelapor,
            $ticket,
            'tiket.dibatalkan',
            "Tiket {$ticket->no_tiket} dibatalkan",
            'Aduan anda dibatalkan oleh penyelia ICT dengan sebab: '.$sebab,
            ['nombor_tiket' => $ticket->no_tiket],
        );

        return $ticket;
    }

    // ------------------------------------------------------------------
    // SLA alerts (FR-TKT-20, UC-18)
    // ------------------------------------------------------------------

    /**
     * @return array{amar_80: int, amar_100: int, dilanggar: int}
     */
    public function semakAmaranSla(): array
    {
        $calculator = new SlaCalculator($this->calendar());
        $amar80 = 0;
        $amar100 = 0;

        Ticket::query()
            ->open()
            ->whereNotNull('juruteknik_id')
            ->with('juruteknik')
            ->chunkById(100, function (Collection $tickets) use ($calculator, &$amar80, &$amar100): void {
                foreach ($tickets as $ticket) {
                    // The SLA clock is paused while waiting for a vendor, so
                    // the alert sweep must skip those tickets entirely.
                    if ($ticket->status === TicketStatus::MenungguVendor) {
                        continue;
                    }

                    $peratus = $calculator->peratusanTerpakai(
                        $ticket->masa_dibuka,
                        $ticket->sasaran_pemulihan,
                        $ticket->minit_jeda_sla,
                    );

                    if ($peratus >= 100 && $ticket->amaran_100_dihantar_pada === null) {
                        $ticket->forceFill(['amaran_100_dihantar_pada' => now()])->save();
                        $amar100++;

                        $this->amaranSla($ticket, true, $calculator);
                    } elseif ($peratus >= 80 && $peratus < 100 && $ticket->amaran_80_dihantar_pada === null) {
                        $ticket->forceFill(['amaran_80_dihantar_pada' => now()])->save();
                        $amar80++;

                        $this->amaranSla($ticket, false, $calculator);
                    }
                }
            });

        return ['amar_80' => $amar80, 'amar_100' => $amar100, 'dilanggar' => $amar100];
    }

    private function amaranSla(Ticket $ticket, bool $dilanggar, SlaCalculator $calculator): void
    {
        $peratus = $calculator->peratusanTerpakai(
            $ticket->masa_dibuka,
            $ticket->sasaran_pemulihan,
            $ticket->minit_jeda_sla,
        );
        $pesan = $dilanggar
            ? "Tiket {$ticket->no_tiket} telah melanggar sasaran pemulihan."
            : "Tiket {$ticket->no_tiket} telah melepasi {$peratus}% daripada sasaran pemulihan.";

        $penerima = $this->penyelia();

        if ($ticket->juruteknik !== null) {
            $penerima->push($ticket->juruteknik);
        }

        foreach ($penerima as $user) {
            $this->notifier->hantar(
                $user,
                $ticket,
                $dilanggar ? 'tiket.sla_langgar' : 'tiket.sla_amaran',
                $dilanggar ? "SLA dilanggar: {$ticket->no_tiket}" : "Amaran SLA: {$ticket->no_tiket}",
                $pesan,
                ['nombor_tiket' => $ticket->no_tiket],
            );
        }
    }

    // ------------------------------------------------------------------
    // Shared helpers
    // ------------------------------------------------------------------

    /**
     * Store uploads on the local disk (outside the web root) with generated
     * file names (NFR-S24, NFR-S25). The original client name is kept for
     * display only.
     *
     * @param  array<int, UploadedFile>  $files
     * @return array<int, array{path: string, name: string, size: int}>|null
     */
    public function simpanLampiran(array $files): ?array
    {
        $stored = [];

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $stored[] = [
                'path' => $file->store('tiket-lampiran'),
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize() ?? 0,
            ];
        }

        return $stored === [] ? null : $stored;
    }

    public function padamLampiran(?array $lampiran): void
    {
        foreach ($lampiran ?? [] as $fail) {
            if (is_array($fail) && isset($fail['path'])) {
                Storage::delete($fail['path']);
            }
        }
    }

    /**
     * Resume the SLA clock after a pause that started at jeda_mula_pada.
     * Both the targets and the accumulated pause counter move by the same
     * amount, so the elapsed-percentage computation stays consistent on
     * both sides of the pause.
     */
    private function sambungSemulaSla(Ticket $ticket, ?CarbonInterface $hingga = null): void
    {
        $mula = $ticket->jeda_mula_pada;

        if ($mula === null) {
            return;
        }

        $calendar = $this->calendar();
        $jeda = $calendar->workingMinutesBetween($mula, $hingga ?? now());

        $ticket->forceFill([
            'minit_jeda_sla' => $ticket->minit_jeda_sla + max(0, $jeda),
            'sasaran_tindak_balas' => $jeda > 0
                ? $calendar->addWorkingMinutes($ticket->sasaran_tindak_balas, $jeda)
                : $ticket->sasaran_tindak_balas,
            'sasaran_pemulihan' => $jeda > 0
                ? $calendar->addWorkingMinutes($ticket->sasaran_pemulihan, $jeda)
                : $ticket->sasaran_pemulihan,
            'jeda_mula_pada' => null,
        ])->save();
    }

    private function slaDipatuhi(Ticket $ticket, CarbonInterface $selesai): bool
    {
        $calendar = $this->calendar();

        $berlalu = $calendar->workingMinutesBetween($ticket->masa_dibuka, $selesai) - $ticket->minit_jeda_sla;
        $jumlah = $calendar->workingMinutesBetween($ticket->masa_dibuka, $ticket->sasaran_pemulihan);

        return $jumlah > 0 && $berlalu <= $jumlah;
    }

    private function minitTindakBalas(TicketPriority $priority): int
    {
        return (int) (setting("sla.{$priority->value}.tindak_balas_minit") ?? $priority->tindakBalasMinitLalai());
    }

    private function minitPemulihan(TicketPriority $priority): int
    {
        return (int) (setting("sla.{$priority->value}.pemulihan_minit") ?? $priority->pemulihanMinitLalai());
    }

    /**
     * A full working day, derived from the longest open window in the week.
     */
    private function hariKerjaMinit(): int
    {
        $terpanjang = 480;

        foreach (OperatingHour::query()->organisationDefault()->get() as $hour) {
            if ($hour->is_closed || $hour->opens_at === null || $hour->closes_at === null) {
                continue;
            }

            $minit = (int) ceil(Carbon::parse($hour->closes_at)->diffInMinutes(Carbon::parse($hour->opens_at)));

            if ($minit > $terpanjang) {
                $terpanjang = $minit;
            }
        }

        return $terpanjang;
    }

    /**
     * @return Collection<int, User>
     */
    private function penyelia(): Collection
    {
        return User::role('penyelia-ict')->get();
    }

    private function maklumPenyelia(Ticket $ticket, string $eventKey, string $pesan): void
    {
        foreach ($this->penyelia() as $penyelia) {
            $this->notifier->hantar(
                $penyelia,
                $ticket,
                $eventKey,
                "Tiket {$ticket->no_tiket}",
                $pesan,
                ['nombor_tiket' => $ticket->no_tiket],
            );
        }
    }

    private function calendar(): WorkingCalendar
    {
        return WorkingCalendar::organisationDefault();
    }

    /**
     * @param  array<int, TicketStatus>  $dibenarkan
     */
    private function perluStatus(Ticket $ticket, array $dibenarkan, string $tindakan): void
    {
        if (in_array($ticket->status, $dibenarkan, true)) {
            return;
        }

        $label = implode(' atau ', array_map(fn (TicketStatus $status): string => $status->label(), $dibenarkan));

        throw ValidationException::withMessages([
            'status' => "Tindakan {$tindakan} hanya dibenarkan bagi tiket berstatus {$label}. Status semasa: {$ticket->status->label()}.",
        ]);
    }

    private function perluTerbuka(Ticket $ticket, string $tindakan): void
    {
        if ($ticket->status->isOpen()) {
            return;
        }

        throw ValidationException::withMessages([
            'status' => "Tidak boleh {$tindakan} kerana tiket ini telah {$ticket->status->label()}.",
        ]);
    }
}
