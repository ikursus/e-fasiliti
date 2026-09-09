<?php

namespace Tests\Unit\Support;

use App\Enums\LocationLevel;
use App\Support\Hierarchy\HierarchyRules;
use PHPUnit\Framework\TestCase;

class HierarchyRulesTest extends TestCase
{
    public function test_a_campus_must_not_have_a_parent(): void
    {
        $this->assertFalse(HierarchyRules::parentLevelIsValid(LocationLevel::Kampus, LocationLevel::Kampus));
        $this->assertTrue(HierarchyRules::parentLevelIsValid(LocationLevel::Kampus, null));
    }

    public function test_a_building_must_hang_under_a_campus(): void
    {
        $this->assertTrue(HierarchyRules::parentLevelIsValid(LocationLevel::Bangunan, LocationLevel::Kampus));
        $this->assertFalse(HierarchyRules::parentLevelIsValid(LocationLevel::Bangunan, LocationLevel::Tingkat));
        $this->assertFalse(HierarchyRules::parentLevelIsValid(LocationLevel::Bangunan, null));
    }

    public function test_a_room_must_hang_under_a_floor(): void
    {
        $this->assertTrue(HierarchyRules::parentLevelIsValid(LocationLevel::Ruang, LocationLevel::Tingkat));
        $this->assertFalse(HierarchyRules::parentLevelIsValid(LocationLevel::Ruang, LocationLevel::Bangunan));
    }

    public function test_a_record_may_not_become_its_own_parent(): void
    {
        $this->assertTrue(HierarchyRules::createsCycle(7, 7, []));
    }

    public function test_a_record_may_not_be_moved_under_its_own_descendant(): void
    {
        // 7 is the record; 8 and 9 are its descendants.
        $this->assertTrue(HierarchyRules::createsCycle(7, 9, [8, 9]));
        $this->assertFalse(HierarchyRules::createsCycle(7, 4, [8, 9]));
    }

    public function test_no_cycle_when_the_parent_is_cleared(): void
    {
        $this->assertFalse(HierarchyRules::createsCycle(7, null, [8, 9]));
    }
}
