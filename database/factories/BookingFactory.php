<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = now()->addDays(fake()->numberBetween(1, 30));

        return [
            'reference_no' => 'TMP-'.now()->format('Ym').'-'.str_pad(
                (string) fake()->unique()->numberBetween(1, 99999),
                5,
                '0',
                STR_PAD_LEFT
            ),
            'room_id' => Room::factory(),
            'booked_by_id' => User::factory(),
            'owner_id' => User::factory(),
            'title' => 'Mesyuarat '.fake()->unique()->numberBetween(1, 999),
            'description' => null,
            'starts_at' => $start->copy()->setTime(9, 0),
            'ends_at' => $start->copy()->setTime(11, 0),
            'participant_count' => 10,
            'room_layout_id' => null,
            'status' => BookingStatus::Disahkan,
            'cancellation_reason' => null,
            'cancelled_late' => false,
        ];
    }

    /**
     * A booking that starts tomorrow afternoon — safely in the future.
     */
    public function upcoming(): static
    {
        return $this->state(fn () => [
            'starts_at' => now()->addDay()->setTime(14, 0),
            'ends_at' => now()->addDay()->setTime(16, 0),
        ]);
    }

    public function confirmed(): static
    {
        return $this->state(fn () => ['status' => BookingStatus::Disahkan]);
    }

    public function pendingApproval(): static
    {
        return $this->state(fn () => ['status' => BookingStatus::MenungguKelulusan]);
    }
}
