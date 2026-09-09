<?php

namespace Tests\Unit\Services;

use App\Models\Holiday;
use App\Services\Configuration\HolidayCalendar;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HolidayCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_one_off_holiday_matches_only_its_own_date(): void
    {
        Holiday::factory()->create([
            'date' => '2026-05-01',
            'name' => 'Hari Pekerja',
            'type' => 'cuti_umum',
            'recurs_annually' => false,
        ]);

        $calendar = app(HolidayCalendar::class);

        $this->assertTrue($calendar->isHoliday(CarbonImmutable::parse('2026-05-01')));
        $this->assertFalse($calendar->isHoliday(CarbonImmutable::parse('2027-05-01')));
    }

    public function test_an_annually_recurring_holiday_matches_the_same_day_every_year(): void
    {
        Holiday::factory()->create([
            'date' => '2026-08-31',
            'name' => 'Hari Kebangsaan',
            'type' => 'cuti_umum',
            'recurs_annually' => true,
        ]);

        $calendar = app(HolidayCalendar::class);

        $this->assertTrue($calendar->isHoliday(CarbonImmutable::parse('2026-08-31')));
        $this->assertTrue($calendar->isHoliday(CarbonImmutable::parse('2030-08-31')));
        $this->assertFalse($calendar->isHoliday(CarbonImmutable::parse('2030-09-01')));
    }

    public function test_a_deactivated_holiday_is_ignored(): void
    {
        Holiday::factory()->create([
            'date' => '2026-05-01',
            'type' => 'cuti_umum',
            'is_active' => false,
        ]);

        $this->assertFalse(app(HolidayCalendar::class)->isHoliday(CarbonImmutable::parse('2026-05-01')));
    }

    public function test_a_non_bookable_day_is_not_a_public_holiday_but_blocks_bookings(): void
    {
        Holiday::factory()->create([
            'date' => '2026-06-15',
            'name' => 'Penyelenggaraan Bangunan',
            'type' => 'hari_tanpa_tempahan',
        ]);

        $calendar = app(HolidayCalendar::class);

        $this->assertFalse($calendar->isHoliday(CarbonImmutable::parse('2026-06-15')));
        $this->assertFalse($calendar->isBookable(CarbonImmutable::parse('2026-06-15')));
    }

    public function test_a_public_holiday_also_blocks_bookings(): void
    {
        Holiday::factory()->create(['date' => '2026-05-01', 'type' => 'cuti_umum']);

        $this->assertFalse(app(HolidayCalendar::class)->isBookable(CarbonImmutable::parse('2026-05-01')));
    }

    public function test_an_ordinary_day_is_bookable(): void
    {
        $this->assertTrue(app(HolidayCalendar::class)->isBookable(CarbonImmutable::parse('2026-05-02')));
    }

    public function test_the_calendar_can_be_built_from_a_collection_without_touching_the_database(): void
    {
        $calendar = HolidayCalendar::fromCollection(collect([
            new Holiday(['date' => '2026-05-01', 'type' => 'cuti_umum', 'recurs_annually' => false, 'is_active' => true]),
        ]));

        $this->assertTrue($calendar->isHoliday(CarbonImmutable::parse('2026-05-01')));
    }
}
