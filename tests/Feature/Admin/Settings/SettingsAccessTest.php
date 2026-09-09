<?php

namespace Tests\Feature\Admin\Settings;

use App\Models\SystemSetting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsAccessTest extends TestCase
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
        $this->get(route('admin.settings.general'))->assertRedirect(route('login'));
    }

    public function test_the_four_permitted_roles_may_view_settings(): void
    {
        foreach (['pentadbir-sistem', 'pentadbir-fasiliti', 'penyelia-ict', 'pegawai-aset'] as $role) {
            $this->actingAs($this->userWithRole($role))
                ->get(route('admin.settings.general'))
                ->assertOk();
        }
    }

    public function test_other_roles_are_forbidden(): void
    {
        foreach (['kakitangan', 'setiausaha', 'pelulus', 'juruteknik'] as $role) {
            $this->actingAs($this->userWithRole($role))
                ->get(route('admin.settings.general'))
                ->assertForbidden();
        }
    }

    public function test_a_viewer_may_not_write_settings(): void
    {
        SystemSetting::create([
            'key' => 'umum.nama_organisasi',
            'value' => '"Lama"',
            'value_type' => 'teks',
            'group' => 'umum',
        ]);

        $this->actingAs($this->userWithRole('penyelia-ict'))
            ->put(route('admin.settings.general.update'), ['umum_nama_organisasi' => 'Baharu'])
            ->assertForbidden();

        $this->assertSame('Lama', setting('umum.nama_organisasi'));
    }

    public function test_the_administrator_may_write_settings(): void
    {
        SystemSetting::create([
            'key' => 'umum.nama_organisasi',
            'value' => '"Lama"',
            'value_type' => 'teks',
            'group' => 'umum',
        ]);

        $this->actingAs($this->userWithRole('pentadbir-sistem'))
            ->put(route('admin.settings.general.update'), ['umum_nama_organisasi' => 'Baharu'])
            ->assertRedirect(route('admin.settings.general'));

        $this->assertSame('Baharu', setting('umum.nama_organisasi'));
        $this->assertDatabaseHas('audit_logs', ['action' => 'setting.updated']);
    }

    public function test_the_sidebar_shows_the_settings_link_only_to_permitted_roles(): void
    {
        $this->actingAs($this->userWithRole('pegawai-aset'))
            ->get(route('dashboard'))
            ->assertSee(route('admin.settings.general'));

        $this->actingAs($this->userWithRole('kakitangan'))
            ->get(route('dashboard'))
            ->assertDontSee(route('admin.settings.general'));
    }
}
