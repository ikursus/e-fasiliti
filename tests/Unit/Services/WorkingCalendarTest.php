<?php

namespace Tests\Unit\Services;

use App\Models\Holiday;
use App\Services\Configuration\HolidayCalendar;
use App\Services\Ticket\WorkingCalendar;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class WorkingCalendarTest extends TestCase
{
    /**
     * 09:00-17:00 Mon-Fri, weekends closed, no holidays unless a test adds
     * one. All expected dates below sit in the week of 7-11 Sep 2026
     * (Mon 7th to Fri 11th).
     *
     * @return array<int, array{is_closed: bool, opens_at: ?string, closes_at: ?string}>
     */
    private function calendarHours(): array
    {
        $hours = [];

        foreach (range(0, 6) as $day) {
            $weekend = in_array($day, [0, 6], true);

            $hours[$day] = [
                'is_closed' => $weekend,
                'opens_at' => $weekend ? null : '09:00',
                'closes_at' => $weekend ? null : '17:00',
            ];
        }

        return $hours;
    }

    private function calendar(?Collection $holidays = null): WorkingCalendar
    {
        return new WorkingCalendar(
            $this->calendarHours(),
            HolidayCalendar::fromCollection($holidays ?? collect()),
        );
    }

    public function test_it_advances_within_the_same_working_day(): void
    {
        $dari = Carbon::parse('2026-09-09 10:00:00'); // Rabu

        $hasil = $this->calendar()->addWorkingMinutes($dari, 120);

        $this->assertTrue($hasil->equalTo(Carbon::parse('2026-09-09 12:00:00')));
    }

    public function test_it_carries_leftover_minutes_into_the_next_working_day(): void
    {
        $dari = Carbon::parse('2026-09-09 10:00:00'); // Rabu, 420 minit tinggal hari itu

        $hasil = $this->calendar()->addWorkingMinutes($dari, 480);

        $this->assertTrue($hasil->equalTo(Carbon::parse('2026-09-10 10:00:00')));
    }

    public function test_it_skips_the_weekend(): void
    {
        $jumaat = Carbon::parse('2026-09-11 16:00:00');

        $hasil = $this->calendar()->addWorkingMinutes($jumaat, 120);

        $this->assertTrue($hasil->equalTo(Carbon::parse('2026-09-14 10:00:00')));
    }

    public function test_it_skips_public_holidays(): void
    {
        $cuti = $this->cutiTanpaKonstruktor('2026-09-16');

        $selasa = Carbon::parse('2026-09-15 16:00:00');

        $hasil = $this->calendar(collect([$cuti]))->addWorkingMinutes($selasa, 90);

        // 60 minit terakhir Selasa digunakan; Rabu ialah cuti, jadi baki
        // 30 minit jatuh pada Khamis 09:00-09:30.
        $this->assertTrue($hasil->equalTo(Carbon::parse('2026-09-17 09:30:00')));
    }

    /**
     * A Holiday built without the Eloquent constructor: unit tests run
     * without a database connection, and the datetime cast would otherwise
     * reach for the default date format through the connection resolver.
     */
    private function cutiTanpaKonstruktor(string $tarikh): Holiday
    {
        $cuti = (new \ReflectionClass(Holiday::class))->newInstanceWithoutConstructor();

        $isi = \Closure::bind(function () use ($cuti, $tarikh): void {
            $cuti->attributes = [
                'date' => Carbon::parse($tarikh),
                'name' => 'Cuti Ujian',
                'type' => Holiday::TYPE_PUBLIC,
                'recurs_annually' => false,
                'is_active' => true,
            ];
        }, null, Holiday::class);

        $isi();

        return $cuti;
    }

    public function test_it_counts_working_minutes_between_two_moments(): void
    {
        $jumaat = Carbon::parse('2026-09-11 16:00:00');
        $isnin = Carbon::parse('2026-09-14 10:00:00');

        $minit = $this->calendar()->workingMinutesBetween($jumaat, $isnin);

        // Jumaat 16:00-17:00 (60) + Isnin 09:00-10:00 (60), hujung minggu kosong.
        $this->assertSame(120, $minit);
    }

    public function test_it_counts_nothing_for_a_reversed_or_empty_window(): void
    {
        $calendar = $this->calendar();

        $this->assertSame(0, $calendar->workingMinutesBetween(
            Carbon::parse('2026-09-14 10:00:00'),
            Carbon::parse('2026-09-11 16:00:00'),
        ));

        $this->assertSame(0, $calendar->workingMinutesBetween(
            Carbon::parse('2026-09-12 10:00:00'), // Sabtu
            Carbon::parse('2026-09-13 10:00:00'), // Ahad
        ));
    }
}
