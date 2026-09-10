<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name',
    'email',
    'password',
    'is_active',
    'organization_unit_id',
    'primary_location_id',
    'delegate_user_id',
    'delegate_start_at',
    'delegate_end_at',
    'last_login_at',
    'last_login_ip',
    'profile_photo_path',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The priority order used to pick which dashboard to show when the user
     * holds more than one role (FR-USR-04). Lower numbers win.
     */
    public const ROLE_DASHBOARD_PRIORITY = [
        'pentadbir-sistem' => 1,
        'pentadbir-fasiliti' => 2,
        'penyelia-ict' => 3,
        'pegawai-aset' => 4,
        'pelulus' => 5,
        'setiausaha' => 6,
        'juruteknik' => 7,
        'kakitangan' => 8,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'delegate_start_at' => 'datetime',
            'delegate_end_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Public URL of the profile photo, or an empty string when none is set.
     * The photo lives on the "public" disk at profile/{user_id}/{hash}.
     */
    public function profilePhotoUrl(): string
    {
        if ($this->profile_photo_path === null || $this->profile_photo_path === '') {
            return '';
        }

        return Storage::disk('public')->url($this->profile_photo_path);
    }

    /**
     * Organisational unit this user is assigned to (FR-USR-06).
     */
    public function organizationUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationUnit::class, 'organization_unit_id');
    }

    /**
     * Primary physical location of this user (FR-USR-06).
     */
    public function primaryLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'primary_location_id');
    }

    /**
     * The user standing in for this user's approvals (FR-USR-08).
     */
    public function delegate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegate_user_id');
    }

    /**
     * Whether the local account is allowed to authenticate.
     */
    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * Whether this user has the highest (super admin) system role R8.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('pentadbir-sistem');
    }

    /**
     * Resolve the dashboard key for this user based on their highest-priority
     * role. Falls back to the "kakitangan" (staff) dashboard.
     */
    public function dashboardKey(): string
    {
        $priority = self::ROLE_DASHBOARD_PRIORITY;

        $best = 'kakitangan';
        $bestPriority = PHP_INT_MAX;

        foreach ($this->getRoleNames() as $role) {
            if (isset($priority[$role]) && $priority[$role] < $bestPriority) {
                $best = $role;
                $bestPriority = $priority[$role];
            }
        }

        return $best;
    }
}
