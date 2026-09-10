<?php

namespace App\Models;

use App\Enums\AssetStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_number',
        'category_id',
        'brand',
        'model',
        'serial_number',
        'specifications',
        'acquisition_date',
        'acquisition_cost',
        'order_number',
        'warranty_start_date',
        'warranty_months',
        'location_id',
        'responsible_user_id',
        'status',
        'mac_address',
        'ip_address',
        'hostname',
        'qr_code',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'specifications' => 'array',
            'acquisition_date' => 'date',
            'acquisition_cost' => 'decimal:2',
            'warranty_start_date' => 'date',
            'warranty_months' => 'integer',
            'status' => AssetStatus::class,
        ];
    }

    /**
     * Kategori aset daripada senarai rujukan (FR-ADM-07).
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ReferenceValue::class, 'category_id');
    }

    /**
     * Lokasi semasa aset (FR-AST-05).
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    /**
     * Pengguna bertanggungjawab; kosong bagi aset dalam simpanan (FR-AST-05).
     */
    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    /**
     * Sejarah pergerakan aset, terbaharu dahulu (FR-AST-06).
     */
    public function histories(): HasMany
    {
        return $this->hasMany(AssetHistory::class, 'asset_id');
    }

    /**
     * FR-AST-10: carian bebas merentas pengenal dan medan deskriptif.
     *
     * @param  Builder<self>  $query
     */
    public function scopeSearch(Builder $query, string $term): void
    {
        $query->where(fn (Builder $inner) => $inner
            ->where('registration_number', 'like', "%{$term}%")
            ->orWhere('serial_number', 'like', "%{$term}%")
            ->orWhere('brand', 'like', "%{$term}%")
            ->orWhere('model', 'like', "%{$term}%")
            ->orWhere('hostname', 'like', "%{$term}%"));
    }

    /**
     * Kakitangan hanya melihat aset yang didaftarkan atas namanya
     * (matriks M09 "R sendiri", keputusan 9 September 2026).
     *
     * @param  Builder<self>  $query
     */
    public function scopeVisibleTo(Builder $query, User $user): void
    {
        if ($user->hasRole('kakitangan') && ! $user->can('aset.kemaskini')) {
            $query->where('responsible_user_id', $user->id);
        }
    }

    /**
     * Medan pergerakan untuk rekod sejarah (FR-AST-06). Status dipetakan
     * kepada nilai skalar supaya kedua-dua sisi kekal serasi JSON.
     *
     * @return array{registration_number: string, location_id: int|null, responsible_user_id: int|null, status: string}
     */
    public function movementSnapshot(): array
    {
        return [
            'registration_number' => $this->registration_number,
            'location_id' => $this->location_id,
            'responsible_user_id' => $this->responsible_user_id,
            'status' => $this->status->value,
        ];
    }

    /**
     * Medan yang dijejak dalam jejak audit. Nilai skalar semata-mata,
     * daripada nilai yang telah melalui cast Eloquent.
     *
     * @return array<string, mixed>
     */
    public function auditSnapshot(): array
    {
        return [
            'registration_number' => $this->registration_number,
            'category_id' => $this->category_id,
            'brand' => $this->brand,
            'model' => $this->model,
            'serial_number' => $this->serial_number,
            'acquisition_date' => $this->acquisition_date?->toDateString(),
            'acquisition_cost' => $this->acquisition_cost,
            'warranty_start_date' => $this->warranty_start_date?->toDateString(),
            'warranty_months' => $this->warranty_months,
            'location_id' => $this->location_id,
            'responsible_user_id' => $this->responsible_user_id,
            'status' => $this->status->value,
        ];
    }

    /**
     * FR-AST-11 (asas): status waranti dikira daripada tarikh mula dan
     * tempoh bulan. Null apabila aset tidak mempunyai maklumat waranti.
     *
     * @return array{active: bool|null, ends_at: CarbonInterface|null, days_remaining: int|null}
     */
    public function warrantyStatus(): array
    {
        if ($this->warranty_start_date === null || $this->warranty_months === null) {
            return ['active' => null, 'ends_at' => null, 'days_remaining' => null];
        }

        $endsAt = $this->warranty_start_date->copy()->addMonths($this->warranty_months)->endOfDay();
        $today = now()->startOfDay();

        return [
            'active' => $today->lte($endsAt),
            'ends_at' => $endsAt,
            'days_remaining' => $today->lt($endsAt)
                ? (int) $today->diffInDays($endsAt)
                : (int) $endsAt->startOfDay()->diffInDays($today),
        ];
    }
}
