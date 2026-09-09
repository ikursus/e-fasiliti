<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'status',
        'record_type',
        'record_id',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'record_id' => 'integer',
        ];
    }

    /**
     * The actor who performed the audited action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
