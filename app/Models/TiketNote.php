<?php

namespace App\Models;

use Database\Factories\TiketNoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TiketNote extends Model
{
    /** @use HasFactory<TiketNoteFactory> */
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'catatan',
        'boleh_dilihat_pelapor',
        'lampiran',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'boleh_dilihat_pelapor' => 'boolean',
            'lampiran' => 'array',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
