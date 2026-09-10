<?php

namespace Tests\Feature\Ticket;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\ReferenceValue;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketEventNotification;
use Illuminate\Support\Facades\Notification;

class TicketOpeningTest extends TicketTestCase
{
    public function test_a_staff_member_opens_a_ticket_and_gets_a_reference_number(): void
    {
        ReferenceValue::factory()->create(['type' => 'jenis_kerosakan', 'code' => 'TIDAK-HIDUP']);

        $response = $this->actingAs($this->kakitangan)
            ->post(route('tiket.store'), $this->dataTiketSah());

        $ticket = Ticket::query()->sole();

        $response->assertRedirect(route('tiket.show', $ticket));
        $this->assertMatchesRegularExpression('/^TKT-\d{6}-\d{5}$/', $ticket->no_tiket);
        $this->assertSame(TicketStatus::Baharu, $ticket->status);
        $this->assertSame(TicketPriority::P2, $ticket->keutamaan);
        $this->assertNotNull($ticket->sasaran_tindak_balas);
        $this->assertNotNull($ticket->sasaran_pemulihan);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'ticket.created',
            'record_type' => 'ticket',
            'record_id' => $ticket->id,
        ]);
    }

    public function test_the_reporter_and_every_supervisor_are_notified(): void
    {
        ReferenceValue::factory()->create(['type' => 'jenis_kerosakan', 'code' => 'TIDAK-HIDUP']);

        $this->actingAs($this->kakitangan)->post(route('tiket.store'), $this->dataTiketSah());

        $ticket = Ticket::query()->sole();

        Notification::assertSentTo($this->kakitangan, TicketEventNotification::class);
        Notification::assertSentTo($this->penyelia, TicketEventNotification::class);
        Notification::assertNotSentTo($this->juruteknik, TicketEventNotification::class);

        $this->assertSame($ticket->pelapor_id, $this->kakitangan->id);
    }

    public function test_an_unknown_fault_category_is_rejected(): void
    {
        $response = $this->actingAs($this->kakitangan)
            ->post(route('tiket.store'), $this->dataTiketSah(['kategori_masalah' => 'TIADA']));

        $response->assertSessionHasErrors('kategori_masalah');
        $this->assertSame(0, Ticket::query()->count());
    }

    public function test_a_too_short_description_is_rejected(): void
    {
        ReferenceValue::factory()->create(['type' => 'jenis_kerosakan', 'code' => 'TIDAK-HIDUP']);

        $this->actingAs($this->kakitangan)
            ->post(route('tiket.store'), $this->dataTiketSah(['keterangan' => 'rosak']))
            ->assertSessionHasErrors('keterangan');
    }

    public function test_a_user_without_the_open_permission_cannot_open_a_ticket(): void
    {
        $pelulus = User::factory()->create();
        $pelulus->assignRole('pelulus');

        $this->actingAs($pelulus)
            ->post(route('tiket.store'), $this->dataTiketSah())
            ->assertForbidden();

        $this->assertSame(0, Ticket::query()->count());
    }

    public function test_the_report_form_renders_for_staff(): void
    {
        ReferenceValue::factory()->create(['type' => 'jenis_kerosakan', 'code' => 'RANGKAIAN', 'label' => 'Masalah rangkaian']);

        $this->actingAs($this->kakitangan)
            ->get(route('tiket.create'))
            ->assertOk()
            ->assertSee('Lapor Kerosakan')
            ->assertSee('Masalah rangkaian');
    }

    public function test_the_reporter_sees_their_tickets_on_the_index(): void
    {
        $ticket = Ticket::factory()->create(['pelapor_id' => $this->kakitangan->id, 'lokasi_id' => $this->lokasi->id]);
        Ticket::factory()->create(['lokasi_id' => $this->lokasi->id]); // tiket orang lain

        $this->actingAs($this->kakitangan)
            ->get(route('tiket.index'))
            ->assertOk()
            ->assertSee($ticket->no_tiket);
    }
}
