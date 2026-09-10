<?php

namespace App\Models;

use App\Enums\AssetEventType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'event_type',
        'before',
        'after',
        'reason',
        'recorded_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_type' => AssetEventType::class,
            'before' => 'array',
            'after' => 'array',
        ];
    }

    /**
     * The asset this movement belongs to.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    /**
     * Pegawai yang merekodkan peristiwa (FR-AST-06).
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
