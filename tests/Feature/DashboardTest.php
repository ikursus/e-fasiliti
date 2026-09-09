<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Role slug => expected dashboard view (SRS §1.4, R1-R8).
     *
     * @return array<string, array<int, string>>
     */
    public static function roleDashboardProvider(): array
    {
        return [
            'kakitangan' => ['kakitangan', 'dashboard.kakitangan'],
            'setiausaha' => ['setiausaha', 'dashboard.setiausaha'],
            'pelulus' => ['pelulus', 'dashboard.pelulus'],
            'pentadbir-fasiliti' => ['pentadbir-fasiliti', 'dashboard.pentadbir-fasiliti'],
            'juruteknik' => ['juruteknik', 'dashboard.juruteknik'],
            'penyelia-ict' => ['penyelia-ict', 'dashboard.penyelia-ict'],
            'pegawai-aset' => ['pegawai-aset', 'dashboard.pegawai-aset'],
            'pentadbir-sistem' => ['pentadbir-sistem', 'dashboard.pentadbir-sistem'],
        ];
    }

    #[DataProvider('roleDashboardProvider')]
    public function test_each_role_renders_its_own_dashboard(string $roleName, string $expectedView): void
    {
        Role::create(['name' => $roleName]);
        $user = User::factory()->create();
        $user->assignRole($roleName);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewIs($expectedView);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_user_with_multiple_roles_gets_the_highest_priority_dashboard(): void
    {
        Role::create(['name' => 'juruteknik']);
        Role::create(['name' => 'pentadbir-sistem']);

        $user = User::factory()->create();
        $user->assignRole('juruteknik', 'pentadbir-sistem');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewIs('dashboard.pentadbir-sistem');
    }

    public function test_user_without_any_role_falls_back_to_the_staff_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewIs('dashboard.kakitangan');
    }

    public function test_deactivated_user_is_logged_out_when_opening_the_dashboard(): void
    {
        Role::create(['name' => 'kakitangan']);

        $user = User::factory()->create(['is_active' => false]);
        $user->assignRole('kakitangan');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
