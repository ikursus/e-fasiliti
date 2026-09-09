<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OperatingHoursRequest;
use App\Models\OperatingHour;
use App\Models\User;
use App\Services\Audit\AuditRecorder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * M01 — organisation-wide operating hours (FR-ADM-01).
 */
class OperatingHourController extends Controller
{
    public function __construct(private readonly AuditRecorder $audit) {}

    public function edit(): View
    {
        $existing = OperatingHour::query()
            ->organisationDefault()
            ->get()
            ->keyBy('day_of_week');

        $days = collect(range(0, 6))->map(fn (int $day) => [
            'day' => $day,
            'name' => OperatingHour::DAY_NAMES[$day],
            'is_closed' => (bool) ($existing[$day]->is_closed ?? in_array($day, [0, 6], true)),
            'opens_at' => substr((string) ($existing[$day]->opens_at ?? '08:00'), 0, 5),
            'closes_at' => substr((string) ($existing[$day]->closes_at ?? '17:00'), 0, 5),
        ]);

        return view('admin.settings.operating-hours', ['days' => $days]);
    }

    public function update(OperatingHoursRequest $request): RedirectResponse
    {
        $before = OperatingHour::query()
            ->organisationDefault()
            ->get()
            ->mapWithKeys(fn (OperatingHour $hour) => [
                $hour->day_of_week => $hour->is_closed ? 'tutup' : $hour->opens_at.'-'.$hour->closes_at,
            ])
            ->all();

        $after = [];

        DB::transaction(function () use ($request, &$after): void {
            foreach ((array) $request->input('days') as $day => $values) {
                $isClosed = (bool) ($values['is_closed'] ?? false);

                OperatingHour::query()->updateOrCreate(
                    ['owner_type' => null, 'owner_id' => null, 'day_of_week' => (int) $day],
                    [
                        'is_closed' => $isClosed,
                        'opens_at' => $isClosed ? null : $values['opens_at'],
                        'closes_at' => $isClosed ? null : $values['closes_at'],
                    ]
                );

                $after[(int) $day] = $isClosed ? 'tutup' : $values['opens_at'].'-'.$values['closes_at'];
            }
        });

        /** @var User $actor */
        $actor = Auth::user();

        $this->audit->record(
            $actor,
            'operating_hours.updated',
            OperatingHour::query()->organisationDefault()->orderBy('day_of_week')->firstOrFail(),
            before: $before,
            after: $after,
        );

        return to_route('admin.settings.operating-hours')
            ->with('status', 'Waktu operasi telah disimpan.');
    }
}
