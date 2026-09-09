<?php

namespace Tests\Unit\Factories;

use App\Models\Location;
use App\Models\OrganizationUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HierarchyFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_location_factory_creates_an_active_campus_by_default(): void
    {
        $location = Location::factory()->create();

        $this->assertSame('kampus', $location->level->value);
        $this->assertNull($location->parent_id);
        $this->assertTrue($location->is_active);
    }

    public function test_location_factory_can_build_a_child_at_a_given_level(): void
    {
        $campus = Location::factory()->create();
        $building = Location::factory()->bangunan()->childOf($campus)->create();

        $this->assertSame('bangunan', $building->level->value);
        $this->assertSame($campus->id, $building->parent_id);
    }

    public function test_organization_unit_factory_creates_an_active_division(): void
    {
        $unit = OrganizationUnit::factory()->create();

        $this->assertNull($unit->parent_id);
        $this->assertTrue($unit->is_active);
    }
}
