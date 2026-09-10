<?php

namespace Tests\Feature\Ticket;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketEventNotification;
use Illuminate\Support\Facades\Notification;

class TicketAssignmentTest extends TicketTestCase
{
    public function test_a_supervisor_assigns_a_new_ticket_to_a_technician(): void
    {
        Notification::fake();

        $ticket = Ticket::factory()->create(['lokasi_id' => $this->lokasi->id]);

        $response = $this->actingAs($this->penyelia)
            ->post(route('tiket.agih', $ticket), ['juruteknik_id' => $this->juruteknik->id]);

        $response->assertRedirect(route('tiket.show', $ticket));
        $this->assertSame($this->juruteknik->id, $ticket->fresh()->juruteknik_id);
        $this->assertSame(TicketStatus::Diagih, $ticket->fresh()->status);

        Notification::assertSentTo($this->juruteknik, TicketEventNotification::class);
        $this->assertDatabaseHas('audit_logs', ['action' => 'ticket.assigned']);
    }

    public function test_the_assignee_must_hold_the_technician_role(): void
    {
        $ticket = Ticket::factory()->create(['lokasi_id' => $this->lokasi->id]);

        $this->actingAs($this->penyelia)
            ->post(route('tiket.agih', $ticket), ['juruteknik_id' => $this->kakitangan->id])
            ->assertSessionHasErrors('juruteknik_id');

        $this->assertNull($ticket->fresh()->juruteknik_id);
    }

    public function test_bulk_assignment_moves_only_tickets_that_accept_a_technician(): void
    {
        Notification::fake();

        $baharu = Ticket::factory()->count(3)->create(['lokasi_id' => $this->lokasi->id]);
        $ditutup = Ticket::factory()->ditutup()->create(['lokasi_id' => $this->lokasi->id]);

        $ids = $baharu->pluck('id')->merge([$ditutup->id])->all();

        $response = $this->actingAs($this->penyelia)
            ->post(route('tiket.agih-pukal'), ['juruteknik_id' => $this->juruteknik->id, 'ids' => $ids]);

        $response->assertRedirect();
        $response->assertSessionHas('status', fn (string $status) => str_contains($status, '3 tiket diagihkan'));

        foreach ($baharu as $ticket) {
            $this->assertSame(TicketStatus::Diagih, $ticket->fresh()->status);
        }

        $this->assertSame(TicketStatus::Ditutup, $ditutup->fresh()->status);
    }

    public function test_a_technician_cannot_assign_tickets(): void
    {
        $ticket = Ticket::factory()->create(['lokasi_id' => $this->lokasi->id]);

        $this->actingAs($this->juruteknik)
            ->post(route('tiket.agih', $ticket), ['juruteknik_id' => $this->juruteknik->id])
            ->assertForbidden();
    }

    public function test_a_technician_sees_only_their_open_tasks_sorted_by_priority(): void
    {
        $p3 = Ticket::factory()->diagih()->keutamaan(TicketPriority::P3)->create([
            'juruteknik_id' => $this->juruteknik->id,
            'lokasi_id' => $this->lokasi->id,
        ]);
        $p1 = Ticket::factory()->diagih()->keutamaan(TicketPriority::P1)->create([
            'juruteknik_id' => $this->juruteknik->id,
            'lokasi_id' => $this->lokasi->id,
        ]);

        $response = $this->actingAs($this->juruteknik)->get(route('tiket.tugasan'));

        $response->assertOk();
        $this->assertSame(
            [$p1->id, $p3->id],
            $response->viewData('tickets')->getCollection()->pluck('id')->all(),
        );
    }

    public function test_the_technician_task_list_excludes_other_technicians_tickets(): void
    {
        $juruteknikLain = User::factory()->create();
        $juruteknikLain->assignRole('juruteknik');

        $tiketLain = Ticket::factory()->diagih()->create([
            'juruteknik_id' => $juruteknikLain->id,
            'lokasi_id' => $this->lokasi->id,
        ]);

        $response = $this->actingAs($this->juruteknik)->get(route('tiket.tugasan'));

        $this->assertNotContains(
            $tiketLain->id,
            $response->viewData('tickets')->getCollection()->pluck('id')->all(),
        );
    }
}
