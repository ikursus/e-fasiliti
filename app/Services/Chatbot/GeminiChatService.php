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

    public function __construct(private readonly SettingsRepository $settings) {}

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

        $url = sprintf(
            '%s/models/%s:generateContent',
            rtrim((string) config('services.gemini.base_url'), '/'),
            (string) $this->settings->get('chatbot.model', 'gemini-2.5-flash'),
        );

        $payload = $this->buildPayload($message, $history);

        try {
            $response = Http::asJson()
                ->withHeaders(['x-goog-api-key' => (string) config('services.gemini.key')])
                ->timeout(60)
                ->post($url, $payload);
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

        if ((string) config('services.gemini.key') === '') {
            throw new ChatbotConfigurationException('Kunci API Pembantu AI belum dikonfigurasi. Letakkan GEMINI_API_KEY dalam fail .env.');
        }
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
