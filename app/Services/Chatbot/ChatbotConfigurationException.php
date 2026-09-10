<?php

namespace App\Services\Chatbot;

/**
 * Raised when GEMINI_API_KEY is missing, so no request can be made at all.
 */
class ChatbotConfigurationException extends ChatbotException {}
