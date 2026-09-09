<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'location_id',
        'base_capacity',
        'requires_approval',
        'allowed_roles',
        'min_duration_minutes',
        'max_duration_minutes',
        'buffer_before_minutes',
        'buffer_after_minutes',
        'qr_code',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'base_capacity' => 'integer',
            'requires_approval' => 'boolean',
            'allowed_roles' => 'array',
            'min_duration_minutes' => 'integer',
            'max_duration_minutes' => 'integer',
            'buffer_before_minutes' => 'integer',
            'buffer_after_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The ruang-level location this room sits at (FR-BLK-02).
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Supported layouts, each with its own capacity (FR-BLK-03).
     */
    public function layouts(): HasMany
    {
        return $this->hasMany(RoomLayout::class);
    }

    /**
     * Fixed facilities, recorded from the reference list (FR-BLK-04).
     */
    public function facilities(): HasMany
    {
        return $this->hasMany(RoomFacility::class);
    }

    /**
     * Bookings against this room. The engine writing them arrives in M05.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Upcoming bookings that still hold a slot (FR-BLK-08).
     */
    public function upcomingBookings(): HasMany
    {
        return $this->bookings()->upcoming();
    }

    /**
     * Room-specific operating hours. Rows are polymorphic; when absent the
     * organisation defaults apply (FR-TMP-06 consumes both).
     */
    public function operatingHours(): MorphMany
    {
        return $this->morphMany(OperatingHour::class, 'owner');
    }

    /**
     * Only rooms still in use.
     *
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Full path of the room's location, e.g.
     * "Kampus Induk / Bangunan A / Tingkat 3 / Bilik Mesyuarat 1".
     */
    public function locationFullPath(): string
    {
        return $this->location?->fullPath() ?? '';
    }

    /**
     * Reasons this room may not be deleted. History is never destroyed
     * (DRD principle 2): any booking, past or future, keeps the row.
     *
     * @return array<int, string>
     */
    public function referenceSummary(): array
    {
        $reasons = [];

        if ($this->bookings()->exists()) {
            $reasons[] = 'tempahan';
        }

        return $reasons;
    }
}
