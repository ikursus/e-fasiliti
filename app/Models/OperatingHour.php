<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OperatingHour extends Model
{
    protected $fillable = [
        'owner_type',
        'owner_id',
        'day_of_week',
        'opens_at',
        'closes_at',
        'is_closed',
    ];

    /**
     * Malay day names, indexed the same way Carbon indexes days of week.
     *
     * @var array<int, string>
     */
    public const DAY_NAMES = [
        0 => 'Ahad',
        1 => 'Isnin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Khamis',
        5 => 'Jumaat',
        6 => 'Sabtu',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'is_closed' => 'boolean',
        ];
    }

    /**
     * The room or other entity these hours belong to, if any.
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Organisation-wide default hours.
     *
     * @param  Builder<self>  $query
     */
    public function scopeOrganisationDefault(Builder $query): void
    {
        $query->whereNull('owner_type')->whereNull('owner_id');
    }

    public function dayName(): string
    {
        return self::DAY_NAMES[$this->day_of_week] ?? '';
    }
}
