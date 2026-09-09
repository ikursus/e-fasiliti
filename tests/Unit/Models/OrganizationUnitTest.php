<?php

namespace Tests\Unit\Models;

use App\Models\OrganizationUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_path_joins_division_and_unit(): void
    {
        $division = OrganizationUnit::factory()->create(['name' => 'Bahagian Pentadbiran']);
        $unit = OrganizationUnit::factory()->childOf($division)->create(['name' => 'Unit ICT']);

        $this->assertSame('Bahagian Pentadbiran / Unit ICT', $unit->fullPath());
    }

    public function test_descendant_ids_returns_child_units(): void
    {
        $division = OrganizationUnit::factory()->create();
        $unit = OrganizationUnit::factory()->childOf($division)->create();

        $this->assertSame([$unit->id], $division->descendantIds());
    }

    public function test_is_division_is_true_only_without_a_parent(): void
    {
        $division = OrganizationUnit::factory()->create();
        $unit = OrganizationUnit::factory()->childOf($division)->create();

        $this->assertTrue($division->isDivision());
        $this->assertFalse($unit->isDivision());
    }

    public function test_is_descendant_of_detects_a_parent_division(): void
    {
        $division = OrganizationUnit::factory()->create();
        $unit = OrganizationUnit::factory()->childOf($division)->create();

        $this->assertTrue($unit->isDescendantOf($division));
        $this->assertFalse($division->isDescendantOf($unit));
    }

    public function test_active_scope_excludes_deactivated_records(): void
    {
        OrganizationUnit::factory()->create();
        OrganizationUnit::factory()->inactive()->create();

        $this->assertSame(1, OrganizationUnit::query()->active()->count());
    }

    public function test_reference_summary_is_empty_for_an_unused_unit(): void
    {
        $this->assertSame([], OrganizationUnit::factory()->create()->referenceSummary());
    }

    public function test_reference_summary_reports_children(): void
    {
        $division = OrganizationUnit::factory()->create();
        OrganizationUnit::factory()->childOf($division)->create();

        $this->assertContains('unit anak', $division->referenceSummary());
    }

    public function test_reference_summary_reports_users(): void
    {
        $division = OrganizationUnit::factory()->create();
        User::factory()->create(['organization_unit_id' => $division->id]);

        $this->assertContains('pengguna', $division->referenceSummary());
    }

    public function test_reference_summary_reports_children_and_users_together(): void
    {
        $division = OrganizationUnit::factory()->create();
        OrganizationUnit::factory()->childOf($division)->create();
        User::factory()->create(['organization_unit_id' => $division->id]);

        $reasons = $division->referenceSummary();

        $this->assertContains('unit anak', $reasons);
        $this->assertContains('pengguna', $reasons);
    }
}
