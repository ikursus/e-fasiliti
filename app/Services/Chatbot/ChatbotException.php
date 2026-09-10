<?php

namespace App\Services\Chatbot;

use RuntimeException;

/**
 * Base class for every chatbot failure the controller maps to a user-safe
 * response. Messages are written in Bahasa Melayu and safe to display; the
 * underlying cause is reported server-side.
 */
class ChatbotException extends RuntimeException {}
