<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\Location;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationManagementTest extends TestCase
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

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('admin.locations.index'))->assertRedirect(route('login'));
    }

    public function test_every_role_may_view_the_location_tree(): void
    {
        foreach (array_keys(RolesAndPermissionsSeeder::ROLES) as $role) {
            $this->actingAs($this->userWithRole($role))
                ->get(route('admin.locations.index'))
                ->assertOk();
        }
    }

    public function test_the_tree_shows_children_under_their_parent(): void
    {
        $campus = Location::factory()->create(['name' => 'Kampus Induk']);
        Location::factory()->bangunan()->childOf($campus)->create(['name' => 'Blok A']);

        $this->actingAs($this->admin)
            ->get(route('admin.locations.index'))
            ->assertOk()
            ->assertSee('Kampus Induk')
            ->assertSee('Blok A');
    }

    public function test_search_filters_by_name_or_code(): void
    {
        Location::factory()->create(['name' => 'Kampus Induk', 'code' => 'KI-01']);
        Location::factory()->create(['name' => 'Kampus Cawangan', 'code' => 'KC-01']);

        $this->actingAs($this->admin)
            ->get(route('admin.locations.index', ['search' => 'Cawangan']))
            ->assertOk()
            ->assertSee('Kampus Cawangan')
            ->assertDontSee('Kampus Induk');
    }

    public function test_a_technician_may_not_open_the_create_form(): void
    {
        $this->actingAs($this->userWithRole('juruteknik'))
            ->get(route('admin.locations.create'))
            ->assertForbidden();
    }

    public function test_a_facility_administrator_may_create_a_location(): void
    {
        $this->actingAs($this->userWithRole('pentadbir-fasiliti'))
            ->post(route('admin.locations.store'), [
                'code' => 'KAMPUS-BARU',
                'name' => 'Kampus Baharu',
                'level' => 'kampus',
                'parent_id' => null,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.locations.index'));

        $this->assertDatabaseHas('locations', ['code' => 'KAMPUS-BARU', 'is_active' => true]);
    }

    public function test_creating_a_location_writes_an_audit_entry(): void
    {
        $this->actingAs($this->admin)->post(route('admin.locations.store'), [
            'code' => 'KAMPUS-AUDIT',
            'name' => 'Kampus Audit',
            'level' => 'kampus',
            'parent_id' => null,
            'is_active' => 1,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'location.created',
            'record_type' => 'location',
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_updating_a_location_records_the_previous_name(): void
    {
        $location = Location::factory()->create(['name' => 'Nama Lama', 'code' => 'KOD-A']);

        $this->actingAs($this->admin)->put(route('admin.locations.update', $location), [
            'code' => 'KOD-A',
            'name' => 'Nama Baharu',
            'level' => 'kampus',
            'parent_id' => null,
            'is_active' => 1,
        ])->assertRedirect(route('admin.locations.index'));

        $this->assertDatabaseHas('locations', ['id' => $location->id, 'name' => 'Nama Baharu']);

        $log = AuditLog::query()->where('action', 'location.updated')->latest('id')->first();
        $this->assertSame('Nama Lama', $log->metadata['before']['name']);
        $this->assertSame('Nama Baharu', $log->metadata['after']['name']);
    }

    public function test_renaming_a_location_keeps_the_same_record_for_history(): void
    {
        $location = Location::factory()->create(['name' => 'Nama Lama']);

        $this->actingAs($this->admin)->put(route('admin.locations.update', $location), [
            'code' => $location->code,
            'name' => 'Nama Baharu',
            'level' => 'kampus',
            'parent_id' => null,
            'is_active' => 1,
        ]);

        $this->assertSame($location->id, Location::where('name', 'Nama Baharu')->first()->id);
    }

    public function test_deactivating_a_parent_deactivates_its_descendants(): void
    {
        $campus = Location::factory()->create();
        $building = Location::factory()->bangunan()->childOf($campus)->create();
        $floor = Location::factory()->tingkat()->childOf($building)->create();

        $this->actingAs($this->admin)
            ->patch(route('admin.locations.toggle', $campus))
            ->assertRedirect(route('admin.locations.index'));

        $this->assertFalse($campus->fresh()->is_active);
        $this->assertFalse($building->fresh()->is_active);
        $this->assertFalse($floor->fresh()->is_active);
    }

    public function test_reactivating_a_location_does_not_touch_its_descendants(): void
    {
        $campus = Location::factory()->inactive()->create();
        $building = Location::factory()->bangunan()->inactive()->childOf($campus)->create();

        $this->actingAs($this->admin)->patch(route('admin.locations.toggle', $campus));

        $this->assertTrue($campus->fresh()->is_active);
        $this->assertFalse($building->fresh()->is_active);
    }

    public function test_a_facility_administrator_may_not_delete(): void
    {
        $location = Location::factory()->create();

        $this->actingAs($this->userWithRole('pentadbir-fasiliti'))
            ->delete(route('admin.locations.destroy', $location))
            ->assertForbidden();
    }

    public function test_deletion_is_blocked_when_the_location_has_children(): void
    {
        $campus = Location::factory()->create();
        Location::factory()->bangunan()->childOf($campus)->create();

        $this->actingAs($this->admin)
            ->from(route('admin.locations.index'))
            ->delete(route('admin.locations.destroy', $campus))
            ->assertRedirect(route('admin.locations.index'))
            ->assertSessionHasErrors('id');

        $this->assertDatabaseHas('locations', ['id' => $campus->id]);
    }

    public function test_deletion_is_blocked_when_a_user_references_the_location(): void
    {
        $location = Location::factory()->create();
        User::factory()->create(['primary_location_id' => $location->id]);

        $this->actingAs($this->admin)
            ->from(route('admin.locations.index'))
            ->delete(route('admin.locations.destroy', $location))
            ->assertSessionHasErrors('id');

        $this->assertDatabaseHas('locations', ['id' => $location->id]);
    }

    public function test_the_code_is_stored_in_upper_case(): void
    {
        $this->actingAs($this->admin)->post(route('admin.locations.store'), [
            'code' => 'kampus-kecil',
            'name' => 'Kampus Huruf Kecil',
            'level' => 'kampus',
            'parent_id' => null,
            'is_active' => 1,
        ]);

        $this->assertDatabaseHas('locations', ['code' => 'KAMPUS-KECIL']);
    }

    public function test_deactivating_a_parent_records_the_cascaded_descendants(): void
    {
        $campus = Location::factory()->create();
        $building = Location::factory()->bangunan()->childOf($campus)->create();

        $this->actingAs($this->admin)->patch(route('admin.locations.toggle', $campus));

        $log = AuditLog::query()->where('action', 'location.deactivated')->latest('id')->first();

        $this->assertSame([$building->id], $log->metadata['cascaded_ids']);
    }

    public function test_reactivating_a_location_is_refused_while_its_parent_is_inactive(): void
    {
        $campus = Location::factory()->inactive()->create();
        $building = Location::factory()->bangunan()->inactive()->childOf($campus)->create();

        $this->actingAs($this->admin)
            ->from(route('admin.locations.index'))
            ->patch(route('admin.locations.toggle', $building))
            ->assertSessionHasErrors('id');

        $this->assertFalse($building->fresh()->is_active);
    }

    public function test_a_staff_member_sees_the_tree_without_management_actions(): void
    {
        Location::factory()->create(['name' => 'Kampus Induk']);

        $this->actingAs($this->userWithRole('kakitangan'))
            ->get(route('admin.locations.index'))
            ->assertOk()
            ->assertSee('Kampus Induk')
            ->assertDontSee('Padam')
            ->assertDontSee('Nyahaktif');
    }

    public function test_an_unreferenced_location_is_deleted_and_audited(): void
    {
        $location = Location::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.locations.destroy', $location))
            ->assertRedirect(route('admin.locations.index'));

        $this->assertDatabaseMissing('locations', ['id' => $location->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'location.deleted']);
    }
}
