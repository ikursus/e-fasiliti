<?php

namespace App\Services\Chatbot;

/**
 * Raised when the Gemini API itself fails: unreachable, non-2xx, or an
 * answer the service cannot extract text from.
 */
class ChatbotApiException extends ChatbotException {}
