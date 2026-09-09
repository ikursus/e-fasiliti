<?php

namespace Tests\Feature\Admin;

use App\Models\Location;
use App\Models\OrganizationUnit;
use App\Models\User;
use Database\Seeders\OrganizationStructureSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectoryNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_the_sidebar_links_to_the_directories_for_every_role(): void
    {
        $this->actingAs($this->userWithRole('kakitangan'))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('admin.locations.index'))
            ->assertSee(route('admin.organization-units.index'));
    }

    public function test_the_sidebar_hides_user_administration_from_non_administrators(): void
    {
        $this->actingAs($this->userWithRole('kakitangan'))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('admin.users.index'));
    }

    /**
     * The plan verifies the seeder with migrate:fresh --seed, which this
     * project forbids because .env points at a real development database.
     * Running it here against the in-memory driver proves the same thing.
     */
    public function test_the_structure_seeder_builds_the_full_location_tree(): void
    {
        $this->seed(OrganizationStructureSeeder::class);

        $this->assertDatabaseHas('locations', ['code' => 'KAMPUS-PUTRAJAYA', 'parent_id' => null]);

        $campus = Location::where('code', 'KAMPUS-PUTRAJAYA')->firstOrFail();
        $building = Location::where('code', 'BANG-A')->firstOrFail();
        $room = Location::where('code', 'BM-A-1-01')->firstOrFail();

        $this->assertSame($campus->id, $building->parent_id);
        $this->assertSame('ruang', $room->level->value);
        $this->assertTrue($room->isDescendantOf($campus));

        $this->assertGreaterThan(0, OrganizationUnit::count());
    }

    public function test_the_structure_seeder_is_safe_to_run_twice(): void
    {
        $this->seed(OrganizationStructureSeeder::class);
        $first = Location::count();

        $this->seed(OrganizationStructureSeeder::class);

        $this->assertSame($first, Location::count());
    }
}
