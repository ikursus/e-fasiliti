<?php

namespace App\Models;

use App\Enums\ReferenceValueType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomLayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'layout_code',
        'capacity',
        'is_default',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'is_default' => 'boolean',
        ];
    }

    /**
     * The room this layout belongs to.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Display label from the susun_atur_bilik reference list, falling back
     * to the raw code when the list entry was removed.
     */
    public function label(): string
    {
        $reference = ReferenceValue::query()
            ->where('type', ReferenceValueType::SusunAturBilik->value)
            ->where('code', $this->layout_code)
            ->first();

        return $reference?->label ?? $this->layout_code;
    }
}
