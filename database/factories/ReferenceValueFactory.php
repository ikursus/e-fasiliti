<?php

namespace Database\Factories;

use App\Models\ReferenceValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReferenceValue>
 */
class ReferenceValueFactory extends Factory
{
    protected $model = ReferenceValue::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'kategori_aset',
            'code' => strtoupper(fake()->unique()->bothify('REF-###')),
            'label' => ucfirst(fake()->unique()->words(2, true)),
            'sort_order' => 1,
            'is_active' => true,
            'metadata' => null,
        ];
    }
}
