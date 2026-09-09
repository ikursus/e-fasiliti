<?php

namespace App\Services\Configuration;

use App\Models\Holiday;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Calendar questions for M01 (FR-ADM-02). All matching happens in memory
 * against a collection, so the rules can be unit tested with no fixtures
 * and reused by the M05 booking engine without extra queries.
 */
class HolidayCalendar
{
    /**
     * @param  Collection<int, Holiday>|null  $holidays
     */
    public function __construct(private ?Collection $holidays = null) {}

    /**
     * Build a calendar around an already-loaded collection.
     *
     * @param  Collection<int, Holiday>  $holidays
     */
    public static function fromCollection(Collection $holidays): self
    {
        return new self($holidays);
    }

    /**
     * Whether the date is a public holiday.
     */
    public function isHoliday(CarbonInterface $date): bool
    {
        return $this->matches($date, Holiday::TYPE_PUBLIC);
    }

    /**
     * Whether bookings are allowed on this date. Public holidays and
     * explicitly blocked days both return false.
     */
    public function isBookable(CarbonInterface $date): bool
    {
        return ! $this->isHoliday($date) && ! $this->matches($date, Holiday::TYPE_NO_BOOKING);
    }

    private function matches(CarbonInterface $date, string $type): bool
    {
        return $this->entries()
            ->where('type', $type)
            ->contains(fn (Holiday $holiday) => $this->covers($holiday, $date));
    }

    private function covers(Holiday $holiday, CarbonInterface $date): bool
    {
        if (! $holiday->is_active) {
            return false;
        }

        if ($holiday->recurs_annually) {
            return $holiday->date->format('m-d') === $date->format('m-d');
        }

        return $holiday->date->isSameDay($date);
    }

    /**
     * @return Collection<int, Holiday>
     */
    private function entries(): Collection
    {
        return $this->holidays ??= Holiday::query()->active()->get();
    }
}
