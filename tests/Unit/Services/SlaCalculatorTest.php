<?php

namespace Tests\Unit\Services;

use App\Enums\TicketPriority;
use App\Services\Configuration\HolidayCalendar;
use App\Services\Ticket\SlaCalculator;
use App\Services\Ticket\WorkingCalendar;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class SlaCalculatorTest extends TestCase
{
    /**
     * The same fixed 09:00-17:00 Mon-Fri week as the WorkingCalendar tests.
     * All dates sit in the week of 7-11 Sep 2026 (Mon 7th to Fri 11th).
     */
    private function kalkulator(?Collection $holidays = null): SlaCalculator
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

        return new SlaCalculator(new WorkingCalendar($hours, HolidayCalendar::fromCollection($holidays ?? collect())));
    }

    public function test_it_sets_response_and_recovery_targets_inside_working_time(): void
    {
        $dibuka = Carbon::parse('2026-09-09 10:00:00'); // Rabu

        $sasaran = $this->kalkulator()->kiraSasaran($dibuka, 120, 480);

        $this->assertTrue($sasaran['sasaran_tindak_balas']->equalTo(Carbon::parse('2026-09-09 12:00:00')));
        // 420 minit tinggal hari Rabu, baki 60 minit terus ke Khamis 10:00.
        $this->assertTrue($sasaran['sasaran_pemulihan']->equalTo(Carbon::parse('2026-09-10 10:00:00')));
    }

    public function test_a_friday_afternoon_ticket_recovers_on_monday(): void
    {
        $jumaat = Carbon::parse('2026-09-11 16:00:00');

        $sasaran = $this->kalkulator()->kiraSasaran($jumaat, 30, 240);

        // Tindak balas 30 minit: Jumaat 16:00-16:30. Pemulihan 240 minit:
        // Jumaat baki 60, Isnin 09:00 + 180 = 12:00.
        $this->assertTrue($sasaran['sasaran_tindak_balas']->equalTo(Carbon::parse('2026-09-11 16:30:00')));
        $this->assertTrue($sasaran['sasaran_pemulihan']->equalTo(Carbon::parse('2026-09-14 12:00:00')));
    }

    public function test_elapsed_minutes_exclude_the_weekend(): void
    {
        $minit = $this->kalkulator()->minitBerlalu(
            Carbon::parse('2026-09-11 16:00:00'),
            Carbon::parse('2026-09-14 11:00:00'),
        );

        // Jumaat 60 + Isnin 09:00-11:00 (120) = 180; hujung minggu tidak dikira.
        $this->assertSame(180, $minit);
    }

    public function test_paused_minutes_are_excluded_from_elapsed_time(): void
    {
        $minit = $this->kalkulator()->minitBerlalu(
            Carbon::parse('2026-09-09 09:00:00'),
            Carbon::parse('2026-09-09 17:00:00'),
            60,
        );

        // 480 minit berlalu di jam, tetapi 60 dijeda semasa menunggu vendor.
        $this->assertSame(420, $minit);
    }

    public function test_percentage_reaches_eighty_and_one_hundred(): void
    {
        $kalkulator = $this->kalkulator();
        $dibuka = Carbon::parse('2026-09-09 09:00:00');
        $sasaran = Carbon::parse('2026-09-09 17:00:00'); // 480 minit

        $padaTigaSuku = $kalkulator->peratusanTerpakai($dibuka, $sasaran, 0, Carbon::parse('2026-09-09 15:00:00'));
        $padaTamat = $kalkulator->peratusanTerpakai($dibuka, $sasaran, 0, Carbon::parse('2026-09-09 17:00:00'));

        $this->assertSame(75, $padaTigaSuku);
        $this->assertSame(100, $padaTamat);
        $this->assertTrue($kalkulator->melanggar($dibuka, $sasaran, 0, Carbon::parse('2026-09-10 09:01:00')));
        $this->assertFalse($kalkulator->melanggar($dibuka, $sasaran, 0, Carbon::parse('2026-09-09 16:00:00')));
    }

    public function test_priority_defaults_match_the_brs_targets(): void
    {
        $this->assertSame([30, 240], [TicketPriority::P1->tindakBalasMinitLalai(), TicketPriority::P1->pemulihanMinitLalai()]);
        $this->assertSame([120, 480], [TicketPriority::P2->tindakBalasMinitLalai(), TicketPriority::P2->pemulihanMinitLalai()]);
        $this->assertSame([240, 1440], [TicketPriority::P3->tindakBalasMinitLalai(), TicketPriority::P3->pemulihanMinitLalai()]);
        $this->assertSame([480, 2400], [TicketPriority::P4->tindakBalasMinitLalai(), TicketPriority::P4->pemulihanMinitLalai()]);
    }
}
