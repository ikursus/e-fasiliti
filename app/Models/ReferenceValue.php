<?php

namespace App\Models;

use App\Enums\ReferenceValueType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferenceValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'code',
        'label',
        'sort_order',
        'is_active',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeOfType(Builder $query, ReferenceValueType $type): void
    {
        $query->where('type', $type->value);
    }
}
