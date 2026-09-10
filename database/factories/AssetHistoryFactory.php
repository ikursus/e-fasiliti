<?php

namespace Database\Factories;

use App\Enums\AssetEventType;
use App\Models\Asset;
use App\Models\AssetHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssetHistory>
 */
class AssetHistoryFactory extends Factory
{
    protected $model = AssetHistory::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'asset_id' => Asset::factory(),
            'event_type' => AssetEventType::Didaftar,
            'before' => null,
            'after' => null,
            'reason' => null,
            'recorded_by' => User::factory(),
        ];
    }

    public function pindahLokasi(int $from, int $to): static
    {
        return $this->state(fn (): array => [
            'event_type' => AssetEventType::PindahLokasi,
            'before' => ['location_id' => $from],
            'after' => ['location_id' => $to],
            'reason' => 'Penempatan semula',
        ]);
    }
}
