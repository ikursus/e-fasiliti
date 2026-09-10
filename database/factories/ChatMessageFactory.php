<?php

namespace Database\Factories;

use App\Enums\ChatRole;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChatMessage>
 */
class ChatMessageFactory extends Factory
{
    protected $model = ChatMessage::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chat_session_id' => ChatSession::factory(),
            'role' => fake()->randomElement([ChatRole::User, ChatRole::Model]),
            'content' => fake()->sentence(8),
            'prompt_tokens' => fake()->numberBetween(10, 500),
            'completion_tokens' => fake()->numberBetween(10, 500),
        ];
    }

    public function fromUser(): static
    {
        return $this->state(fn () => ['role' => ChatRole::User]);
    }

    public function fromModel(): static
    {
        return $this->state(fn () => ['role' => ChatRole::Model]);
    }
}
