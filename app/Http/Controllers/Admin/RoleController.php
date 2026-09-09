<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * List roles with their permission counts.
     */
    public function index(): View
    {
        $roles = Role::query()
            ->withCount('permissions')
            ->withCount('users')
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(15);

        return view('admin.roles.index', ['roles' => $roles]);
    }

    /**
     * Show the role creation form.
     */
    public function create(): View
    {
        return view('admin.roles.create', [
            'permissions' => $this->groupedPermissions(),
        ]);
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('roles', 'name')->where('guard_name', 'web')],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
        ]);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);

        $role->syncPermissions(...($data['permissions'] ?? []));

        Cache::forget(config('permission.cache.key'));

        return to_route('admin.roles.index')
            ->with('status', 'Peranan baharu telah dicipta.');
    }

    /**
     * Show the role edit form.
     */
    public function edit(Role $role): View
    {
        $role->load('permissions');

        return view('admin.roles.edit', [
            'role' => $role,
            'permissions' => $this->groupedPermissions(),
        ]);
    }

    /**
     * Update the role and its permissions.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('roles', 'name')->ignore($role->id)],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
        ]);

        $role->update(['name' => $data['name']]);
        $role->syncPermissions(...($data['permissions'] ?? []));

        Cache::forget(config('permission.cache.key'));

        return to_route('admin.roles.index')
            ->with('status', 'Peranan telah diperbaharui.');
    }

    /**
     * Delete a role. A role that still has users is preserved.
     */
    public function destroy(Role $role): RedirectResponse
    {
        if ($role->users()->exists()) {
            return back()->withErrors(['id' => 'Peranan yang masih digunakan oleh pengguna tidak boleh dipadam.']);
        }

        $role->delete();

        return to_route('admin.roles.index')
            ->with('status', 'Peranan telah dihapus.');
    }

    /**
     * Permissions grouped by their module prefix for the form UI.
     *
     * @return array<string, list<Permission>>
     */
    private function groupedPermissions(): array
    {
        $grouped = [];

        foreach (Permission::query()->orderBy('name')->get() as $permission) {
            $module = explode('.', $permission->name)[0];
            $grouped[$module][] = $permission;
        }

        ksort($grouped);

        return $grouped;
    }
}
