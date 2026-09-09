<?php

namespace Database\Factories;

use App\Models\NotificationTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationTemplate>
 */
class NotificationTemplateFactory extends Factory
{
    protected $model = NotificationTemplate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => 'ujian.'.fake()->unique()->word(),
            'channel' => 'emel',
            'locale' => 'ms',
            'subject' => 'Subjek ujian',
            'body' => 'Kandungan ujian.',
            'placeholders' => ['nama_penerima'],
            'is_active' => true,
        ];
    }
}
