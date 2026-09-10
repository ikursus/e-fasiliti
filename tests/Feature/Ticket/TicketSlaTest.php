<?php

namespace Tests\Feature\Ticket;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Notifications\TicketEventNotification;
use App\Services\Ticket\WorkingCalendar;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

class TicketSlaTest extends TicketTestCase
{
    public function test_changing_priority_recomputes_the_targets_and_requires_a_reason(): void
    {
        $ticket = Ticket::factory()->diagih()->keutamaan(TicketPriority::P3)->create([
            'juruteknik_id' => $this->juruteknik->id,
            'lokasi_id' => $this->lokasi->id,
            'masa_dibuka' => Carbon::parse('2026-09-09 09:00:00'),
            'sasaran_tindak_balas' => Carbon::parse('2026-09-09 13:00:00'),
            'sasaran_pemulihan' => Carbon::parse('2026-09-10 09:00:00'),
        ]);

        $this->actingAs($this->penyelia)
            ->post(route('tiket.keutamaan', $ticket), ['keutamaan' => 'P1', 'sebab' => 'Seluruh tingkat terjejas.'])
            ->assertRedirect(route('tiket.show', $ticket));

        $ticket = $ticket->fresh();

        $this->assertSame(TicketPriority::P1, $ticket->keutamaan);
        // P1: 30 minit tindak balas, 240 minit pemulihan dari Rabu 09:00.
        $this->assertTrue($ticket->sasaran_tindak_balas->equalTo(Carbon::parse('2026-09-09 09:30:00')));
        $this->assertTrue($ticket->sasaran_pemulihan->equalTo(Carbon::parse('2026-09-09 13:00:00')));
        $this->assertDatabaseHas('audit_logs', ['action' => 'ticket.priority_changed']);
    }

    public function test_changing_priority_without_a_reason_is_rejected(): void
    {
        $ticket = Ticket::factory()->diagih()->create([
            'juruteknik_id' => $this->juruteknik->id,
            'lokasi_id' => $this->lokasi->id,
        ]);

        $this->actingAs($this->penyelia)
            ->post(route('tiket.keutamaan', $ticket), ['keutamaan' => 'P1', 'sebab' => ''])
            ->assertSessionHasErrors('sebab');
    }

    public function test_the_alert_sweep_warns_at_the_breach_and_does_not_repeat(): void
    {
        Notification::fake();

        $ticket = Ticket::factory()->dalamTindakan()->create([
            'juruteknik_id' => $this->juruteknik->id,
            'lokasi_id' => $this->lokasi->id,
            'masa_dibuka' => Carbon::now()->subDays(5),
            'sasaran_pemulihan' => Carbon::now()->subDays(2),
        ]);

        $this->artisan('tiket:amaran-sla')->assertSuccessful();

        $capMasa = $ticket->fresh()->amaran_100_dihantar_pada;
        $this->assertNotNull($capMasa);

        Notification::assertSentTo($this->penyelia, TicketEventNotification::class);
        Notification::assertSentTo($this->juruteknik, TicketEventNotification::class);

        $this->artisan('tiket:amaran-sla')->assertSuccessful();

        $this->assertTrue($capMasa->equalTo($ticket->fresh()->amaran_100_dihantar_pada));
    }

    public function test_tickets_waiting_for_a_vendor_are_skipped_by_the_alert_sweep(): void
    {
        Notification::fake();

        $ticket = Ticket::factory()->menungguVendor()->create([
            'juruteknik_id' => $this->juruteknik->id,
            'lokasi_id' => $this->lokasi->id,
            'masa_dibuka' => Carbon::now()->subDays(5),
            'sasaran_pemulihan' => Carbon::now()->subDays(2),
        ]);

        $this->artisan('tiket:amaran-sla')->assertSuccessful();

        $this->assertNull($ticket->fresh()->amaran_100_dihantar_pada);
        Notification::assertNothingSentTo($this->penyelia);
    }

    public function test_resuming_after_a_vendor_pause_shifts_the_targets(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-09 15:00:00')); // Rabu

        $ticket = Ticket::factory()->dalamTindakan()->create([
            'juruteknik_id' => $this->juruteknik->id,
            'lokasi_id' => $this->lokasi->id,
            'masa_dibuka' => Carbon::parse('2026-09-09 09:00:00'),
            'sasaran_tindak_balas' => Carbon::parse('2026-09-09 09:30:00'),
            'sasaran_pemulihan' => Carbon::parse('2026-09-09 17:00:00'),
            'no_rujukan_vendor' => 'VEN-9999',
            'vendor_nama' => 'Vendor Ujian',
        ]);

        $ticket->forceFill([
            'status' => TicketStatus::MenungguVendor,
            'jeda_mula_pada' => Carbon::parse('2026-09-09 10:00:00'),
        ])->save();

        $this->actingAs($this->juruteknik)
            ->post(route('tiket.sambung-vendor', $ticket))
            ->assertRedirect(route('tiket.show', $ticket));

        $ticket = $ticket->fresh();

        // Dijeda dari 10:00 ke 15:00 = 300 minit bekerja. Sasaran digerakkan
        // ke hadapan dengan jumlah yang sama: 17:00 + 300 minit bekerja
        // berakhir pada Khamis 14:00 (Rabu tinggal 120, baki 180 ke Khamis).
        $this->assertSame(300, $ticket->minit_jeda_sla);
        $this->assertNull($ticket->jeda_mula_pada);
        $this->assertTrue($ticket->sasaran_pemulihan->equalTo(Carbon::parse('2026-09-10 14:00:00')));
        $this->assertSame(TicketStatus::DalamTindakan, $ticket->status);

        Carbon::setTestNow();
    }

    public function test_the_service_calendar_matches_the_manual_computation(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-09 15:00:00'));

        $calendar = WorkingCalendar::organisationDefault();
        $sasaran = $calendar->addWorkingMinutes(Carbon::parse('2026-09-09 10:00:00'), 300);

        // 300 minit muat sepenuhnya dalam hari Rabu (09:00-17:00).
        $this->assertTrue($sasaran->equalTo(Carbon::parse('2026-09-09 15:00:00')));

        Carbon::setTestNow();
    }
}
