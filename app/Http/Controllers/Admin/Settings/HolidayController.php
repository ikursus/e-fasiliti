<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HolidayRequest;
use App\Models\Holiday;
use App\Models\User;
use App\Services\Audit\AuditRecorder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * M01 — public holidays and non-bookable days (FR-ADM-02).
 */
class HolidayController extends Controller
{
    public function __construct(private readonly AuditRecorder $audit) {}

    public function index(Request $request): View
    {
        $year = (int) $request->integer('year', (int) date('Y'));

        return view('admin.settings.holidays', [
            'year' => $year,
            'holidays' => Holiday::query()
                ->where(fn ($query) => $query
                    ->whereYear('date', $year)
                    ->orWhere('recurs_annually', true))
                ->orderBy('date')
                ->get(),
        ]);
    }

    public function store(HolidayRequest $request): RedirectResponse
    {
        $holiday = Holiday::create([
            'date' => $request->string('date')->value(),
            'name' => $request->string('name')->value(),
            'type' => $request->string('type')->value(),
            'recurs_annually' => $request->boolean('recurs_annually'),
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->audit->record($this->actor(), 'holiday.created', $holiday, after: [
            'date' => $holiday->date->toDateString(),
            'name' => $holiday->name,
            'type' => $holiday->type,
        ]);

        return to_route('admin.settings.holidays')->with('status', 'Cuti telah ditambah.');
    }

    public function destroy(Holiday $holiday): RedirectResponse
    {
        $snapshot = [
            'date' => $holiday->date->toDateString(),
            'name' => $holiday->name,
            'type' => $holiday->type,
        ];

        $holiday->delete();

        $this->audit->record($this->actor(), 'holiday.deleted', $holiday, before: $snapshot);

        return to_route('admin.settings.holidays')->with('status', 'Cuti telah dipadam.');
    }

    private function actor(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
