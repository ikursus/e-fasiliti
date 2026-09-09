<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleBookingRulesRequest;
use App\Models\RoleBookingRule;
use App\Models\User;
use App\Services\Audit\AuditRecorder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

/**
 * M01 — booking limits per role (FR-ADM-03). Consumed by M05.
 */
class RoleBookingRuleController extends Controller
{
    public function __construct(private readonly AuditRecorder $audit) {}

    public function edit(): View
    {
        $existing = RoleBookingRule::query()->get()->keyBy('role_id');

        $rows = Role::query()->orderBy('name')->get()->map(fn (Role $role) => [
            'id' => $role->id,
            'name' => $role->name,
            'min_duration_minutes' => $existing[$role->id]->min_duration_minutes ?? 30,
            'max_duration_minutes' => $existing[$role->id]->max_duration_minutes ?? 240,
            'max_advance_days' => $existing[$role->id]->max_advance_days ?? 90,
        ]);

        return view('admin.settings.booking-rules', ['rows' => $rows]);
    }

    public function update(RoleBookingRulesRequest $request): RedirectResponse
    {
        $before = RoleBookingRule::query()->get()->mapWithKeys(fn (RoleBookingRule $rule) => [
            (string) $rule->role_id => $rule->min_duration_minutes.'/'.$rule->max_duration_minutes.'/'.$rule->max_advance_days,
        ])->all();

        $after = [];

        DB::transaction(function () use ($request, &$after): void {
            foreach ((array) $request->input('rules') as $roleId => $values) {
                RoleBookingRule::query()->updateOrCreate(
                    ['role_id' => (int) $roleId],
                    [
                        'min_duration_minutes' => (int) $values['min_duration_minutes'],
                        'max_duration_minutes' => (int) $values['max_duration_minutes'],
                        'max_advance_days' => (int) $values['max_advance_days'],
                    ]
                );

                $after[(string) $roleId] = $values['min_duration_minutes'].'/'.$values['max_duration_minutes'].'/'.$values['max_advance_days'];
            }
        });

        /** @var User $actor */
        $actor = Auth::user();

        $this->audit->record(
            $actor,
            'booking_rules.updated',
            RoleBookingRule::query()->orderBy('id')->firstOrFail(),
            before: $before,
            after: $after,
        );

        return to_route('admin.settings.booking-rules')
            ->with('status', 'Peraturan tempahan telah disimpan.');
    }
}
