<?php

namespace Tests\Feature\Ticket;

use App\Enums\TicketStatus;
use App\Models\AuditLog;
use App\Models\Ticket;
use App\Services\Ticket\WorkingCalendar;
use Carbon\Carbon;

class TicketAutoCloseTest extends TicketTestCase
{
    public function test_a_ticket_unconfirmed_for_three_working_days_closes_automatically(): void
    {
        $ticket = Ticket::factory()->menungguPengesahan()->create([
            'lokasi_id' => $this->lokasi->id,
            'pelapor_id' => $this->kakitangan->id,
            'masa_kerja_selesai' => Carbon::now()->subDays(10),
        ]);

        $this->artisan('tiket:tutup-automatik')->assertSuccessful();

        $ticket = $ticket->fresh();
        $this->assertSame(TicketStatus::Ditutup, $ticket->status);
        $this->assertTrue($ticket->penutupan_automatik);
        $this->assertNotNull($ticket->masa_ditutup);

        $audit = AuditLog::query()->where('action', 'ticket.auto_closed')->where('record_id', $ticket->id)->sole();
        $this->assertNull($audit->user_id);
    }

    public function test_a_ticket_still_inside_the_confirmation_window_stays_open(): void
    {
        $ticket = Ticket::factory()->menungguPengesahan()->create([
            'lokasi_id' => $this->lokasi->id,
            'masa_kerja_selesai' => Carbon::now()->subDay(),
        ]);

        $this->artisan('tiket:tutup-automatik')->assertSuccessful();

        $this->assertSame(TicketStatus::MenungguPengesahan, $ticket->fresh()->status);
    }

    public function test_the_command_is_safe_to_run_twice(): void
    {
        $ticket = Ticket::factory()->menungguPengesahan()->create([
            'lokasi_id' => $this->lokasi->id,
            'masa_kerja_selesai' => Carbon::now()->subDays(10),
        ]);

        $this->artisan('tiket:tutup-automatik')->assertSuccessful();
        $masaDitutup = $ticket->fresh()->masa_ditutup;

        $this->artisan('tiket:tutup-automatik')->assertSuccessful();

        $this->assertTrue($masaDitutup->equalTo($ticket->fresh()->masa_ditutup));
        $this->assertSame(
            1,
            AuditLog::query()->where('action', 'ticket.auto_closed')->where('record_id', $ticket->id)->count(),
        );
    }

    public function test_three_working_days_spans_five_calendar_days_over_a_weekend(): void
    {
        // Perbandingan kalendar sendiri: Jumaat 16:00 + 3 hari bekerja
        // (8 jam sehari) berakhir pada Rabu, bukan Isnin.
        $calendar = WorkingCalendar::organisationDefault();
        $deadline = $calendar->addWorkingMinutes(Carbon::parse('2026-09-11 16:00:00'), 3 * 480);

        $this->assertTrue($deadline->equalTo(Carbon::parse('2026-09-16 16:00:00')));
    }
}
