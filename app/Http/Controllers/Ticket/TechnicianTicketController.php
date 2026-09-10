<?php

namespace App\Http\Controllers\Ticket;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\ReferVendorRequest;
use App\Http\Requests\Ticket\StoreNoteRequest;
use App\Http\Requests\Ticket\StoreWorkRequest;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Ticket\SlaCalculator;
use App\Services\Ticket\TicketService;
use App\Services\Ticket\WorkingCalendar;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * The technician's field flow: "Tugasan Saya" (UR-23, UR-25, UC-12).
 */
class TechnicianTicketController extends Controller
{
    public function __construct(private readonly TicketService $tickets) {}

    public function tugasan(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $tickets = Ticket::query()
            ->where('juruteknik_id', $user->id)
            ->open()
            ->with('lokasi')
            ->urutanTugasan()
            ->paginate(20);

        $calculator = new SlaCalculator(WorkingCalendar::organisationDefault());

        return view('tiket.tugasan', [
            'tickets' => $tickets,
            'peratus' => $tickets->getCollection()->mapWithKeys(
                fn (Ticket $ticket) => [$ticket->id => $calculator->peratusanTerpakai(
                    $ticket->masa_dibuka,
                    $ticket->sasaran_pemulihan,
                    $ticket->minit_jeda_sla,
                )],
            )->all(),
        ]);
    }

    public function mula(Ticket $ticket): RedirectResponse
    {
        $this->authorize('work', $ticket);

        $this->tickets->mulaKerja($ticket, Auth::user());

        return to_route('tiket.show', $ticket)
            ->with('status', 'Kerja telah dimulakan. Masa tindak balas pertama direkodkan.');
    }

    public function simpanKerja(StoreWorkRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('work', $ticket);

        $this->tickets->simpanKerja($ticket, $request->validated(), Auth::user());

        return to_route('tiket.show', $ticket)
            ->with('status', 'Rekod kerja telah disimpan.');
    }

    public function catatan(StoreNoteRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('work', $ticket);

        $this->tickets->catatNota(
            $ticket,
            Auth::user(),
            $request->string('catatan')->value(),
            $request->boolean('boleh_dilihat_pelapor', true),
            $request->file('lampiran', []),
        );

        return to_route('tiket.show', $ticket)
            ->with('status', 'Catatan kemajuan telah direkodkan.');
    }

    public function rujukVendor(ReferVendorRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('work', $ticket);

        $this->tickets->rujukVendor(
            $ticket,
            $request->string('no_rujukan_vendor')->value(),
            $request->string('vendor_nama')->value(),
            Auth::user(),
        );

        return to_route('tiket.show', $ticket)
            ->with('status', 'Tiket telah dirujuk kepada vendor. Jam SLA dijeda sehingga kerja disambung semula.');
    }

    public function sambungVendor(Ticket $ticket): RedirectResponse
    {
        $this->authorize('work', $ticket);

        $this->tickets->sambungSelepasVendor($ticket, Auth::user());

        return to_route('tiket.show', $ticket)
            ->with('status', 'Kerja disambung semula. Jam SLA berjalan semula.');
    }

    public function selesai(Ticket $ticket): RedirectResponse
    {
        $this->authorize('work', $ticket);

        $this->tickets->tandaSelesai($ticket, Auth::user());

        return to_route('tiket.show', $ticket)
            ->with('status', 'Kerja ditanda selesai. Permintaan pengesahan telah dihantar kepada pelapor.');
    }
}
