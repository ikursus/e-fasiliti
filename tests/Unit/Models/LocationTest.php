<?php

namespace Tests\Unit\Models;

use App\Models\Location;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_path_joins_every_ancestor_name(): void
    {
        $campus = Location::factory()->create(['name' => 'Kampus Induk']);
        $building = Location::factory()->bangunan()->childOf($campus)->create(['name' => 'Blok A']);
        $floor = Location::factory()->tingkat()->childOf($building)->create(['name' => 'Tingkat 3']);
        $room = Location::factory()->ruang()->childOf($floor)->create(['name' => 'Bilik Mesyuarat 1']);

        $this->assertSame(
            'Kampus Induk / Blok A / Tingkat 3 / Bilik Mesyuarat 1',
            $room->fullPath()
        );
    }

    public function test_descendant_ids_returns_every_level_below(): void
    {
        $campus = Location::factory()->create();
        $building = Location::factory()->bangunan()->childOf($campus)->create();
        $floor = Location::factory()->tingkat()->childOf($building)->create();
        $room = Location::factory()->ruang()->childOf($floor)->create();

        $ids = $campus->descendantIds();

        sort($ids);
        $expected = [$building->id, $floor->id, $room->id];
        sort($expected);

        $this->assertSame($expected, $ids);
    }

    public function test_is_descendant_of_detects_indirect_ancestry(): void
    {
        $campus = Location::factory()->create();
        $building = Location::factory()->bangunan()->childOf($campus)->create();
        $floor = Location::factory()->tingkat()->childOf($building)->create();

        $this->assertTrue($floor->isDescendantOf($campus));
        $this->assertFalse($campus->isDescendantOf($floor));
    }

    public function test_active_scope_excludes_deactivated_records(): void
    {
        Location::factory()->create();
        Location::factory()->inactive()->create();

        $this->assertSame(1, Location::query()->active()->count());
    }

    public function test_reference_summary_is_empty_for_an_unused_location(): void
    {
        $location = Location::factory()->create();

        $this->assertSame([], $location->referenceSummary());
    }

    public function test_reference_summary_reports_children(): void
    {
        $campus = Location::factory()->create();
        Location::factory()->bangunan()->childOf($campus)->create();

        $this->assertContains('lokasi anak', $campus->referenceSummary());
    }

    public function test_reference_summary_reports_users(): void
    {
        $location = Location::factory()->create();
        User::factory()->create(['primary_location_id' => $location->id]);

        $this->assertContains('pengguna', $location->referenceSummary());
    }

    public function test_reference_summary_reports_rooms(): void
    {
        $location = Location::factory()->ruang()->create();
        Room::factory()->create(['location_id' => $location->id]);

        $this->assertContains('bilik', $location->referenceSummary());
    }

    public function test_reference_summary_reports_children_and_users_together(): void
    {
        $location = Location::factory()->create();
        Location::factory()->bangunan()->childOf($location)->create();
        User::factory()->create(['primary_location_id' => $location->id]);

        $reasons = $location->referenceSummary();

        $this->assertContains('lokasi anak', $reasons);
        $this->assertContains('pengguna', $reasons);
    }
}
