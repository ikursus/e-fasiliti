<?php

namespace Tests\Feature\Admin;

use App\Models\Location;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationValidationTest extends TestCase
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

    public function test_a_campus_may_not_have_a_parent(): void
    {
        $campus = Location::factory()->create();

        $this->actingAs($this->admin)
            ->from(route('admin.locations.create'))
            ->post(route('admin.locations.store'), [
                'code' => 'KAMPUS-2',
                'name' => 'Kampus Kedua',
                'level' => 'kampus',
                'parent_id' => $campus->id,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('parent_id');
    }

    public function test_a_building_requires_a_campus_parent(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.locations.create'))
            ->post(route('admin.locations.store'), [
                'code' => 'BLOK-A',
                'name' => 'Blok A',
                'level' => 'bangunan',
                'parent_id' => null,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('parent_id');
    }

    public function test_a_room_may_not_hang_under_a_building(): void
    {
        $campus = Location::factory()->create();
        $building = Location::factory()->bangunan()->childOf($campus)->create();

        $this->actingAs($this->admin)
            ->from(route('admin.locations.create'))
            ->post(route('admin.locations.store'), [
                'code' => 'BILIK-1',
                'name' => 'Bilik Mesyuarat 1',
                'level' => 'ruang',
                'parent_id' => $building->id,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('parent_id');
    }

    public function test_the_code_must_be_unique_within_the_same_parent(): void
    {
        $campus = Location::factory()->create();
        Location::factory()->bangunan()->childOf($campus)->create(['code' => 'BLOK-A']);

        $this->actingAs($this->admin)
            ->from(route('admin.locations.create'))
            ->post(route('admin.locations.store'), [
                'code' => 'BLOK-A',
                'name' => 'Blok A Duplikat',
                'level' => 'bangunan',
                'parent_id' => $campus->id,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('code');
    }

    public function test_the_same_code_is_accepted_under_a_different_parent(): void
    {
        $campusA = Location::factory()->create();
        $campusB = Location::factory()->create();
        Location::factory()->bangunan()->childOf($campusA)->create(['code' => 'BLOK-A']);

        $this->actingAs($this->admin)
            ->post(route('admin.locations.store'), [
                'code' => 'BLOK-A',
                'name' => 'Blok A Kampus B',
                'level' => 'bangunan',
                'parent_id' => $campusB->id,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.locations.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('locations', [
            'code' => 'BLOK-A',
            'parent_id' => $campusB->id,
        ]);
    }

    public function test_two_campuses_may_not_share_a_code(): void
    {
        Location::factory()->create(['code' => 'KAMPUS-1']);

        $this->actingAs($this->admin)
            ->from(route('admin.locations.create'))
            ->post(route('admin.locations.store'), [
                'code' => 'KAMPUS-1',
                'name' => 'Kampus Duplikat',
                'level' => 'kampus',
                'parent_id' => null,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('code');
    }

    public function test_a_location_may_not_become_its_own_parent(): void
    {
        $campus = Location::factory()->create();
        $building = Location::factory()->bangunan()->childOf($campus)->create();

        $this->actingAs($this->admin)
            ->from(route('admin.locations.edit', $building))
            ->put(route('admin.locations.update', $building), [
                'code' => $building->code,
                'name' => $building->name,
                'level' => 'bangunan',
                'parent_id' => $building->id,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('parent_id');
    }

    public function test_the_level_may_not_change_while_children_exist(): void
    {
        $campus = Location::factory()->create();
        $building = Location::factory()->bangunan()->childOf($campus)->create();
        Location::factory()->tingkat()->childOf($building)->create();

        $this->actingAs($this->admin)
            ->from(route('admin.locations.edit', $building))
            ->put(route('admin.locations.update', $building), [
                'code' => $building->code,
                'name' => $building->name,
                'level' => 'tingkat',
                'parent_id' => $campus->id,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('level');
    }

    public function test_a_code_differing_only_by_letter_case_is_rejected(): void
    {
        Location::factory()->create(['code' => 'KAMPUS-1']);

        $this->actingAs($this->admin)
            ->from(route('admin.locations.create'))
            ->post(route('admin.locations.store'), [
                'code' => 'kampus-1',
                'name' => 'Kampus Huruf Kecil',
                'level' => 'kampus',
                'parent_id' => null,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('code');
    }

    /**
     * The browser submits an unselected parent as an empty string, not null.
     * ConvertEmptyStringsToNull turns it back into null, and this test pins
     * that: without it the root-level uniqueness check would never match.
     */
    public function test_an_empty_parent_field_is_treated_as_no_parent(): void
    {
        Location::factory()->create(['code' => 'KAMPUS-1']);

        $this->actingAs($this->admin)
            ->from(route('admin.locations.create'))
            ->post(route('admin.locations.store'), [
                'code' => 'KAMPUS-1',
                'name' => 'Kampus Duplikat',
                'level' => 'kampus',
                'parent_id' => '',
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('code');
    }

    public function test_an_active_location_may_not_be_created_under_an_inactive_parent(): void
    {
        $campus = Location::factory()->inactive()->create();

        $this->actingAs($this->admin)
            ->from(route('admin.locations.create'))
            ->post(route('admin.locations.store'), [
                'code' => 'BLOK-A',
                'name' => 'Blok A',
                'level' => 'bangunan',
                'parent_id' => $campus->id,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('is_active');
    }

    public function test_an_inactive_location_may_still_be_created_under_an_inactive_parent(): void
    {
        $campus = Location::factory()->inactive()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.locations.store'), [
                'code' => 'BLOK-A',
                'name' => 'Blok A',
                'level' => 'bangunan',
                'parent_id' => $campus->id,
                'is_active' => 0,
            ])
            ->assertRedirect(route('admin.locations.index'))
            ->assertSessionHasNoErrors();
    }

    public function test_a_location_may_not_be_activated_while_its_parent_is_inactive(): void
    {
        $campus = Location::factory()->inactive()->create();
        $building = Location::factory()->bangunan()->inactive()->childOf($campus)->create();

        $this->actingAs($this->admin)
            ->from(route('admin.locations.edit', $building))
            ->put(route('admin.locations.update', $building), [
                'code' => $building->code,
                'name' => $building->name,
                'level' => 'bangunan',
                'parent_id' => $campus->id,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('is_active');
    }
}
