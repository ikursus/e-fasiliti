<?php

namespace App\Services\Ticket;

use App\Models\OperatingHour;
use App\Services\Configuration\HolidayCalendar;
use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Working-time calendar for the SLA engine (FR-TKT-07, SDD §5.2). SLA clock
 * runs only within configured operating hours, excluding weekends and public
 * holidays. Pure date arithmetic — it touches the database only through the
 * constructor helpers, so the math itself is unit testable in isolation.
 */
class WorkingCalendar
{
    /**
     * Hard stop for pathological calendars: an entirely closed calendar can
     * never advance, and a corrupt setup must not spin the loop forever.
     */
    private const MAX_DAY_STEPS = 800;

    /**
     * @param  array<int, array{is_closed: bool, opens_at: ?string, closes_at: ?string}>  $hours
     */
    public function __construct(
        private readonly array $hours,
        private readonly HolidayCalendar $holidays,
    ) {}

    public static function organisationDefault(): self
    {
        /** @var array<int, array{is_closed: bool, opens_at: ?string, closes_at: ?string}> $hours */
        $hours = OperatingHour::query()
            ->organisationDefault()
            ->get()
            ->keyBy('day_of_week')
            ->map(fn (OperatingHour $hour): array => [
                'is_closed' => (bool) $hour->is_closed,
                'opens_at' => $hour->opens_at,
                'closes_at' => $hour->closes_at,
            ])
            ->all();

        return new self($hours, new HolidayCalendar);
    }

    /**
     * The earliest moment the given number of working minutes past $from
     * falls, respecting operating hours, weekends and holidays.
     */
    public function addWorkingMinutes(CarbonInterface $from, int $minutes): Carbon
    {
        $cursor = Carbon::instance($from);
        $remaining = $minutes;

        if ($remaining <= 0) {
            return $cursor;
        }

        for ($step = 0; $step < self::MAX_DAY_STEPS && $remaining > 0; $step++) {
            $window = $this->workingWindowFor($cursor);

            if ($window !== null) {
                [$opens, $closes] = $window;

                if ($cursor->lt($opens)) {
                    $cursor = $opens->copy();
                }

                if ($cursor->lt($closes)) {
                    $available = (int) ceil($cursor->diffInMinutes($closes));

                    if ($available >= $remaining) {
                        $cursor = $cursor->copy()->addMinutes($remaining);
                        $remaining = 0;
                    } else {
                        $remaining -= $available;
                        $cursor = $closes->copy();
                    }
                }
            }

            if ($remaining > 0) {
                $cursor = $cursor->copy()->addDay()->startOfDay();
            }
        }

        return $cursor;
    }

    /**
     * Working minutes elapsed between two moments. A moment outside working
     * time contributes nothing.
     */
    public function workingMinutesBetween(CarbonInterface $from, CarbonInterface $to): int
    {
        if ($to->lte($from)) {
            return 0;
        }

        $cursor = Carbon::instance($from);
        $end = Carbon::instance($to);
        $total = 0;

        for ($step = 0; $step < self::MAX_DAY_STEPS && $cursor->lt($end); $step++) {
            $window = $this->workingWindowFor($cursor);

            if ($window !== null) {
                [$opens, $closes] = $window;

                $segmentStart = $cursor->max($opens);
                $segmentEnd = $end->min($closes);

                if ($segmentStart->lt($segmentEnd)) {
                    $total += (int) ceil($segmentStart->diffInMinutes($segmentEnd));
                }
            }

            $cursor = $cursor->copy()->addDay()->startOfDay();
        }

        return $total;
    }

    /**
     * The open and close moments for the day of the given date, or null when
     * the organisation is closed that day.
     *
     * @return array{0: Carbon, 1: Carbon}|null
     */
    private function workingWindowFor(CarbonInterface $date): ?array
    {
        $entry = $this->hours[$date->dayOfWeek] ?? null;

        if ($entry === null || $entry['is_closed']) {
            return null;
        }

        if ($entry['opens_at'] === null || $entry['closes_at'] === null) {
            return null;
        }

        if ($this->holidays->isHoliday($date)) {
            return null;
        }

        $opens = Carbon::instance($date->copy()->startOfDay())->setTimeFromTimeString($entry['opens_at']);
        $closes = Carbon::instance($date->copy()->startOfDay())->setTimeFromTimeString($entry['closes_at']);

        if ($closes->lte($opens)) {
            return null;
        }

        return [$opens, $closes];
    }
}
