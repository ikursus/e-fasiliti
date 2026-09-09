<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Chatbot\GeminiChatService;
use App\Services\Configuration\SettingsRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * M17 — assistant configuration, editable without code (FR-CHB-05). The API
 * key itself stays in the environment; this screen only shows its status.
 */
class ChatbotSettingsController extends Controller
{
    public function __construct(private readonly SettingsRepository $settings) {}

    public function edit(): View
    {
        return view('admin.settings.chatbot', [
            'enabled' => (bool) $this->settings->get('chatbot.enabled', false),
            'model' => (string) $this->settings->get('chatbot.model', 'gemini-2.5-flash'),
            'temperature' => (float) $this->settings->get('chatbot.temperature', 0.4),
            'maxOutputTokens' => (int) $this->settings->get('chatbot.max_output_tokens', 1024),
            'maxHistory' => (int) $this->settings->get('chatbot.max_history', 20),
            'systemPrompt' => (string) $this->settings->get('chatbot.system_prompt', GeminiChatService::DEFAULT_SYSTEM_PROMPT),
            'apiKeyConfigured' => (string) config('services.gemini.key') !== '',
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
        ]);

        /** @var User $actor */
        $actor = Auth::user();

        $this->settings->set('chatbot.enabled', (bool) ($validated['chatbot_enabled'] ?? false), $actor, 'boolean', 'chatbot');
        $this->settings->set('chatbot.model', $validated['chatbot_model'], $actor, 'teks', 'chatbot');
        $this->settings->set('chatbot.temperature', (float) $validated['chatbot_temperature'], $actor, 'nombor', 'chatbot');
        $this->settings->set('chatbot.max_output_tokens', (int) $validated['chatbot_max_output_tokens'], $actor, 'nombor', 'chatbot');
        $this->settings->set('chatbot.max_history', (int) $validated['chatbot_max_history'], $actor, 'nombor', 'chatbot');
        $this->settings->set('chatbot.system_prompt', $validated['chatbot_system_prompt'], $actor, 'teks', 'chatbot');

        return to_route('admin.settings.chatbot')->with('status', 'Tetapan Pembantu AI telah disimpan.');
    }
}
