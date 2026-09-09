<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_no',
        'room_id',
        'booked_by_id',
        'owner_id',
        'title',
        'description',
        'starts_at',
        'ends_at',
        'participant_count',
        'room_layout_id',
        'status',
        'cancellation_reason',
        'cancelled_late',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'participant_count' => 'integer',
            'status' => BookingStatus::class,
            'cancelled_late' => 'boolean',
        ];
    }

    /**
     * The room this booking reserves.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * The user who made the booking, which may act on behalf of another.
     */
    public function bookedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'booked_by_id');
    }

    /**
     * The user whose meeting it is; usually the same as bookedBy (FR-TMP-09).
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * The chosen layout, when the booking selected one.
     */
    public function layout(): BelongsTo
    {
        return $this->belongsTo(RoomLayout::class, 'room_layout_id');
    }

    /**
     * Bookings that start in the future and still hold a slot (FR-BLK-08).
     *
     * @param  Builder<self>  $query
     */
    public function scopeUpcoming(Builder $query): void
    {
        $query->whereIn('status', BookingStatus::slotHoldingValues())
            ->where('starts_at', '>', now());
    }
}
