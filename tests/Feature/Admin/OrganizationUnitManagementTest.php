<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\OrganizationUnit;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationUnitManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('pentadbir-sistem');
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_every_role_may_view_the_unit_tree(): void
    {
        foreach (array_keys(RolesAndPermissionsSeeder::ROLES) as $role) {
            $this->actingAs($this->userWithRole($role))
                ->get(route('admin.organization-units.index'))
                ->assertOk();
        }
    }

    public function test_a_facility_administrator_may_not_create_units(): void
    {
        $this->actingAs($this->userWithRole('pentadbir-fasiliti'))
            ->get(route('admin.organization-units.create'))
            ->assertForbidden();
    }

    public function test_the_administrator_may_create_a_division(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.organization-units.store'), [
                'code' => 'BHG-BARU',
                'name' => 'Bahagian Baharu',
                'parent_id' => null,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.organization-units.index'));

        $this->assertDatabaseHas('organization_units', ['code' => 'BHG-BARU']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'organization_unit.created']);
    }

    public function test_a_unit_may_not_hang_under_another_unit(): void
    {
        $division = OrganizationUnit::factory()->create();
        $unit = OrganizationUnit::factory()->childOf($division)->create();

        $this->actingAs($this->admin)
            ->from(route('admin.organization-units.create'))
            ->post(route('admin.organization-units.store'), [
                'code' => 'TERLALU-DALAM',
                'name' => 'Unit Aras Ketiga',
                'parent_id' => $unit->id,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('parent_id');
    }

    public function test_a_division_with_children_may_not_become_a_unit(): void
    {
        $division = OrganizationUnit::factory()->create();
        OrganizationUnit::factory()->childOf($division)->create();
        $other = OrganizationUnit::factory()->create();

        $this->actingAs($this->admin)
            ->from(route('admin.organization-units.edit', $division))
            ->put(route('admin.organization-units.update', $division), [
                'code' => $division->code,
                'name' => $division->name,
                'parent_id' => $other->id,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('parent_id');
    }

    public function test_deactivating_a_division_deactivates_its_units(): void
    {
        $division = OrganizationUnit::factory()->create();
        $unit = OrganizationUnit::factory()->childOf($division)->create();

        $this->actingAs($this->admin)
            ->patch(route('admin.organization-units.toggle', $division))
            ->assertRedirect(route('admin.organization-units.index'));

        $this->assertFalse($division->fresh()->is_active);
        $this->assertFalse($unit->fresh()->is_active);
    }

    public function test_deletion_is_blocked_when_a_user_belongs_to_the_unit(): void
    {
        $unit = OrganizationUnit::factory()->create();
        User::factory()->create(['organization_unit_id' => $unit->id]);

        $this->actingAs($this->admin)
            ->from(route('admin.organization-units.index'))
            ->delete(route('admin.organization-units.destroy', $unit))
            ->assertSessionHasErrors('id');

        $this->assertDatabaseHas('organization_units', ['id' => $unit->id]);
    }

    public function test_an_unreferenced_unit_is_deleted(): void
    {
        $unit = OrganizationUnit::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.organization-units.destroy', $unit))
            ->assertRedirect(route('admin.organization-units.index'));

        $this->assertDatabaseMissing('organization_units', ['id' => $unit->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'organization_unit.deleted']);
    }

    public function test_the_deletion_audit_keeps_the_snapshot_of_the_removed_unit(): void
    {
        $unit = OrganizationUnit::factory()->create(['code' => 'BHG-LAMA', 'name' => 'Bahagian Lama']);

        $this->actingAs($this->admin)->delete(route('admin.organization-units.destroy', $unit));

        $log = AuditLog::query()->where('action', 'organization_unit.deleted')->latest('id')->first();

        $this->assertSame('BHG-LAMA', $log->metadata['before']['code']);
        $this->assertSame('Bahagian Lama', $log->metadata['before']['name']);
    }

    public function test_the_code_is_stored_in_upper_case(): void
    {
        $this->actingAs($this->admin)->post(route('admin.organization-units.store'), [
            'code' => 'bhg-kecil',
            'name' => 'Bahagian Huruf Kecil',
            'parent_id' => null,
            'is_active' => 1,
        ]);

        $this->assertDatabaseHas('organization_units', ['code' => 'BHG-KECIL']);
    }

    public function test_an_active_unit_may_not_be_created_under_an_inactive_division(): void
    {
        $division = OrganizationUnit::factory()->inactive()->create();

        $this->actingAs($this->admin)
            ->from(route('admin.organization-units.create'))
            ->post(route('admin.organization-units.store'), [
                'code' => 'UNIT-BARU',
                'name' => 'Unit Baharu',
                'parent_id' => $division->id,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('is_active');
    }

    public function test_a_unit_may_not_be_reactivated_while_its_division_is_inactive(): void
    {
        $division = OrganizationUnit::factory()->inactive()->create();
        $unit = OrganizationUnit::factory()->inactive()->childOf($division)->create();

        $this->actingAs($this->admin)
            ->from(route('admin.organization-units.index'))
            ->patch(route('admin.organization-units.toggle', $unit))
            ->assertSessionHasErrors('id');

        $this->assertFalse($unit->fresh()->is_active);
    }

    public function test_deactivating_a_division_records_the_cascaded_units(): void
    {
        $division = OrganizationUnit::factory()->create();
        $unit = OrganizationUnit::factory()->childOf($division)->create();

        $this->actingAs($this->admin)->patch(route('admin.organization-units.toggle', $division));

        $log = AuditLog::query()->where('action', 'organization_unit.deactivated')->latest('id')->first();

        $this->assertSame([$unit->id], $log->metadata['cascaded_ids']);
    }

    public function test_a_staff_member_sees_the_tree_without_management_actions(): void
    {
        OrganizationUnit::factory()->create(['name' => 'Bahagian Khidmat']);

        $this->actingAs($this->userWithRole('kakitangan'))
            ->get(route('admin.organization-units.index'))
            ->assertOk()
            ->assertSee('Bahagian Khidmat')
            ->assertDontSee('Padam')
            ->assertDontSee('Nyahaktif');
    }
}
