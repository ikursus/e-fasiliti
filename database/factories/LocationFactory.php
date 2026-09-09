<?php

namespace Database\Factories;

use App\Enums\LocationLevel;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    protected $model = Location::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('LOK-####')),
            'name' => fn (array $attributes) => $this->nameFor($attributes['level'] ?? LocationLevel::Kampus),
            'level' => LocationLevel::Kampus,
            'parent_id' => null,
            'is_active' => true,
        ];
    }

    public function bangunan(): static
    {
        return $this->state(fn () => ['level' => LocationLevel::Bangunan]);
    }

    public function tingkat(): static
    {
        return $this->state(fn () => ['level' => LocationLevel::Tingkat]);
    }

    public function ruang(): static
    {
        return $this->state(fn () => ['level' => LocationLevel::Ruang]);
    }

    public function childOf(Location $parent): static
    {
        return $this->state(fn () => ['parent_id' => $parent->id]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    /**
     * Build a unique display name whose prefix matches the given level.
     */
    private function nameFor(LocationLevel|string $level): string
    {
        if (is_string($level)) {
            $level = LocationLevel::from($level);
        }

        return $level->label().' '.fake()->unique()->city();
    }
}
