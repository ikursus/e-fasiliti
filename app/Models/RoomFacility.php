<?php

namespace App\Models;

use App\Enums\ReferenceValueType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomFacility extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'facility_code',
    ];

    /**
     * The room this facility row belongs to.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Display label from the kemudahan_bilik reference list, falling back
     * to the raw code when the list entry was removed.
     */
    public function label(): string
    {
        $reference = ReferenceValue::query()
            ->where('type', ReferenceValueType::KemudahanBilik->value)
            ->where('code', $this->facility_code)
            ->first();

        return $reference?->label ?? $this->facility_code;
    }
}
