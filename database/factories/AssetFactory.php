<?php

namespace Database\Factories;

use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\Location;
use App\Models\ReferenceValue;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asset>
 */
class AssetFactory extends Factory
{
    protected $model = Asset::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'registration_number' => strtoupper(fake()->unique()->bothify('AST-#####')),
            'category_id' => ReferenceValue::factory(),
            'brand' => fake()->randomElement(['Dell', 'HP', 'Canon', 'Epson', 'TP-Link', 'Asus']),
            'model' => ucfirst(fake()->unique()->bothify('model-###')),
            'serial_number' => strtoupper(fake()->unique()->bothify('SN-########')),
            'specifications' => null,
            'acquisition_date' => fake()->dateTimeBetween('-4 years', '-2 months')->format('Y-m-d'),
            'acquisition_cost' => fake()->randomFloat(2, 150, 9000),
            'order_number' => null,
            'warranty_start_date' => null,
            'warranty_months' => null,
            'location_id' => Location::factory(),
            'responsible_user_id' => User::factory(),
            'status' => AssetStatus::Digunakan,
            'mac_address' => null,
            'ip_address' => null,
            'hostname' => null,
            'qr_code' => fake()->unique()->uuid(),
            'notes' => null,
        ];
    }

    public function simpanan(): static
    {
        return $this->state(fn (): array => [
            'status' => AssetStatus::Simpanan,
            'responsible_user_id' => null,
        ]);
    }

    public function dalamPembaikan(): static
    {
        return $this->state(fn (): array => ['status' => AssetStatus::DalamPembaikan]);
    }

    public function tidakAktif(): static
    {
        return $this->state(fn (): array => ['status' => AssetStatus::TidakAktif]);
    }

    public function dilupuskan(): static
    {
        return $this->state(fn (): array => ['status' => AssetStatus::Dilupuskan]);
    }

    public function inWarranty(): static
    {
        return $this->state(fn (): array => [
            'warranty_start_date' => now()->subMonths(6)->toDateString(),
            'warranty_months' => 36,
        ]);
    }

    public function outOfWarranty(): static
    {
        return $this->state(fn (): array => [
            'warranty_start_date' => now()->subYears(5)->toDateString(),
            'warranty_months' => 24,
        ]);
    }

    public function ownedBy(User $user): static
    {
        return $this->state(fn (): array => ['responsible_user_id' => $user->id]);
    }

    public function atLocation(Location $location): static
    {
        return $this->state(fn (): array => ['location_id' => $location->id]);
    }

    public function withCategory(ReferenceValue $category): static
    {
        return $this->state(fn (): array => ['category_id' => $category->id]);
    }
}
