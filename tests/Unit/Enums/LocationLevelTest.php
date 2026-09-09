<?php

namespace Tests\Unit\Enums;

use App\Enums\LocationLevel;
use PHPUnit\Framework\TestCase;

class LocationLevelTest extends TestCase
{
    public function test_kampus_is_the_root_level_and_has_no_parent_level(): void
    {
        $this->assertTrue(LocationLevel::Kampus->isRoot());
        $this->assertNull(LocationLevel::Kampus->parentLevel());
    }

    public function test_each_non_root_level_has_the_level_directly_above_it(): void
    {
        $this->assertSame(LocationLevel::Kampus, LocationLevel::Bangunan->parentLevel());
        $this->assertSame(LocationLevel::Bangunan, LocationLevel::Tingkat->parentLevel());
        $this->assertSame(LocationLevel::Tingkat, LocationLevel::Ruang->parentLevel());
    }

    public function test_labels_are_in_malay_title_case(): void
    {
        $this->assertSame('Kampus', LocationLevel::Kampus->label());
        $this->assertSame('Bangunan', LocationLevel::Bangunan->label());
        $this->assertSame('Tingkat', LocationLevel::Tingkat->label());
        $this->assertSame('Ruang', LocationLevel::Ruang->label());
    }

    public function test_values_returns_the_four_levels_in_hierarchy_order(): void
    {
        $this->assertSame(
            ['kampus', 'bangunan', 'tingkat', 'ruang'],
            LocationLevel::values()
        );
    }
}
