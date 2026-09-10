<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TiketNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TiketNote>
 */
class TiketNoteFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'user_id' => User::factory(),
            'catatan' => $this->faker->sentence(),
            'boleh_dilihat_pelapor' => true,
        ];
    }

    public function dalaman(): static
    {
        return $this->state(fn () => ['boleh_dilihat_pelapor' => false]);
    }
}
