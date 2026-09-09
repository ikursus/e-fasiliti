<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    public const TYPE_PUBLIC = 'cuti_umum';

    public const TYPE_NO_BOOKING = 'hari_tanpa_tempahan';

    protected $fillable = [
        'date',
        'name',
        'type',
        'recurs_annually',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'recurs_annually' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function typeLabel(): string
    {
        return $this->type === self::TYPE_PUBLIC ? 'Cuti umum' : 'Hari tanpa tempahan';
    }
}
