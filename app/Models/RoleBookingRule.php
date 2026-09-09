<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role;

class RoleBookingRule extends Model
{
    protected $fillable = [
        'role_id',
        'min_duration_minutes',
        'max_duration_minutes',
        'max_advance_days',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_duration_minutes' => 'integer',
            'max_duration_minutes' => 'integer',
            'max_advance_days' => 'integer',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
