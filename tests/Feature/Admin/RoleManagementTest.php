<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'pentadbir-sistem']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('pentadbir-sistem');
    }

    public function test_user_without_the_system_administrator_role_is_forbidden(): void
    {
        Role::create(['name' => 'kakitangan']);
        $staff = User::factory()->create();
        $staff->assignRole('kakitangan');

        $this->actingAs($staff)
            ->get(route('admin.roles.index'))
            ->assertForbidden();
    }

    public function test_create_page_renders_with_grouped_permissions(): void
    {
        Permission::create(['name' => 'users.manage']);
        Permission::create(['name' => 'users.view']);
        Permission::create(['name' => 'roles.manage']);

        $this->actingAs($this->admin)
            ->get(route('admin.roles.create'))
            ->assertOk()
            ->assertSee('users.manage')
            ->assertSee('roles.manage');
    }

    public function test_edit_page_renders_with_grouped_permissions(): void
    {
        Permission::create(['name' => 'users.manage']);

        $role = Role::create(['name' => 'penyelia-ict']);
        $role->givePermissionTo('users.manage');

        $this->actingAs($this->admin)
            ->get(route('admin.roles.edit', $role))
            ->assertOk()
            ->assertSee('penyelia-ict')
            ->assertSee('users.manage');
    }

    public function test_admin_creates_a_role_with_permissions(): void
    {
        Permission::create(['name' => 'users.manage']);
        Permission::create(['name' => 'roles.manage']);

        $this->actingAs($this->admin)
            ->post(route('admin.roles.store'), [
                'name' => 'penyelia-ict',
                'permissions' => ['users.manage', 'roles.manage'],
            ])
            ->assertRedirect(route('admin.roles.index'))
            ->assertSessionHas('status');

        $role = Role::query()->where('name', 'penyelia-ict')->firstOrFail();

        $this->assertTrue($role->hasPermissionTo('users.manage'));
        $this->assertTrue($role->hasPermissionTo('roles.manage'));
    }

    public function test_store_rejects_a_duplicate_role_name(): void
    {
        Role::create(['name' => 'penyelia-ict']);

        $this->actingAs($this->admin)
            ->post(route('admin.roles.store'), ['name' => 'penyelia-ict'])
            ->assertSessionHasErrors('name');
    }

    public function test_admin_updates_a_role_and_replaces_its_permissions(): void
    {
        Permission::create(['name' => 'users.manage']);
        Permission::create(['name' => 'roles.manage']);

        $role = Role::create(['name' => 'penyelia-ict']);
        $role->givePermissionTo('users.manage');

        $this->actingAs($this->admin)
            ->put(route('admin.roles.update', $role), [
                'name' => 'penyelia-ict-junior',
                'permissions' => ['roles.manage'],
            ])
            ->assertRedirect(route('admin.roles.index'))
            ->assertSessionHas('status');

        $role->refresh();

        $this->assertSame('penyelia-ict-junior', $role->name);
        $this->assertTrue($role->hasPermissionTo('roles.manage'));
        $this->assertFalse($role->hasPermissionTo('users.manage'));
    }

    public function test_role_without_users_can_be_deleted(): void
    {
        $role = Role::create(['name' => 'kakitangan-lama']);

        $this->actingAs($this->admin)
            ->delete(route('admin.roles.destroy', $role))
            ->assertRedirect(route('admin.roles.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_role_still_in_use_cannot_be_deleted(): void
    {
        $role = Role::create(['name' => 'kakitangan']);
        $staff = User::factory()->create();
        $staff->assignRole('kakitangan');

        $this->actingAs($this->admin)
            ->delete(route('admin.roles.destroy', $role))
            ->assertSessionHasErrors('id');

        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }
}
