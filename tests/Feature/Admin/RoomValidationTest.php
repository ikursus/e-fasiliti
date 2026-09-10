<?php

namespace Tests\Feature\Admin;

use App\Models\Location;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SystemConfigurationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SystemConfigurationSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('pentadbir-sistem');
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function roomPayload(array $overrides = []): array
    {
        $ruang = Location::factory()->ruang()->create();

        return array_merge([
            'code' => 'BM-SAH-01',
            'name' => 'Bilik Sah',
            'location_id' => $ruang->id,
            'base_capacity' => 20,
            'min_duration_minutes' => 30,
            'max_duration_minutes' => 480,
            'is_active' => 1,
            'layouts' => [
                ['layout_code' => 'TEATER', 'capacity' => 30, 'is_default' => 1],
            ],
        ], $overrides);
    }

    public function test_the_code_must_be_unique_case_insensitively(): void
    {
        $this->actingAs($this->admin)->post(route('admin.rooms.store'), $this->roomPayload());

        $this->actingAs($this->admin)
            ->post(route('admin.rooms.store'), $this->roomPayload(['code' => 'bm-sah-01']))
            ->assertSessionHasErrors('code');
    }

    public function test_the_location_must_sit_at_ruang_level(): void
    {
        $bangunan = Location::factory()->bangunan()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.rooms.store'), $this->roomPayload(['location_id' => $bangunan->id]))
            ->assertSessionHasErrors('location_id');
    }

    public function test_an_active_room_cannot_sit_inside_an_inactive_location(): void
    {
        $ruang = Location::factory()->ruang()->inactive()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.rooms.store'), $this->roomPayload(['location_id' => $ruang->id]))
            ->assertSessionHasErrors('is_active');
    }

    public function test_the_maximum_duration_must_exceed_the_minimum(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.rooms.store'), $this->roomPayload([
                'min_duration_minutes' => 120,
                'max_duration_minutes' => 120,
            ]))
            ->assertSessionHasErrors('max_duration_minutes');
    }

    public function test_exactly_one_layout_must_be_the_default(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.rooms.store'), $this->roomPayload([
                'layouts' => [
                    ['layout_code' => 'TEATER', 'capacity' => 30, 'is_default' => 0],
                    ['layout_code' => 'KELAS', 'capacity' => 24, 'is_default' => 0],
                ],
            ]))
            ->assertSessionHasErrors('layouts');

        $this->actingAs($this->admin)
            ->post(route('admin.rooms.store'), $this->roomPayload([
                'layouts' => [
                    ['layout_code' => 'TEATER', 'capacity' => 30, 'is_default' => 1],
                    ['layout_code' => 'KELAS', 'capacity' => 24, 'is_default' => 1],
                ],
            ]))
            ->assertSessionHasErrors('layouts');
    }

    public function test_layout_codes_must_come_from_the_reference_list(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.rooms.store'), $this->roomPayload([
                'layouts' => [
                    ['layout_code' => 'TIADA', 'capacity' => 10, 'is_default' => 1],
                ],
            ]))
            ->assertSessionHasErrors('layouts.0.layout_code');
    }

    public function test_facility_codes_must_come_from_the_reference_list(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.rooms.store'), $this->roomPayload([
                'facilities' => ['PROJEKTOR', 'TIADA'],
            ]))
            ->assertSessionHasErrors('facilities.1');
    }

    public function test_allowed_roles_must_be_real_roles(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.rooms.store'), $this->roomPayload([
                'allowed_roles' => ['bukan-peranan'],
            ]))
            ->assertSessionHasErrors('allowed_roles.0');
    }
}
