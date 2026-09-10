<?php

namespace App\Models;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    /** @use HasFactory<TicketFactory> */
    use HasFactory;

    protected $fillable = [
        'no_tiket',
        'lokasi_id',
        'aset_id',
        'pelapor_id',
        'juruteknik_id',
        'kategori_masalah',
        'keterangan',
        'telefon_hubungan',
        'keutamaan',
        'status',
        'sebab_keutamaan',
        'sebab_batal',
        'no_rujukan_vendor',
        'vendor_nama',
        'diagnosis',
        'tindakan',
        'kos_pembaikan',
        'masa_kerja_minit',
        'masa_dibuka',
        'masa_tindak_balas_pertama',
        'masa_kerja_selesai',
        'masa_ditutup',
        'sasaran_tindak_balas',
        'sasaran_pemulihan',
        'jeda_mula_pada',
        'minit_jeda_sla',
        'sla_dipatuhi',
        'amaran_80_dihantar_pada',
        'amaran_100_dihantar_pada',
        'penutupan_automatik',
        'lampiran',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'keutamaan' => TicketPriority::class,
            'kos_pembaikan' => 'decimal:2',
            'masa_kerja_minit' => 'integer',
            'masa_dibuka' => 'datetime',
            'masa_tindak_balas_pertama' => 'datetime',
            'masa_kerja_selesai' => 'datetime',
            'masa_ditutup' => 'datetime',
            'sasaran_tindak_balas' => 'datetime',
            'sasaran_pemulihan' => 'datetime',
            'jeda_mula_pada' => 'datetime',
            'minit_jeda_sla' => 'integer',
            'sla_dipatuhi' => 'boolean',
            'amaran_80_dihantar_pada' => 'datetime',
            'amaran_100_dihantar_pada' => 'datetime',
            'penutupan_automatik' => 'boolean',
            'lampiran' => 'array',
        ];
    }

    /**
     * The physical location the problem was reported at.
     */
    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'lokasi_id');
    }

    /**
     * The user who reported the fault.
     */
    public function pelapor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pelapor_id');
    }

    /**
     * The technician responsible for the repair.
     */
    public function juruteknik(): BelongsTo
    {
        return $this->belongsTo(User::class, 'juruteknik_id');
    }

    /**
     * Progress notes, newest last for a chronological timeline.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(TiketNote::class)->orderBy('created_at');
    }

    /**
     * Human-readable label of the fault category code (FR-TKT-02).
     */
    public function kategoriLabel(): string
    {
        return ReferenceValue::query()
            ->where('type', 'jenis_kerosakan')
            ->where('code', $this->kategori_masalah)
            ->value('label') ?? $this->kategori_masalah;
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeOpen(Builder $query): void
    {
        $query->whereNotIn('status', [TicketStatus::Ditutup->value, TicketStatus::Dibatalkan->value]);
    }

    /**
     * Order by priority, then by the soonest recovery target (UR-23).
     * CASE is used instead of FIELD() so the same query runs on MySQL and
     * on the in-memory SQLite the test suite uses.
     *
     * @param  Builder<self>  $query
     */
    public function scopeUrutanTugasan(Builder $query): void
    {
        $query->orderByRaw(
            "CASE keutamaan WHEN 'P1' THEN 1 WHEN 'P2' THEN 2 WHEN 'P3' THEN 3 WHEN 'P4' THEN 4 ELSE 5 END"
        )->orderBy('sasaran_pemulihan');
    }
}
