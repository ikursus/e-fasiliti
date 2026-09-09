<?php

namespace Database\Factories;

use App\Models\SystemSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SystemSetting>
 */
class SystemSettingFactory extends Factory
{
    protected $model = SystemSetting::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => 'ujian.'.fake()->unique()->word(),
            'value' => '1',
            'value_type' => 'nombor',
            'group' => 'umum',
            'description' => null,
            'updated_by' => null,
        ];
    }
}
