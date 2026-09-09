<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LocationLevel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LocationStoreRequest;
use App\Http\Requests\Admin\LocationUpdateRequest;
use App\Models\Location;
use App\Models\User;
use App\Services\Audit\AuditRecorder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * M03 — physical location hierarchy (FR-ORG-01, FR-ORG-03, FR-ORG-04).
 */
class LocationController extends Controller
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * Render the location tree, or a flat result list when searching.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));

        if ($search !== '') {
            $matches = Location::query()
                ->with('parent')
                ->where(fn ($query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%"))
                ->orderBy('level')
                ->orderBy('name')
                ->get();

            return view('admin.locations.index', [
                'roots' => collect(),
                'matches' => $matches,
                'search' => $search,
            ]);
        }

        $roots = Location::query()
            ->whereNull('parent_id')
            ->with('descendants')
            ->orderBy('name')
            ->get();

        return view('admin.locations.index', [
            'roots' => $roots,
            'matches' => null,
            'search' => '',
        ]);
    }

    public function create(): View
    {
        return view('admin.locations.create', [
            'location' => new Location(['is_active' => true, 'level' => LocationLevel::Kampus]),
            'parents' => $this->parentOptions(),
            'levels' => LocationLevel::cases(),
        ]);
    }

    public function store(LocationStoreRequest $request): RedirectResponse
    {
        $location = Location::create([
            'code' => $request->string('code')->upper()->value(),
            'name' => $request->string('name')->value(),
            'level' => $request->string('level')->value(),
            'parent_id' => $request->input('parent_id'),
            'is_active' => $request->boolean('is_active'),
        ]);

        // Snapshot from a refreshed model so parent_id is recorded as the
        // integer the database holds, not the string the form submitted.
        $this->audit->record($this->actor(), 'location.created', $location, after: $location->refresh()->only([
            'code', 'name', 'parent_id',
        ]));

        return to_route('admin.locations.index')
            ->with('status', 'Lokasi telah dicipta.');
    }

    public function edit(Location $location): View
    {
        return view('admin.locations.edit', [
            'location' => $location,
            'parents' => $this->parentOptions($location),
            'levels' => LocationLevel::cases(),
        ]);
    }

    public function update(LocationUpdateRequest $request, Location $location): RedirectResponse
    {
        $before = $location->only(['code', 'name', 'level', 'parent_id', 'is_active']);

        $location->update([
            'code' => $request->string('code')->upper()->value(),
            'name' => $request->string('name')->value(),
            'level' => $request->string('level')->value(),
            'parent_id' => $request->input('parent_id'),
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->audit->record(
            $this->actor(),
            'location.updated',
            $location,
            before: $this->normalise($before),
            after: $this->normalise($location->refresh()->only(['code', 'name', 'level', 'parent_id', 'is_active'])),
        );

        return to_route('admin.locations.index')
            ->with('status', 'Lokasi telah dikemas kini.');
    }

    /**
     * Deactivate or reactivate. Deactivating cascades to descendants so a
     * closed building does not leave bookable rooms behind. Reactivating is
     * refused while the parent is still inactive, which keeps that same
     * invariant from being undone one record at a time.
     */
    public function toggle(Location $location): RedirectResponse
    {
        $activating = ! $location->is_active;

        if ($activating && $location->parent !== null && ! $location->parent->is_active) {
            return back()->withErrors([
                'id' => 'Lokasi ini tidak boleh diaktifkan kerana lokasi induknya masih tidak aktif. Aktifkan lokasi induk dahulu.',
            ]);
        }

        $cascadedIds = $activating ? [] : $location->descendantIds();

        DB::transaction(function () use ($location, $activating, $cascadedIds): void {
            $location->update(['is_active' => $activating]);

            if (! $activating && $cascadedIds !== []) {
                Location::query()
                    ->whereIn('id', $cascadedIds)
                    ->update(['is_active' => false]);
            }
        });

        $this->audit->record(
            $this->actor(),
            $activating ? 'location.activated' : 'location.deactivated',
            $location,
            before: ['is_active' => ! $activating],
            after: ['is_active' => $activating],
            // The cascaded rows change without an audit entry of their own,
            // so name them here or the trail cannot explain why they moved.
            context: $cascadedIds === [] ? [] : ['cascaded_ids' => $cascadedIds],
        );

        return to_route('admin.locations.index')->with(
            'status',
            $activating ? 'Lokasi telah diaktifkan semula.' : 'Lokasi dan keturunannya telah dinyahaktifkan.'
        );
    }

    /**
     * Hard delete, only for records nothing points at (FR-ORG-03).
     */
    public function destroy(Location $location): RedirectResponse
    {
        $reasons = $location->referenceSummary();

        if ($reasons !== []) {
            return back()->withErrors([
                'id' => 'Lokasi ini tidak boleh dipadam kerana masih dirujuk oleh: '.implode(', ', $reasons).'. Nyahaktifkan lokasi sebagai gantinya.',
            ]);
        }

        $snapshot = $location->only(['code', 'name', 'level', 'parent_id']);
        $location->delete();

        $this->audit->record($this->actor(), 'location.deleted', $location, before: $this->normalise($snapshot));

        return to_route('admin.locations.index')
            ->with('status', 'Lokasi telah dipadam.');
    }

    /**
     * Every location that may act as a parent, with its full path label.
     *
     * The bottom level is left out: nothing may sit under a ruang, so
     * offering one only lets the user pick an option the validator is
     * guaranteed to reject.
     *
     * @return Collection<int, array{id: int, label: string, level: string}>
     */
    private function parentOptions(?Location $exclude = null): Collection
    {
        $excluded = $exclude === null ? [] : array_merge([$exclude->id], $exclude->descendantIds());

        $eligibleLevels = array_filter(
            LocationLevel::cases(),
            fn (LocationLevel $level) => $level->parentLevel() !== null,
        );

        return Location::query()
            ->with('parent')
            ->whereIn('level', array_map(
                fn (LocationLevel $level) => $level->parentLevel()->value,
                $eligibleLevels,
            ))
            ->whereNotIn('id', $excluded)
            ->orderBy('name')
            ->get()
            ->map(fn (Location $location) => [
                'id' => $location->id,
                'label' => $location->fullPath(),
                'level' => $location->level->value,
            ])
            ->values();
    }

    /**
     * Cast enum values so audit payloads stay JSON-comparable.
     *
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function normalise(array $values): array
    {
        return array_map(
            fn ($value) => $value instanceof LocationLevel ? $value->value : $value,
            $values
        );
    }

    private function actor(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
