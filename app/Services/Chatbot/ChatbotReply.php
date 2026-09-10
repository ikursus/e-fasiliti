<?php

namespace App\Services\Chatbot;

/**
 * The assistant's answer, with token usage when the API reports it.
 */
final readonly class ChatbotReply
{
    public function __construct(
        public string $text,
        public ?int $promptTokens,
        public ?int $completionTokens,
    ) {}
}
