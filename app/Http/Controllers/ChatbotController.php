<?php

namespace App\Http\Controllers;

use App\Enums\ChatRole;
use App\Http\Requests\Chatbot\ChatMessageRequest;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Services\Chatbot\ChatbotException;
use App\Services\Chatbot\GeminiChatService;
use App\Services\Configuration\SettingsRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * M17 — Pembantu AI: chat page and message handling for every role holding
 * chatbot.guna (FR-CHB-01 to FR-CHB-04).
 */
class ChatbotController extends Controller
{
    public function __construct(
        private readonly GeminiChatService $assistant,
        private readonly SettingsRepository $settings,
    ) {}

    /**
     * The chat page: conversation list beside the active conversation.
     */
    public function index(Request $request): View
    {
        $sessions = $this->sessionsFor((int) Auth::id());

        $active = $sessions->firstWhere('id', (int) $request->query('sesi'))
            ?? $sessions->first();

        return view('chatbot.index', [
            'sessions' => $sessions,
            'activeSession' => $active,
            'initialMessages' => $active === null
                ? []
                : $active->messages()->get()
                    ->map(fn (ChatMessage $message) => [
                        'role' => $message->role->value,
                        'content' => $message->content,
                    ])
                    ->all(),
        ]);
    }

    /**
     * Start an empty conversation and open it (FR-CHB-03).
     */
    public function storeSession(): RedirectResponse
    {
        $session = ChatSession::query()->create([
            'user_id' => Auth::id(),
            'title' => 'Perbualan baharu',
            'last_message_at' => now(),
        ]);

        return to_route('chatbot.index', ['sesi' => $session->id]);
    }

    /**
     * Delete one conversation owned by the current user (FR-CHB-04).
     */
    public function destroySession(ChatSession $chat_session): RedirectResponse
    {
        abort_unless($chat_session->user_id === Auth::id(), 404);

        $chat_session->delete();

        return to_route('chatbot.index')->with('status', 'Perbualan telah dipadam.');
    }

    /**
     * One assistant turn (FR-CHB-02). JSON because the interface posts with
     * fetch and appends both messages in place. Nothing is persisted unless
     * the API answers, so a failed turn leaves no orphan user message to
     * duplicate on retry.
     *
     * @throws ChatbotException handled inline
     */
    public function send(ChatMessageRequest $request, ChatSession $chat_session): JsonResponse
    {
        abort_unless($chat_session->user_id === Auth::id(), 404);

        $text = (string) $request->validated('message');
        $isFirstMessage = ! $chat_session->messages()->exists();

        // Query the model directly: the messages() relation defaults to
        // oldest-first, and chaining latest() onto it would only add a
        // secondary ORDER BY, leaving the oldest message first.
        $history = ChatMessage::query()
            ->where('chat_session_id', $chat_session->id)
            ->latest()
            ->limit(max(1, (int) $this->settings->get('chatbot.max_history', 20)))
            ->get()
            ->reverse()
            ->values();

        try {
            $reply = $this->assistant->send($text, $history);
        } catch (ChatbotException $exception) {
            report($exception);

            return response()->json(['message' => $exception->getMessage()], 503);
        }

        DB::transaction(function () use ($chat_session, $text, $reply, $isFirstMessage): void {
            ChatMessage::query()->create([
                'chat_session_id' => $chat_session->id,
                'role' => ChatRole::User,
                'content' => $text,
            ]);

            ChatMessage::query()->create([
                'chat_session_id' => $chat_session->id,
                'role' => ChatRole::Model,
                'content' => $reply->text,
                'prompt_tokens' => $reply->promptTokens,
                'completion_tokens' => $reply->completionTokens,
            ]);

            // The first user turn names the conversation; later turns keep
            // the title the user has already learned to recognise.
            $chat_session->forceFill([
                'title' => $isFirstMessage ? mb_substr($text, 0, 60) : $chat_session->title,
                'last_message_at' => now(),
            ])->save();
        });

        return response()->json(['reply' => $reply->text]);
    }

    /**
     * The current user's conversations, most recently used first.
     *
     * @return Collection<int, ChatSession>
     */
    private function sessionsFor(int $userId): Collection
    {
        return ChatSession::query()
            ->where('user_id', $userId)
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->get();
    }
}
