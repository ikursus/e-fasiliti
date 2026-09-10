<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\RoomLayout;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomLayout>
 */
class RoomLayoutFactory extends Factory
{
    protected $model = RoomLayout::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'layout_code' => strtoupper(fake()->unique()->lexify('????')),
            'capacity' => 20,
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn () => ['is_default' => true]);
    }
}
