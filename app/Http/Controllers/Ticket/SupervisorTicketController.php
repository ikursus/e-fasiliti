<?php

namespace App\Http\Controllers\Ticket;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\AssignTicketRequest;
use App\Http\Requests\Ticket\CancelTicketRequest;
use App\Http\Requests\Ticket\UpdatePriorityRequest;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Ticket\SlaCalculator;
use App\Services\Ticket\TicketService;
use App\Services\Ticket\WorkingCalendar;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The supervisor's flow: assignment queue with workload, priority changes
 * and withdrawal of invalid reports (UC-11, UR-26, UR-27).
 */
class SupervisorTicketController extends Controller
{
    public function __construct(private readonly TicketService $tickets) {}

    public function index(Request $request): View
    {
        $filters = [
            'status' => $request->input('status'),
            'keutamaan' => $request->input('keutamaan'),
            'juruteknik_id' => $request->integer('juruteknik_id') ?: null,
        ];

        $tickets = Ticket::query()
            ->with(['lokasi', 'pelapor', 'juruteknik'])
            ->when($filters['status'], fn ($query, $status) => $query->where('status', $status))
            ->when($filters['keutamaan'], fn ($query, $keutamaan) => $query->where('keutamaan', $keutamaan))
            ->when($filters['juruteknik_id'], fn ($query, $id) => $query->where('juruteknik_id', $id))
            ->urutanTugasan()
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('tiket.senarai', [
            'tickets' => $tickets,
            'filters' => $filters,
            'status' => TicketStatus::cases(),
            'keutamaan' => TicketPriority::cases(),
            'juruteknik' => $this->senaraiJuruteknik(),
            'beban' => $this->bebanKerja(),
            'ringkasan' => $this->ringkasanStatus(),
            'peratusSla' => $this->peratusSla($tickets->getCollection()),
        ]);
    }

    public function agih(AssignTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $juruteknik = User::query()->findOrFail($request->integer('juruteknik_id'));

        if ($request->filled('ids')) {
            return $this->agihPukal($request, $ticket);
        }

        $this->tickets->agih($ticket, $juruteknik, Auth::user());

        return to_route('tiket.show', $ticket)
            ->with('status', "Tiket telah diagihkan kepada {$juruteknik->name}.");
    }

    public function agihPukal(AssignTicketRequest $request, ?Ticket $ticket = null): RedirectResponse
    {
        $validated = $request->validated();

        if (empty($validated['ids'])) {
            return back()->withErrors(['ids' => 'Pilih sekurang-kurangnya satu tiket untuk agihan pukal.']);
        }

        $juruteknik = User::query()->findOrFail($validated['juruteknik_id']);
        $hasil = $this->tickets->agihPukal($validated['ids'], $juruteknik, Auth::user());

        return back()->with('status', sprintf(
            '%d tiket diagihkan kepada %s. %d tiket tidak dapat diagihkan kerana statusnya.',
            $hasil['berjaya'],
            $juruteknik->name,
            $hasil['gagal'],
        ));
    }

    public function keutamaan(UpdatePriorityRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->tickets->ubahKeutamaan(
            $ticket,
            TicketPriority::from($request->string('keutamaan')->value()),
            $request->string('sebab')->value(),
            Auth::user(),
        );

        return to_route('tiket.show', $ticket)
            ->with('status', 'Keutamaan telah diubah dan sasaran SLA dikira semula.');
    }

    public function batal(CancelTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->tickets->batal($ticket, $request->string('sebab_batal')->value(), Auth::user());

        return to_route('tiket.show', $ticket)
            ->with('status', 'Tiket telah dibatalkan dan pelapor dimaklumkan.');
    }

    /**
     * Open ticket count per technician, shown on the assignment screen
     * (FR-TKT-10) so the supervisor can spread the load fairly.
     *
     * @return array<int, array{id: int, nama: string, terbuka: int}>
     */
    private function bebanKerja(): array
    {
        $counts = Ticket::query()
            ->open()
            ->selectRaw('juruteknik_id, count(*) as terbuka')
            ->whereNotNull('juruteknik_id')
            ->groupBy('juruteknik_id')
            ->pluck('terbuka', 'juruteknik_id');

        return $this->senaraiJuruteknik()
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'nama' => $user->name,
                'terbuka' => (int) ($counts[$user->id] ?? 0),
            ])
            ->all();
    }

    private function senaraiJuruteknik()
    {
        return User::role('juruteknik')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<string, int>
     */
    private function ringkasanStatus(): array
    {
        $ringkasan = [];

        foreach ([
            TicketStatus::Baharu,
            TicketStatus::Diagih,
            TicketStatus::DalamTindakan,
            TicketStatus::MenungguVendor,
            TicketStatus::MenungguPengesahan,
        ] as $status) {
            $ringkasan[$status->value] = Ticket::query()->where('status', $status->value)->count();
        }

        return $ringkasan;
    }

    private function peratusSla($tickets): array
    {
        $calculator = new SlaCalculator(WorkingCalendar::organisationDefault());

        return $tickets
            ->filter(fn (Ticket $ticket): bool => $ticket->status->isOpen())
            ->mapWithKeys(fn (Ticket $ticket): array => [$ticket->id => $calculator->peratusanTerpakai(
                $ticket->masa_dibuka,
                $ticket->sasaran_pemulihan,
                $ticket->minit_jeda_sla,
            )])
            ->all();
    }
}
