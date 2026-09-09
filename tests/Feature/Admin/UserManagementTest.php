<?php

namespace Tests\Feature\Admin;

use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'pentadbir-sistem']);
        Role::create(['name' => 'kakitangan']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('pentadbir-sistem');
    }

    public function test_guest_is_redirected_to_login_for_user_management(): void
    {
        $this->get(route('admin.users.index'))->assertRedirect(route('login'));
    }

    public function test_user_without_the_system_administrator_role_is_forbidden(): void
    {
        $staff = User::factory()->create();
        $staff->assignRole('kakitangan');

        $this->actingAs($staff)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_admin_creates_a_user_with_roles_and_persisted_state(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'name' => 'Aisyah Rahman',
                'email' => 'aisyah@e-fasiliti.test',
                'password' => 'KataLaluan123!',
                'password_confirmation' => 'KataLaluan123!',
                'roles' => ['kakitangan'],
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('status');

        $user = User::query()->where('email', 'aisyah@e-fasiliti.test')->firstOrFail();

        $this->assertTrue($user->hasRole('kakitangan'));
        $this->assertTrue($user->is_active);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->admin->id,
            'action' => 'user.created',
            'record_id' => $user->id,
        ]);
    }

    public function test_store_rejects_an_empty_payload_with_validation_errors(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [])
            ->assertSessionHasErrors(['name', 'email', 'password', 'roles']);
    }

    public function test_store_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'aisyah@e-fasiliti.test']);

        $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'name' => 'Aisyah Rahman',
                'email' => 'aisyah@e-fasiliti.test',
                'password' => 'KataLaluan123!',
                'password_confirmation' => 'KataLaluan123!',
                'roles' => ['kakitangan'],
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_admin_updates_a_user_and_replaces_their_roles(): void
    {
        $user = User::factory()->create();
        $user->assignRole('kakitangan');

        $this->actingAs($this->admin)
            ->put(route('admin.users.update', $user), [
                'name' => 'Aisyah Rahman',
                'email' => $user->email,
                'roles' => ['pentadbir-sistem'],
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('status');

        $user->refresh();

        $this->assertSame('Aisyah Rahman', $user->name);
        $this->assertTrue($user->hasRole('pentadbir-sistem'));
        $this->assertFalse($user->hasRole('kakitangan'));
    }

    public function test_admin_cannot_deactivate_their_own_account(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.users.update', $this->admin), [
                'name' => $this->admin->name,
                'email' => $this->admin->email,
                'roles' => ['pentadbir-sistem'],
                'is_active' => '0',
            ])
            ->assertSessionHasErrors('id');

        $this->assertTrue($this->admin->refresh()->is_active);
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $this->admin))
            ->assertSessionHasErrors('id');

        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_admin_can_delete_another_user(): void
    {
        $staff = User::factory()->create();
        $staff->assignRole('kakitangan');

        $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $staff))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('users', ['id' => $staff->id]);
    }

    public function test_admin_index_supports_search_and_role_filter(): void
    {
        $staff = User::factory()->create(['name' => 'Aisyah Rahman']);
        $staff->assignRole('kakitangan');
        User::factory()->create(['name' => 'Zulkifli Ahmad']);

        $this->actingAs($this->admin)
            ->get(route('admin.users.index', ['search' => 'Aisyah']))
            ->assertOk()
            ->assertSee('Aisyah Rahman')
            ->assertDontSee('Zulkifli Ahmad');

        $this->actingAs($this->admin)
            ->get(route('admin.users.index', ['role' => 'kakitangan']))
            ->assertOk()
            ->assertSee('Aisyah Rahman')
            ->assertDontSee('Zulkifli Ahmad');
    }

    public function test_the_user_form_uses_the_location_tree_picker(): void
    {
        $campus = Location::factory()->create(['name' => 'Kampus Induk']);
        Location::factory()->bangunan()->childOf($campus)->create(['name' => 'Blok A']);

        $this->actingAs($this->admin)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('primary_location_id_kampus')
            ->assertSee('Blok A');
    }
}
