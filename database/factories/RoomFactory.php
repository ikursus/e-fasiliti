<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    protected $model = Room::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('BM-###')),
            'name' => 'Bilik Mesyuarat '.fake()->unique()->numberBetween(1, 999),
            'location_id' => Location::factory()->ruang(),
            'base_capacity' => 20,
            'requires_approval' => false,
            'allowed_roles' => null,
            'min_duration_minutes' => 30,
            'max_duration_minutes' => 480,
            'buffer_before_minutes' => 0,
            'buffer_after_minutes' => 0,
            'qr_code' => null,
            'is_active' => true,
        ];
    }

    public function needsApproval(): static
    {
        return $this->state(fn () => ['requires_approval' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
