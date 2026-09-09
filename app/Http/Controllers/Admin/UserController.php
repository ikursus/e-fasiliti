<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserStoreRequest;
use App\Http\Requests\Admin\UserUpdateRequest;
use App\Models\AuditLog;
use App\Models\OrganizationUnit;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * List users with server-side search, a role filter and pagination.
     */
    public function index(Request $request): View
    {
        $users = User::query()
            ->with(['organizationUnit', 'primaryLocation'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->string('search'));

                $query->where(fn ($q) => $q
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->when($request->filled('role'), fn ($query) => $query->role((string) $request->string('role')))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => Role::all(['id', 'name']),
        ]);
    }

    /**
     * Show the user creation form.
     */
    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => Role::all(['id', 'name']),
            'organizationUnits' => OrganizationUnit::all(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created user (FR-USR-06/07).
     */
    public function store(UserStoreRequest $request): RedirectResponse
    {
        $data = $request->safe()->only([
            'name',
            'email',
            'password',
            'organization_unit_id',
            'primary_location_id',
            'is_active',
        ]);

        $user = User::create($data);
        $user->syncRoles($request->input('roles', []));

        $this->audit('user.created', $user, $request);

        return to_route('admin.users.index')
            ->with('status', 'Akaun pengguna telah dicipta.');
    }

    /**
     * Show the user edit form.
     */
    public function edit(User $user): View
    {
        $user->load('roles');

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => Role::all(['id', 'name']),
            'organizationUnits' => OrganizationUnit::all(['id', 'name']),
        ]);
    }

    /**
     * Update the user.
     */
    public function update(UserUpdateRequest $request, User $user): RedirectResponse
    {
        if ($user->is(Auth::user()) && $request->boolean('is_active') === false) {
            return back()->withErrors(['id' => 'Anda tidak boleh menyahaktifkan akaun sendiri.']);
        }

        $user->fill($request->safe()->only([
            'name',
            'email',
            'organization_unit_id',
            'primary_location_id',
            'is_active',
        ]));

        if ($request->filled('password')) {
            $user->password = $request->string('password');
        }

        $user->save();
        $user->syncRoles($request->input('roles', []));

        $this->audit('user.updated', $user, $request);

        return to_route('admin.users.index')
            ->with('status', 'Maklumat pengguna telah dikemas kini.');
    }

    /**
     * Remove a user. Deactivation is preferred (FR-USR-07); deletion is only
     * allowed for accounts that cannot be self-targeted or last-super-admin.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is(Auth::user())) {
            return back()->withErrors(['id' => 'Anda tidak boleh memadam akaun sendiri.']);
        }

        if ($user->isSuperAdmin() && $this->isOnlyActiveSuperAdmin($user)) {
            return back()->withErrors(['id' => 'Pentadbir sistem terakhir tidak boleh dipadam.']);
        }

        $user->delete();

        $this->audit('user.deleted', $user, $request);

        return to_route('admin.users.index')
            ->with('status', 'Akaun pengguna telah dipadam.');
    }

    /**
     * Whether the given super admin is the last remaining active one.
     */
    private function isOnlyActiveSuperAdmin(User $user): bool
    {
        return User::query()
            ->where('is_active', true)
            ->whereKeyNot($user->getKey())
            ->whereHas('roles', fn ($query) => $query->where('name', 'pentadbir-sistem'))
            ->doesntExist();
    }

    /**
     * Record an administrative action in the audit trail (FR-AUD-03).
     */
    private function audit(string $action, User $target, Request $request): void
    {
        /** @var User $actor */
        $actor = Auth::user();

        AuditLog::create([
            'user_id' => $actor->id,
            'action' => $action,
            'status' => 'success',
            'record_type' => 'user',
            'record_id' => $target->id,
            'ip_address' => $request->ip(),
            'user_agent' => str($request->userAgent() ?? '')->limit(255),
            'metadata' => ['target_email' => $target->email],
        ]);
    }
}
