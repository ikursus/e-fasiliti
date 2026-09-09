<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogViewerAccessTest extends TestCase
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

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/log-viewer')->assertRedirect(route('login'));
    }

    public function test_every_role_except_the_system_administrator_is_forbidden(): void
    {
        foreach (array_keys(RolesAndPermissionsSeeder::ROLES) as $role) {
            if ($role === 'pentadbir-sistem') {
                continue;
            }

            $this->actingAs($this->userWithRole($role))
                ->get('/log-viewer')
                ->assertForbidden();
        }
    }

    public function test_the_system_administrator_may_open_the_log_viewer(): void
    {
        $this->actingAs($this->userWithRole('pentadbir-sistem'))
            ->get('/log-viewer')
            ->assertOk();
    }

    public function test_the_api_is_closed_to_everyone_but_the_system_administrator(): void
    {
        $this->actingAs($this->userWithRole('penyelia-ict'))
            ->getJson('/log-viewer/api/files')
            ->assertForbidden();
    }

    public function test_the_sidebar_links_to_the_log_viewer_for_the_administrator_only(): void
    {
        $this->actingAs($this->userWithRole('pentadbir-sistem'))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('/log-viewer');

        $this->actingAs($this->userWithRole('penyelia-ict'))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('/log-viewer');
    }
}
