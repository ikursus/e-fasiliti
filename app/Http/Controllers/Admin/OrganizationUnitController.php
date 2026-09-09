<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrganizationUnitStoreRequest;
use App\Http\Requests\Admin\OrganizationUnitUpdateRequest;
use App\Models\OrganizationUnit;
use App\Models\User;
use App\Services\Audit\AuditRecorder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * M03 — organisational hierarchy, divisions and units (FR-ORG-02).
 */
class OrganizationUnitController extends Controller
{
    public function __construct(private readonly AuditRecorder $audit) {}

    public function index(): View
    {
        return view('admin.organization-units.index', [
            'divisions' => OrganizationUnit::query()
                ->whereNull('parent_id')
                ->with(['children' => fn ($query) => $query->orderBy('name')])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.organization-units.create', [
            'unit' => new OrganizationUnit(['is_active' => true]),
            'divisions' => $this->divisionOptions(),
        ]);
    }

    public function store(OrganizationUnitStoreRequest $request): RedirectResponse
    {
        $unit = OrganizationUnit::create([
            'code' => $request->string('code')->upper()->value(),
            'name' => $request->string('name')->value(),
            'parent_id' => $request->input('parent_id'),
            'is_active' => $request->boolean('is_active'),
        ]);

        // Snapshot from a refreshed model so parent_id is recorded as the
        // integer the database holds, not the string the form submitted.
        $this->audit->record($this->actor(), 'organization_unit.created', $unit, after: $unit->refresh()->only([
            'code', 'name', 'parent_id',
        ]));

        return to_route('admin.organization-units.index')
            ->with('status', 'Unit organisasi telah dicipta.');
    }

    public function edit(OrganizationUnit $organizationUnit): View
    {
        return view('admin.organization-units.edit', [
            'unit' => $organizationUnit,
            'divisions' => $this->divisionOptions($organizationUnit),
        ]);
    }

    public function update(OrganizationUnitUpdateRequest $request, OrganizationUnit $organizationUnit): RedirectResponse
    {
        $before = $organizationUnit->only(['code', 'name', 'parent_id', 'is_active']);

        $organizationUnit->update([
            'code' => $request->string('code')->upper()->value(),
            'name' => $request->string('name')->value(),
            'parent_id' => $request->input('parent_id'),
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->audit->record(
            $this->actor(),
            'organization_unit.updated',
            $organizationUnit,
            before: $before,
            after: $organizationUnit->refresh()->only(['code', 'name', 'parent_id', 'is_active']),
        );

        return to_route('admin.organization-units.index')
            ->with('status', 'Unit organisasi telah dikemas kini.');
    }

    /**
     * Deactivate or reactivate. Deactivating cascades to the units under a
     * division; reactivating is refused while the division is still inactive,
     * which keeps that invariant from being undone one record at a time.
     */
    public function toggle(OrganizationUnit $organizationUnit): RedirectResponse
    {
        $activating = ! $organizationUnit->is_active;

        if ($activating && $organizationUnit->parent !== null && ! $organizationUnit->parent->is_active) {
            return back()->withErrors([
                'id' => 'Unit ini tidak boleh diaktifkan kerana bahagian induknya masih tidak aktif. Aktifkan bahagian dahulu.',
            ]);
        }

        $cascadedIds = $activating ? [] : $organizationUnit->descendantIds();

        DB::transaction(function () use ($organizationUnit, $activating, $cascadedIds): void {
            $organizationUnit->update(['is_active' => $activating]);

            if (! $activating && $cascadedIds !== []) {
                OrganizationUnit::query()
                    ->whereIn('id', $cascadedIds)
                    ->update(['is_active' => false]);
            }
        });

        $this->audit->record(
            $this->actor(),
            $activating ? 'organization_unit.activated' : 'organization_unit.deactivated',
            $organizationUnit,
            before: ['is_active' => ! $activating],
            after: ['is_active' => $activating],
            // The cascaded rows change without an audit entry of their own,
            // so name them here or the trail cannot explain why they moved.
            context: $cascadedIds === [] ? [] : ['cascaded_ids' => $cascadedIds],
        );

        return to_route('admin.organization-units.index')->with(
            'status',
            $activating ? 'Unit telah diaktifkan semula.' : 'Unit dan unit anaknya telah dinyahaktifkan.'
        );
    }

    public function destroy(OrganizationUnit $organizationUnit): RedirectResponse
    {
        $reasons = $organizationUnit->referenceSummary();

        if ($reasons !== []) {
            return back()->withErrors([
                'id' => 'Unit ini tidak boleh dipadam kerana masih dirujuk oleh: '.implode(', ', $reasons).'. Nyahaktifkan unit sebagai gantinya.',
            ]);
        }

        $snapshot = $organizationUnit->only(['code', 'name', 'parent_id']);
        $organizationUnit->delete();

        $this->audit->record($this->actor(), 'organization_unit.deleted', $organizationUnit, before: $snapshot);

        return to_route('admin.organization-units.index')
            ->with('status', 'Unit organisasi telah dipadam.');
    }

    /**
     * Divisions available as a parent, excluding the record being edited.
     *
     * @return Collection<int, OrganizationUnit>
     */
    private function divisionOptions(?OrganizationUnit $exclude = null): Collection
    {
        return OrganizationUnit::query()
            ->whereNull('parent_id')
            ->when($exclude !== null, fn ($query) => $query->whereKeyNot($exclude->id))
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }

    private function actor(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
