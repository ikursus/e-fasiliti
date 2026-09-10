<?php

namespace Tests\Feature\Admin;

use App\Enums\BookingStatus;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SystemConfigurationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomDeactivationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SystemConfigurationSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('pentadbir-sistem');
    }

    /**
     * FR-BLK-08: deactivating a room with upcoming bookings first shows
     * the affected list; nothing changes until the operator confirms.
     */
    public function test_deactivating_a_room_with_upcoming_bookings_asks_for_confirmation(): void
    {
        $room = Room::factory()->create();
        $booking = Booking::factory()->upcoming()->confirmed()->create(['room_id' => $room->id]);

        $this->actingAs($this->admin)
            ->patch(route('admin.rooms.toggle', $room))
            ->assertOk()
            ->assertSee($booking->reference_no)
            ->assertSee($booking->title);

        $this->assertTrue($room->fresh()->is_active, 'The room must stay active until confirmation.');
    }

    public function test_confirming_deactivation_deactivates_and_audits_the_affected_count(): void
    {
        $room = Room::factory()->create();
        Booking::factory()->upcoming()->confirmed()->create(['room_id' => $room->id]);
        Booking::factory()->upcoming()->pendingApproval()->create(['room_id' => $room->id]);
        Booking::factory()->create([ // tempahan lampau tidak dikira
            'room_id' => $room->id,
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->subDays(2)->addHours(2),
        ]);

        $this->actingAs($this->admin)
            ->patch(route('admin.rooms.toggle', $room), ['confirm' => 1])
            ->assertRedirect(route('admin.rooms.index'));

        $this->assertFalse($room->fresh()->is_active);

        $log = AuditLog::query()->where('action', 'room.deactivated')->latest('id')->first();

        $this->assertSame(2, $log->metadata['affected_upcoming_bookings']);
    }

    public function test_a_room_without_upcoming_bookings_deactivates_without_a_prompt(): void
    {
        $room = Room::factory()->create();

        $this->actingAs($this->admin)
            ->patch(route('admin.rooms.toggle', $room))
            ->assertRedirect(route('admin.rooms.index'));

        $this->assertFalse($room->fresh()->is_active);
    }

    public function test_reactivating_a_room_is_immediate(): void
    {
        $room = Room::factory()->inactive()->create();

        $this->actingAs($this->admin)
            ->patch(route('admin.rooms.toggle', $room))
            ->assertRedirect(route('admin.rooms.index'));

        $this->assertTrue($room->fresh()->is_active);
    }

    public function test_cancelled_bookings_do_not_block_or_appear(): void
    {
        $room = Room::factory()->create();
        Booking::factory()->create([
            'room_id' => $room->id,
            'starts_at' => now()->addDays(3),
            'ends_at' => now()->addDays(3)->addHours(2),
            'status' => BookingStatus::Dibatalkan,
        ]);

        $this->actingAs($this->admin)
            ->patch(route('admin.rooms.toggle', $room))
            ->assertRedirect(route('admin.rooms.index'));

        $this->assertFalse($room->fresh()->is_active);
    }
}
