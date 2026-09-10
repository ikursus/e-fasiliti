<?php

namespace Tests\Feature\Ticket;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Notifications\TicketEventNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

class TicketWorkflowTest extends TicketTestCase
{
    private function tiketDalamTindakan(): Ticket
    {
        return Ticket::factory()->dalamTindakan()->create([
            'juruteknik_id' => $this->juruteknik->id,
            'lokasi_id' => $this->lokasi->id,
            'pelapor_id' => $this->kakitangan->id,
            'masa_dibuka' => Carbon::now(),
            'sasaran_pemulihan' => Carbon::now()->addDays(30),
        ]);
    }

    public function test_the_technician_starts_work_and_the_first_response_time_is_recorded(): void
    {
        $ticket = Ticket::factory()->diagih()->create([
            'juruteknik_id' => $this->juruteknik->id,
            'lokasi_id' => $this->lokasi->id,
        ]);

        $this->actingAs($this->juruteknik)
            ->post(route('tiket.mula', $ticket))
            ->assertRedirect(route('tiket.show', $ticket));

        $this->assertSame(TicketStatus::DalamTindakan, $ticket->fresh()->status);
        $this->assertNotNull($ticket->fresh()->masa_tindak_balas_pertama);
    }

    public function test_work_cannot_start_before_assignment(): void
    {
        $ticket = Ticket::factory()->create(['lokasi_id' => $this->lokasi->id]);

        $this->actingAs($this->juruteknik)
            ->post(route('tiket.mula', $ticket))
            ->assertSessionHasErrors('status');

        $this->assertSame(TicketStatus::Baharu, $ticket->fresh()->status);
    }

    public function test_the_technician_records_diagnosis_and_repair_details(): void
    {
        $ticket = $this->tiketDalamTindakan();

        $this->actingAs($this->juruteknik)
            ->put(route('tiket.kerja', $ticket), [
                'diagnosis' => 'Unit bekalan kuasa rosak.',
                'tindakan' => 'Unit bekalan kuasa digantikan.',
                'kos_pembaikan' => 180.00,
                'masa_kerja_minit' => 45,
            ])
            ->assertRedirect(route('tiket.show', $ticket));

        $this->assertSame('Unit bekalan kuasa rosak.', $ticket->fresh()->diagnosis);
        $this->assertSame('180.00', (string) $ticket->fresh()->kos_pembaikan);
        $this->assertSame(45, $ticket->fresh()->masa_kerja_minit);
    }

    public function test_internal_notes_are_hidden_from_the_reporter(): void
    {
        $ticket = $this->tiketDalamTindakan();

        $this->actingAs($this->juruteknik)
            ->post(route('tiket.catatan', $ticket), ['catatan' => 'Catatan awam untuk pelapor.'])
            ->assertRedirect();

        $this->actingAs($this->juruteknik)
            ->post(route('tiket.catatan', $ticket), [
                'catatan' => 'Catatan dalaman tentang kos.',
                'boleh_dilihat_pelapor' => '0',
            ])
            ->assertRedirect();

        $this->actingAs($this->kakitangan)
            ->get(route('tiket.show', $ticket))
            ->assertOk()
            ->assertSee('Catatan awam untuk pelapor.')
            ->assertDontSee('Catatan dalaman tentang kos.');
    }

    public function test_completion_asks_the_reporter_to_confirm_before_closing(): void
    {
        Notification::fake();

        $ticket = $this->tiketDalamTindakan();

        $this->actingAs($this->juruteknik)
            ->post(route('tiket.selesai', $ticket))
            ->assertRedirect(route('tiket.show', $ticket));

        $this->assertSame(TicketStatus::MenungguPengesahan, $ticket->fresh()->status);
        $this->assertNotNull($ticket->fresh()->masa_kerja_selesai);
        Notification::assertSentTo($this->kakitangan, TicketEventNotification::class);
    }

    public function test_the_reporter_confirms_and_the_ticket_closes_within_sla(): void
    {
        $ticket = $this->tiketDalamTindakan();
        $ticket->update(['status' => TicketStatus::MenungguPengesahan, 'masa_kerja_selesai' => Carbon::now()]);

        $this->actingAs($this->kakitangan)
            ->post(route('tiket.sahkan', $ticket))
            ->assertRedirect(route('tiket.show', $ticket));

        $this->assertSame(TicketStatus::Ditutup, $ticket->fresh()->status);
        $this->assertTrue($ticket->fresh()->sla_dipatuhi);
        $this->assertDatabaseHas('audit_logs', ['action' => 'ticket.confirmed']);
    }

    public function test_only_the_reporter_may_confirm_closure(): void
    {
        $ticket = $this->tiketDalamTindakan();
        $ticket->update(['status' => TicketStatus::MenungguPengesahan]);

        $this->actingAs($this->penyelia)
            ->post(route('tiket.sahkan', $ticket))
            ->assertForbidden();

        $this->assertSame(TicketStatus::MenungguPengesahan, $ticket->fresh()->status);
    }

    public function test_reopening_returns_the_ticket_to_the_same_technician(): void
    {
        Notification::fake();

        $ticket = $this->tiketDalamTindakan();
        $ticket->update(['status' => TicketStatus::MenungguPengesahan, 'masa_kerja_selesai' => Carbon::now()]);

        $this->actingAs($this->kakitangan)
            ->post(route('tiket.buka-semula', $ticket), ['alasan' => 'Komputer masih tidak menyala.'])
            ->assertRedirect(route('tiket.show', $ticket));

        $this->assertSame(TicketStatus::DalamTindakan, $ticket->fresh()->status);
        $this->assertSame($this->juruteknik->id, $ticket->fresh()->juruteknik_id);
        $this->assertDatabaseHas('audit_logs', ['action' => 'ticket.reopened']);
        Notification::assertSentTo($this->juruteknik, TicketEventNotification::class);
    }

    public function test_reopening_requires_a_reason(): void
    {
        $ticket = $this->tiketDalamTindakan();
        $ticket->update(['status' => TicketStatus::MenungguPengesahan]);

        $this->actingAs($this->kakitangan)
            ->post(route('tiket.buka-semula', $ticket), ['alasan' => ''])
            ->assertSessionHasErrors('alasan');
    }

    public function test_a_supervisor_may_withdraw_an_invalid_report(): void
    {
        Notification::fake();

        $ticket = Ticket::factory()->create([
            'pelapor_id' => $this->kakitangan->id,
            'lokasi_id' => $this->lokasi->id,
        ]);

        $this->actingAs($this->penyelia)
            ->post(route('tiket.batal', $ticket), ['sebab_batal' => 'Laporan tidak sah.'])
            ->assertRedirect(route('tiket.show', $ticket));

        $this->assertSame(TicketStatus::Dibatalkan, $ticket->fresh()->status);
        Notification::assertSentTo($this->kakitangan, TicketEventNotification::class);
    }

    public function test_a_staff_member_cannot_reach_technician_actions(): void
    {
        $ticket = Ticket::factory()->diagih()->create([
            'juruteknik_id' => $this->juruteknik->id,
            'lokasi_id' => $this->lokasi->id,
        ]);

        $this->actingAs($this->kakitangan)
            ->post(route('tiket.mula', $ticket))
            ->assertForbidden();
    }
}
