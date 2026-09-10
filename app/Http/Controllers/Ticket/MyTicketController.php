<?php

namespace App\Http\Controllers\Ticket;

use App\Enums\ReferenceValueType;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\ReopenTicketRequest;
use App\Http\Requests\Ticket\StoreTicketRequest;
use App\Models\Location;
use App\Models\ReferenceValue;
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
 * The reporter's own ticket flow: "Aduan Saya" (UR-20 to UR-22).
 */
class MyTicketController extends Controller
{
    public function __construct(private readonly TicketService $tickets) {}

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();

        $tickets = Ticket::query()
            ->where('pelapor_id', $user->id)
            ->with(['lokasi', 'juruteknik'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('tiket.index', [
            'tickets' => $tickets,
            'terbuka' => $this->kiraPerStatus($user),
        ]);
    }

    public function create(): View
    {
        return view('tiket.create', [
            'lokasi' => $this->pilihanLokasi(),
            'kategori' => $this->pilihanKategori(),
            'gangguan' => TicketPriority::pilihanGangguan(),
        ]);
    }

    public function store(StoreTicketRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $ticket = $this->tickets->buka(
            $user,
            $request->safe()->except(['lampiran']),
            $request->file('lampiran', []),
        );

        return to_route('tiket.show', $ticket)
            ->with('status', "Aduan anda telah diterima. Nombor rujukan {$ticket->no_tiket}.");
    }

    public function show(Request $request, Ticket $ticket): View
    {
        $this->authorize('view', $ticket);

        /** @var User $user */
        $user = Auth::user();

        $ticket->load(['lokasi', 'pelapor', 'juruteknik', 'notes.pengguna']);

        $calculator = new SlaCalculator(WorkingCalendar::organisationDefault());

        return view('tiket.show', [
            'ticket' => $ticket,
            'notes' => $ticket->notes->filter(
                fn ($note) => $note->boleh_dilihat_pelapor || $note->user_id === $user->id || $user->can('tiket.kemas-kini'),
            )->values(),
            'peratusSla' => $ticket->status->isOpen()
                ? $calculator->peratusanTerpakai($ticket->masa_dibuka, $ticket->sasaran_pemulihan, $ticket->minit_jeda_sla)
                : null,
            'adalahPelapor' => $ticket->pelapor_id === $user->id,
            'adalahJuruteknik' => $ticket->juruteknik_id === $user->id,
            'juruteknik' => $user->can('tiket.agih')
                ? User::role('juruteknik')->where('is_active', true)->orderBy('name')->get()
                : collect(),
        ]);
    }

    /**
     * Reporter confirms the repair (UC-13 main flow).
     */
    public function sahkan(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('reporterActions', $ticket);

        $this->tickets->sahkanPenutupan($ticket, Auth::user());

        return to_route('tiket.show', $ticket)
            ->with('status', 'Terima kasih. Tiket telah ditutup.');
    }

    /**
     * Reporter says the problem persists (UC-13, 3a).
     */
    public function bukaSemula(ReopenTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('reporterActions', $ticket);

        $this->tickets->bukaSemula($ticket, Auth::user(), $request->string('alasan')->value());

        return to_route('tiket.show', $ticket)
            ->with('status', 'Tiket telah dibuka semula dan dimaklumkan kepada juruteknik.');
    }

    /**
     * @return array<string, int>
     */
    private function kiraPerStatus(User $user): array
    {
        $counts = [];

        foreach ([TicketStatus::Baharu, TicketStatus::Diagih, TicketStatus::DalamTindakan, TicketStatus::MenungguVendor, TicketStatus::MenungguPengesahan] as $status) {
            $counts[$status->value] = Ticket::query()
                ->where('pelapor_id', $user->id)
                ->where('status', $status->value)
                ->count();
        }

        return $counts;
    }

    /**
     * @return array<int, array{id: int, label: string}>
     */
    private function pilihanLokasi(): array
    {
        return Location::query()
            ->active()
            ->with('parent')
            ->orderBy('name')
            ->get()
            ->map(fn (Location $location) => [
                'id' => $location->id,
                'label' => $location->fullPath(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{code: string, label: string}>
     */
    private function pilihanKategori(): array
    {
        return ReferenceValue::query()
            ->active()
            ->ofType(ReferenceValueType::JenisKerosakan)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (ReferenceValue $value) => [
                'code' => $value->code,
                'label' => $value->label,
            ])
            ->values()
            ->all();
    }
}
