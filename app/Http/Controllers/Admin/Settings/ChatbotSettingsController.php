<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Chatbot\ChatbotException;
use App\Services\Chatbot\GeminiChatService;
use App\Services\Configuration\SettingsRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * M17 — assistant configuration, editable without code (FR-CHB-05). The API
 * key is set here too, stored encrypted, with GEMINI_API_KEY in the
 * environment kept as the fallback.
 */
class ChatbotSettingsController extends Controller
{
    public function __construct(
        private readonly SettingsRepository $settings,
        private readonly GeminiChatService $gemini,
    ) {}

    public function edit(): View
    {
        return view('admin.settings.chatbot', [
            'enabled' => (bool) $this->settings->get('chatbot.enabled', false),
            'model' => (string) $this->settings->get('chatbot.model', 'gemini-2.5-flash'),
            'temperature' => (float) $this->settings->get('chatbot.temperature', 0.4),
            'maxOutputTokens' => (int) $this->settings->get('chatbot.max_output_tokens', 1024),
            'maxHistory' => (int) $this->settings->get('chatbot.max_history', 20),
            'systemPrompt' => (string) $this->settings->get('chatbot.system_prompt', GeminiChatService::DEFAULT_SYSTEM_PROMPT),
            'apiKeyConfigured' => $this->gemini->hasApiKey(),
            'apiKeyMasked' => $this->gemini->maskedApiKey(),
            'apiKeyFromSettings' => $this->gemini->apiKeyIsFromSettings(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'chatbot_enabled' => ['sometimes', 'boolean'],
            // The model name becomes part of the request URL, so it is kept
            // to plain identifier characters.
            'chatbot_model' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9][A-Za-z0-9._-]*$/'],
            'chatbot_temperature' => ['required', 'numeric', 'min:0', 'max:2'],
            'chatbot_max_output_tokens' => ['required', 'integer', 'min:64', 'max:8192'],
            'chatbot_max_history' => ['required', 'integer', 'min:2', 'max:50'],
            'chatbot_system_prompt' => ['required', 'string', 'max:8000'],
            // The key travels in an HTTP header, so it is held to the
            // characters Google actually issues.
            'chatbot_api_key' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9._\-]+$/'],
            'chatbot_api_key_remove' => ['sometimes', 'boolean'],
        ], [
            'chatbot_api_key.regex' => 'Kunci API mengandungi aksara yang tidak sah.',
        ]);

        /** @var User $actor */
        $actor = Auth::user();

        $this->settings->set('chatbot.enabled', (bool) ($validated['chatbot_enabled'] ?? false), $actor, 'boolean', 'chatbot');
        $this->settings->set('chatbot.model', $validated['chatbot_model'], $actor, 'teks', 'chatbot');
        $this->settings->set('chatbot.temperature', (float) $validated['chatbot_temperature'], $actor, 'nombor', 'chatbot');
        $this->settings->set('chatbot.max_output_tokens', (int) $validated['chatbot_max_output_tokens'], $actor, 'nombor', 'chatbot');
        $this->settings->set('chatbot.max_history', (int) $validated['chatbot_max_history'], $actor, 'nombor', 'chatbot');
        $this->settings->set('chatbot.system_prompt', $validated['chatbot_system_prompt'], $actor, 'teks', 'chatbot');

        $this->applyApiKey($validated, $actor);

        return to_route('admin.settings.chatbot')->with('status', 'Tetapan Pembantu AI telah disimpan.');
    }

    /**
     * Verify the saved key against Gemini and report the outcome inline.
     */
    public function test(): RedirectResponse
    {
        try {
            $this->gemini->testConnection();
        } catch (ChatbotException $exception) {
            return to_route('admin.settings.chatbot')
                ->withErrors(['chatbot_test' => $exception->getMessage()]);
        }

        return to_route('admin.settings.chatbot')
            ->with('status', 'Sambungan ke Gemini berjaya. Kunci API sah.');
    }

    /**
     * Write the key only when one was actually supplied, or clear it when
     * removal was ticked. A blank field means "keep the current key", which is
     * what lets the form avoid echoing the secret back to the browser.
     *
     * @param  array<string, mixed>  $validated
     */
    private function applyApiKey(array $validated, User $actor): void
    {
        if ((bool) ($validated['chatbot_api_key_remove'] ?? false)) {
            $this->storeApiKey('', $actor);

            return;
        }

        $key = trim((string) ($validated['chatbot_api_key'] ?? ''));

        if ($key !== '') {
            $this->storeApiKey($key, $actor);
        }
    }

    private function storeApiKey(string $key, User $actor): void
    {
        $this->settings->set(
            GeminiChatService::API_KEY_SETTING,
            $key,
            $actor,
            SettingsRepository::SECRET_TYPE,
            'chatbot',
        );
    }
}
