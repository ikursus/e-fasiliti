<?php

namespace Database\Factories;

use App\Models\OrganizationUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrganizationUnit>
 */
class OrganizationUnitFactory extends Factory
{
    protected $model = OrganizationUnit::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('UNIT-####')),
            'name' => fn (array $attributes) => $this->nameFor($attributes['parent_id'] ?? null),
            'parent_id' => null,
            'is_active' => true,
        ];
    }

    public function childOf(OrganizationUnit $parent): static
    {
        return $this->state(fn () => ['parent_id' => $parent->id]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    /**
     * Build a unique display name whose prefix reflects whether this is a
     * top-level division or a unit under a parent.
     */
    private function nameFor(?int $parentId): string
    {
        $prefix = $parentId === null ? 'Bahagian' : 'Unit';

        return $prefix.' '.fake()->unique()->word();
    }
}
