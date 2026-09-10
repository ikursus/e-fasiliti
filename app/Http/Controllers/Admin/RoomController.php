<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReferenceValueType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoomStoreRequest;
use App\Http\Requests\Admin\RoomUpdateRequest;
use App\Models\OperatingHour;
use App\Models\ReferenceValue;
use App\Models\Room;
use App\Models\User;
use App\Services\Audit\AuditRecorder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

/**
 * M04 — meeting room catalogue (FR-BLK-01..06, FR-BLK-08).
 */
class RoomController extends Controller
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * Flat catalogue with a search over code and name.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));

        $rooms = Room::query()
            ->with('location.parent.parent.parent')
            ->when($search !== '', fn ($query) => $query->where(
                fn ($inner) => $inner
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
            ))
            ->orderBy('name')
            ->get();

        return view('admin.rooms.index', [
            'rooms' => $rooms,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.rooms.create', [
            'room' => new Room([
                'is_active' => true,
                'min_duration_minutes' => 30,
                'max_duration_minutes' => 480,
                'base_capacity' => 20,
            ]),
            'layoutOptions' => $this->layoutOptions(),
            'facilityOptions' => $this->facilityOptions(),
            'roleOptions' => $this->roleOptions(),
        ]);
    }

    public function store(RoomStoreRequest $request): RedirectResponse
    {
        $room = DB::transaction(function () use ($request): Room {
            $room = Room::create($this->roomAttributes($request));

            $this->syncLayouts($room, (array) $request->input('layouts'));
            $this->syncFacilities($room, (array) $request->input('facilities', []));

            return $room;
        });

        // Snapshot from a refreshed model so every field carries its
        // Eloquent-cast value, not the raw form input.
        $this->audit->record(
            $this->actor(),
            'room.created',
            $room,
            after: $room->refresh()->only($this->auditedFields()),
            context: $this->catalogueContext($room)
        );

        return to_route('admin.rooms.index')
            ->with('status', 'Bilik telah dicipta.');
    }

    /**
     * Attributes every store/update writes, straight from validated input.
     *
     * @return array<string, mixed>
     */
    private function roomAttributes(RoomStoreRequest $request): array
    {
        return [
            'code' => $request->string('code')->upper()->value(),
            'name' => $request->string('name')->value(),
            'location_id' => (int) $request->input('location_id'),
            'base_capacity' => (int) $request->input('base_capacity'),
            'requires_approval' => $request->boolean('requires_approval'),
            'allowed_roles' => $request->input('allowed_roles') ?: null,
            'min_duration_minutes' => (int) $request->input('min_duration_minutes'),
            'max_duration_minutes' => (int) $request->input('max_duration_minutes'),
            'is_active' => $request->boolean('is_active'),
        ];
    }

    public function edit(Room $room): View
    {
        $room->load(['layouts', 'facilities', 'operatingHours']);

        return view('admin.rooms.edit', [
            'room' => $room,
            'layoutOptions' => $this->layoutOptions(),
            'facilityOptions' => $this->facilityOptions(),
            'roleOptions' => $this->roleOptions(),
            'hours' => $this->hoursForForm($room),
        ]);
    }

    /**
     * Seven-day grid for the edit form. A day the room has no row for
     * falls back to the organisation default, then to a sane office day —
     * identical fallback the M05 engine will use at read time.
     *
     * @return array<int, array{is_closed: bool, opens_at: string, closes_at: string}>
     */
    private function hoursForForm(Room $room): array
    {
        $roomHours = $room->operatingHours->keyBy('day_of_week');
        $orgDefaults = OperatingHour::query()->organisationDefault()->get()->keyBy('day_of_week');

        $days = [];

        foreach (range(0, 6) as $day) {
            $row = $roomHours->get($day) ?? $orgDefaults->get($day);

            $days[$day] = [
                'is_closed' => $row?->is_closed ?? in_array($day, [0, 6], true),
                'opens_at' => $row?->opens_at !== null ? substr((string) $row->opens_at, 0, 5) : '08:00',
                'closes_at' => $row?->closes_at !== null ? substr((string) $row->closes_at, 0, 5) : '17:00',
            ];
        }

        return $days;
    }

    public function update(RoomUpdateRequest $request, Room $room): RedirectResponse
    {
        $beforeLayouts = $room->layouts()->orderBy('layout_code')->pluck('layout_code')->all();
        $beforeFacilities = $room->facilities()->orderBy('facility_code')->pluck('facility_code')->all();
        $before = $room->only($this->auditedFields());

        DB::transaction(function () use ($request, $room): void {
            $room->update($this->roomAttributes($request));

            $this->syncLayouts($room, (array) $request->input('layouts'));
            $this->syncFacilities($room, (array) $request->input('facilities', []));
            $this->syncOperatingHours($room, (array) $request->input('days', []));
        });

        $this->audit->record(
            $this->actor(),
            'room.updated',
            $room,
            before: $before,
            after: $room->refresh()->only($this->auditedFields()),
            context: [
                'layouts' => [
                    'before' => $beforeLayouts,
                    'after' => $room->layouts()->orderBy('layout_code')->pluck('layout_code')->all(),
                ],
                'facilities' => [
                    'before' => $beforeFacilities,
                    'after' => $room->facilities()->orderBy('facility_code')->pluck('facility_code')->all(),
                ],
            ]
        );

        return to_route('admin.rooms.index')
            ->with('status', 'Bilik telah dikemas kini.');
    }

    /**
     * FR-BLK-08: deactivating a room with upcoming bookings first shows
     * the affected list and asks for confirmation. Nothing changes until
     * the request returns with confirm=1.
     */
    public function toggle(Request $request, Room $room): View|RedirectResponse
    {
        $activating = ! $room->is_active;
        $affectedCount = 0;

        if (! $activating) {
            $affectedCount = $room->upcomingBookings()->count();

            if ($affectedCount > 0 && ! $request->boolean('confirm')) {
                $room->load(['upcomingBookings.owner', 'upcomingBookings.room']);

                return view('admin.rooms.deactivate', [
                    'room' => $room,
                    'affected' => $room->upcomingBookings()->orderBy('starts_at')->get(),
                ]);
            }
        }

        $room->update(['is_active' => $activating]);

        $this->audit->record(
            $this->actor(),
            $activating ? 'room.activated' : 'room.deactivated',
            $room,
            before: ['is_active' => ! $activating],
            after: ['is_active' => $activating],
            context: $activating ? [] : ['affected_upcoming_bookings' => $affectedCount]
        );

        return to_route('admin.rooms.index')->with(
            'status',
            $activating ? 'Bilik telah diaktifkan semula.' : 'Bilik telah dinyahaktifkan.'
        );
    }

    /**
     * Hard delete, only for rooms nothing points at (FR-BLK-02 history
     * rule). A room with any booking must be deactivated instead.
     */
    public function destroy(Room $room): RedirectResponse
    {
        $reasons = $room->referenceSummary();

        if ($reasons !== []) {
            return back()->withErrors([
                'id' => 'Bilik ini tidak boleh dipadam kerana masih dirujuk oleh: '.implode(', ', $reasons).'. Nyahaktifkan bilik sebagai gantinya.',
            ]);
        }

        $snapshot = $room->only(['code', 'name', 'location_id', 'base_capacity']);
        $room->delete();

        $this->audit->record($this->actor(), 'room.deleted', $room, before: $snapshot);

        return to_route('admin.rooms.index')
            ->with('status', 'Bilik telah dipadam.');
    }

    /**
     * Recreate the layout rows from validated input. Delete-and-recreate
     * is atomic inside the caller's transaction and keeps "exactly one
     * default" trivially true.
     *
     * @param  array<int, array<string, mixed>>  $layouts
     */
    private function syncLayouts(Room $room, array $layouts): void
    {
        $room->layouts()->delete();

        foreach ($layouts as $layout) {
            $room->layouts()->create([
                'layout_code' => strtoupper((string) $layout['layout_code']),
                'capacity' => (int) $layout['capacity'],
                'is_default' => (bool) ($layout['is_default'] ?? false),
            ]);
        }
    }

    /**
     * @param  array<int, string>  $facilities
     */
    private function syncFacilities(Room $room, array $facilities): void
    {
        $room->facilities()->delete();

        foreach (array_values(array_filter($facilities)) as $facilityCode) {
            $room->facilities()->create(['facility_code' => $facilityCode]);
        }
    }

    /**
     * Room-owned operating hours. Days absent from a fresh room fall back
     * to the organisation defaults at read time (FR-TMP-06).
     *
     * @param  array<int|string, array<string, mixed>>  $days
     */
    private function syncOperatingHours(Room $room, array $days): void
    {
        foreach ($days as $day => $values) {
            $closed = (bool) ($values['is_closed'] ?? false);

            $room->operatingHours()->updateOrCreate(
                ['day_of_week' => $day],
                [
                    'is_closed' => $closed,
                    'opens_at' => $closed ? null : ($values['opens_at'] ?? null),
                    'closes_at' => $closed ? null : ($values['closes_at'] ?? null),
                ],
            );
        }
    }

    /**
     * @return Collection<int, string>
     */
    private function layoutOptions(): Collection
    {
        return ReferenceValue::query()
            ->active()
            ->ofType(ReferenceValueType::SusunAturBilik)
            ->orderBy('sort_order')
            ->pluck('label', 'code');
    }

    /**
     * @return Collection<int, string>
     */
    private function facilityOptions(): Collection
    {
        return ReferenceValue::query()
            ->active()
            ->ofType(ReferenceValueType::KemudahanBilik)
            ->orderBy('sort_order')
            ->pluck('label', 'code');
    }

    /**
     * @return array<int, string>
     */
    private function roleOptions(): array
    {
        return Role::query()->orderBy('name')->pluck('name')->all();
    }

    /**
     * @return array<int, string>
     */
    private function auditedFields(): array
    {
        return [
            'code',
            'name',
            'location_id',
            'base_capacity',
            'requires_approval',
            'allowed_roles',
            'min_duration_minutes',
            'max_duration_minutes',
            'is_active',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function catalogueContext(Room $room): array
    {
        return [
            'layouts' => $room->layouts()->orderBy('layout_code')->pluck('layout_code')->all(),
            'facilities' => $room->facilities()->orderBy('facility_code')->pluck('facility_code')->all(),
        ];
    }

    private function actor(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
