<?php

namespace App\Services\Chatbot;

use App\Models\ChatMessage;
use App\Services\Configuration\SettingsRepository;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

/**
 * M17 — talks to the Gemini API (Google AI Studio) over plain REST. The
 * generateContent endpoint is used rather than the newer Interactions API
 * because it is fully supported and stateless: the conversation history we
 * already own in chat_messages is the single source of truth, and no
 * server-side interaction state has to be managed or replayed.
 *
 * Every failure surfaces as a ChatbotException subclass carrying a
 * user-safe Bahasa Melayu message.
 */
class GeminiChatService
{
    /**
     * Fallback system prompt when the chatbot.system_prompt setting has no
     * row yet. The SystemConfigurationSeeder seeds the same text.
     */
    public const DEFAULT_SYSTEM_PROMPT = 'Anda ialah Pembantu AI bagi sistem e-Fasiliti (Pengurusan Fasiliti & Aset ICT). Jawab dalam Bahasa Melayu yang ringkas, jelas dan sopan. Tumpukan pada membantu pengguna tentang tempahan bilik mesyuarat, aduan kerosakan ICT, inventari aset dan prosedur sistem e-Fasiliti. Jika pertanyaan di luar skop itu, jawab dengan sopan bahawa anda hanya membantu perkara berkaitan e-Fasiliti. Jangan mengarang fakta tentang data sistem; jika tidak pasti, nasihatkan pengguna menyemak modul berkaitan atau menghubungi pentadbir sistem.';

    /**
     * Settings key holding the Gemini API key, stored encrypted.
     */
    public const API_KEY_SETTING = 'chatbot.api_key';

    private const MISSING_KEY_MESSAGE = 'Kunci API Pembantu AI belum dikonfigurasi. Tetapkan kunci pada skrin Konfigurasi > Pembantu AI.';

    public function __construct(private readonly SettingsRepository $settings) {}

    /**
     * Whether a key is available from either source.
     */
    public function hasApiKey(): bool
    {
        return $this->apiKey() !== '';
    }

    /**
     * Whether the key in force came from the settings screen rather than the
     * environment. False when there is no key at all, so pair it with
     * hasApiKey().
     */
    public function apiKeyIsFromSettings(): bool
    {
        return $this->storedApiKey() !== '';
    }

    /**
     * The key in force reduced to its last few characters, safe to render.
     */
    public function maskedApiKey(): string
    {
        $key = $this->apiKey();

        return $key === '' ? '' : str_repeat('•', 8).mb_substr($key, -4);
    }

    /**
     * Check the key in force against Gemini with one minimal request.
     *
     * Deliberately independent of chatbot.enabled, so an administrator can
     * verify a key before switching the assistant on.
     *
     * @throws ChatbotConfigurationException
     * @throws ChatbotApiException
     */
    public function testConnection(): void
    {
        $key = $this->apiKey();

        if ($key === '') {
            throw new ChatbotConfigurationException(self::MISSING_KEY_MESSAGE);
        }

        try {
            $response = Http::asJson()
                ->withHeaders(['x-goog-api-key' => $key])
                ->timeout(20)
                ->post($this->endpoint(), [
                    'contents' => [$this->turnPayload('user', 'ping')],
                    'generationConfig' => ['maxOutputTokens' => 1],
                ]);
        } catch (ConnectionException $exception) {
            report($exception);

            throw new ChatbotApiException('Pembantu AI tidak dapat dihubungi buat masa ini. Cuba lagi sebentar.');
        }

        if (in_array($response->status(), [400, 401, 403], true)) {
            throw new ChatbotApiException('Kunci API ditolak oleh Gemini (status '.$response->status().'). Semak kunci dan nama model.');
        }

        if ($response->failed()) {
            report('Gemini API mengembalikan status '.$response->status().': '.$response->body());

            throw new ChatbotApiException('Perkhidmatan Pembantu AI mengalami masalah (status '.$response->status().').');
        }
    }

    /**
     * Send one user turn with the prior conversation and return the reply.
     *
     * @param  Collection<int, ChatMessage>  $history  earlier turns, oldest first
     *
     * @throws ChatbotDisabledException
     * @throws ChatbotConfigurationException
     * @throws ChatbotApiException
     */
    public function send(string $message, Collection $history): ChatbotReply
    {
        $this->assertUsable();

        $payload = $this->buildPayload($message, $history);

        try {
            $response = Http::asJson()
                ->withHeaders(['x-goog-api-key' => $this->apiKey()])
                ->timeout(60)
                ->post($this->endpoint(), $payload);
        } catch (ConnectionException $exception) {
            report($exception);

            throw new ChatbotApiException('Pembantu AI tidak dapat dihubungi buat masa ini. Cuba lagi sebentar.');
        }

        if ($response->failed()) {
            report('Gemini API mengembalikan status '.$response->status().': '.$response->body());

            throw new ChatbotApiException('Perkhidmatan Pembantu AI mengalami masalah. Cuba lagi sebentar.');
        }

        return $this->interpret($response->json());
    }

    private function assertUsable(): void
    {
        if (! (bool) $this->settings->get('chatbot.enabled', false)) {
            throw new ChatbotDisabledException('Pembantu AI sedang dinyahaktifkan oleh pentadbir sistem.');
        }

        if (! $this->hasApiKey()) {
            throw new ChatbotConfigurationException(self::MISSING_KEY_MESSAGE);
        }
    }

    /**
     * The generateContent endpoint for the configured model.
     */
    private function endpoint(): string
    {
        return sprintf(
            '%s/models/%s:generateContent',
            rtrim((string) config('services.gemini.base_url'), '/'),
            (string) $this->settings->get('chatbot.model', 'gemini-2.5-flash'),
        );
    }

    /**
     * The key in force. The value saved from the settings screen wins, so an
     * administrator can set one without shell access, and GEMINI_API_KEY in
     * the environment stays as the fallback for existing installations.
     */
    private function apiKey(): string
    {
        $stored = $this->storedApiKey();

        return $stored !== '' ? $stored : trim((string) config('services.gemini.key'));
    }

    private function storedApiKey(): string
    {
        return trim((string) $this->settings->get(self::API_KEY_SETTING, ''));
    }

    /**
     * @param  Collection<int, ChatMessage>  $history
     * @return array<string, mixed>
     */
    private function buildPayload(string $message, Collection $history): array
    {
        return [
            'systemInstruction' => [
                'parts' => [['text' => $this->systemPrompt()]],
            ],
            'contents' => $history
                ->map(fn (ChatMessage $turn) => $this->turnPayload($turn->role->value, $turn->content))
                ->push($this->turnPayload('user', $message))
                ->values()
                ->all(),
            'generationConfig' => [
                'temperature' => (float) $this->settings->get('chatbot.temperature', 0.4),
                'maxOutputTokens' => (int) $this->settings->get('chatbot.max_output_tokens', 1024),
            ],
        ];
    }

    private function systemPrompt(): string
    {
        $prompt = (string) $this->settings->get('chatbot.system_prompt', '');

        return $prompt !== '' ? $prompt : self::DEFAULT_SYSTEM_PROMPT;
    }

    /**
     * @return array{role: string, parts: array<int, array{text: string}>}
     */
    private function turnPayload(string $role, string $text): array
    {
        return [
            'role' => $role,
            'parts' => [['text' => $text]],
        ];
    }

    /**
     * @param  array<string, mixed>|null  $body
     */
    private function interpret(?array $body): ChatbotReply
    {
        $text = $this->extractText($body);

        if ($text === '') {
            report('Gemini API tiada teks dalam calon pertama: '.json_encode($body));

            throw new ChatbotApiException('Pembantu AI tidak memulangkan balasan. Cuba lagi atau mula perbualan baharu.');
        }

        $usage = is_array($body['usageMetadata'] ?? null) ? $body['usageMetadata'] : [];

        return new ChatbotReply(
            text: $text,
            promptTokens: is_numeric($usage['promptTokenCount'] ?? null) ? (int) $usage['promptTokenCount'] : null,
            completionTokens: is_numeric($usage['candidatesTokenCount'] ?? null) ? (int) $usage['candidatesTokenCount'] : null,
        );
    }

    /**
     * Concatenate every text part of the first candidate. Safety blocks and
     * empty answers both degrade to an empty string, which interpret()
     * converts into a user-safe API error.
     *
     * @param  array<string, mixed>|null  $body
     */
    private function extractText(?array $body): string
    {
        $parts = $body['candidates'][0]['content']['parts'] ?? null;

        if (! is_array($parts)) {
            return '';
        }

        $text = '';

        foreach ($parts as $part) {
            if (is_array($part) && is_string($part['text'] ?? null)) {
                $text .= $part['text'];
            }
        }

        return trim($text);
    }
}
