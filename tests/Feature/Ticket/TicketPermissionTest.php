<?php

namespace Tests\Feature\Ticket;

use App\Models\Ticket;
use App\Models\User;

class TicketPermissionTest extends TicketTestCase
{
    public function test_a_reporter_cannot_open_someone_elses_ticket_by_changing_the_url_id(): void
    {
        $pelaporLain = User::factory()->create();
        $pelaporLain->assignRole('kakitangan');

        $ticket = Ticket::factory()->create([
            'pelapor_id' => $pelaporLain->id,
            'lokasi_id' => $this->lokasi->id,
        ]);

        $this->actingAs($this->kakitangan)
            ->get(route('tiket.show', $ticket))
            ->assertForbidden();
    }

    public function test_the_assigned_technician_may_view_the_ticket(): void
    {
        $ticket = Ticket::factory()->diagih()->create([
            'juruteknik_id' => $this->juruteknik->id,
            'lokasi_id' => $this->lokasi->id,
        ]);

        $this->actingAs($this->juruteknik)
            ->get(route('tiket.show', $ticket))
            ->assertOk()
            ->assertSee($ticket->no_tiket);
    }

    public function test_a_supervisor_may_view_any_ticket(): void
    {
        $ticket = Ticket::factory()->create(['lokasi_id' => $this->lokasi->id]);

        $this->actingAs($this->penyelia)
            ->get(route('tiket.show', $ticket))
            ->assertOk()
            ->assertSee($ticket->no_tiket);
    }

    public function test_the_supervisor_list_renders_with_workload_and_filters(): void
    {
        Ticket::factory()->diagih()->create([
            'juruteknik_id' => $this->juruteknik->id,
            'lokasi_id' => $this->lokasi->id,
        ]);

        $this->actingAs($this->penyelia)
            ->get(route('tiket.senarai'))
            ->assertOk()
            ->assertSee('Beban Kerja Juruteknik');

        $this->actingAs($this->penyelia)
            ->get(route('tiket.senarai', ['status' => 'baharu']))
            ->assertOk();
    }

    public function test_read_only_roles_cannot_reach_the_assignment_queue_actions(): void
    {
        $ticket = Ticket::factory()->create(['lokasi_id' => $this->lokasi->id]);

        $pegawaiAset = User::factory()->create();
        $pegawaiAset->assignRole('pegawai-aset');

        $this->actingAs($pegawaiAset)
            ->post(route('tiket.agih', $ticket), ['juruteknik_id' => $this->juruteknik->id])
            ->assertForbidden();
    }

    public function test_an_unauthenticated_user_is_redirected_to_login(): void
    {
        $this->get(route('tiket.index'))->assertRedirect('/login');
    }
}
