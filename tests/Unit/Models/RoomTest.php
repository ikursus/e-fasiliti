<?php

namespace Tests\Unit\Models;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Location;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomTest extends TestCase
{
    use RefreshDatabase;

    public function test_location_full_path_walks_the_whole_hierarchy(): void
    {
        $kampus = Location::factory()->create(['name' => 'Kampus Induk']);
        $bangunan = Location::factory()->bangunan()->childOf($kampus)->create(['name' => 'Blok A']);
        $tingkat = Location::factory()->tingkat()->childOf($bangunan)->create(['name' => 'Tingkat 3']);
        $ruang = Location::factory()->ruang()->childOf($tingkat)->create(['name' => 'Ruang 3A']);

        $room = Room::factory()->create(['location_id' => $ruang->id, 'name' => 'Bilik Mesyuarat 1']);

        $this->assertSame(
            'Kampus Induk / Blok A / Tingkat 3 / Ruang 3A',
            $room->locationFullPath()
        );
        $this->assertTrue($ruang->rooms()->whereKey($room->id)->exists());
    }

    public function test_active_scope_keeps_only_rooms_in_use(): void
    {
        Room::factory()->create(['code' => 'BM-AKTIF']);
        Room::factory()->inactive()->create(['code' => 'BM-TUTUP']);

        $codes = Room::query()->active()->pluck('code')->all();

        $this->assertSame(['BM-AKTIF'], $codes);
    }

    public function test_reference_summary_flags_any_booking_history(): void
    {
        $room = Room::factory()->create();
        $this->assertSame([], $room->referenceSummary());

        Booking::factory()
            ->upcoming()
            ->pendingApproval()
            ->create(['room_id' => $room->id, 'status' => BookingStatus::MenungguKelulusan]);

        $this->assertSame(['tempahan'], $room->refresh()->referenceSummary());
    }

    public function test_upcoming_bookings_scope_keeps_only_future_slot_holding_rows(): void
    {
        $room = Room::factory()->create();

        $future = Booking::factory()->upcoming()->confirmed()->create(['room_id' => $room->id]);
        Booking::factory()->upcoming()->pendingApproval()->create(['room_id' => $room->id]);
        Booking::factory()->create([ // lepas
            'room_id' => $room->id,
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->subDays(2)->addHours(2),
        ]);
        Booking::factory()->create([ // masa depan tetapi dibatalkan
            'room_id' => $room->id,
            'starts_at' => now()->addDays(3),
            'ends_at' => now()->addDays(3)->addHours(2),
            'status' => BookingStatus::Dibatalkan,
        ]);

        $this->assertEqualsCanonicalizing(
            [$future->id, Booking::query()->where('status', BookingStatus::MenungguKelulusan)->sole()->id],
            $room->upcomingBookings()->pluck('id')->all(),
        );
    }
}
