<?php

namespace Tests\Feature\Chatbot;

use App\Models\ChatMessage;
use App\Services\Chatbot\ChatbotApiException;
use App\Services\Chatbot\ChatbotConfigurationException;
use App\Services\Chatbot\ChatbotDisabledException;
use App\Services\Chatbot\ChatbotReply;
use App\Services\Chatbot\GeminiChatService;
use Database\Seeders\SystemConfigurationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeminiChatServiceTest extends TestCase
{
    use RefreshDatabase;

    private GeminiChatService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SystemConfigurationSeeder::class);
        config(['services.gemini.key' => 'test-key']);

        $this->service = app(GeminiChatService::class);
    }

    public function test_it_returns_the_reply_text_and_token_usage(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => 'Selamat pagi!'], ['text' => ' Ada apa?']]]],
                ],
                'usageMetadata' => ['promptTokenCount' => 30, 'candidatesTokenCount' => 9],
            ]),
        ]);

        $reply = $this->service->send('Helo', collect());

        $this->assertInstanceOf(ChatbotReply::class, $reply);
        $this->assertSame('Selamat pagi! Ada apa?', $reply->text);
        $this->assertSame(30, $reply->promptTokens);
        $this->assertSame(9, $reply->completionTokens);
    }

    public function test_it_builds_the_expected_request(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => 'Ok.']]]]],
            ]),
        ]);

        $history = ChatMessage::factory()->fromUser()->make();

        $this->service->send('Soalan baharu', collect([$history]));

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/models/gemini-2.5-flash:generateContent')
                && $request->hasHeader('x-goog-api-key', 'test-key')
                && data_get($request, 'systemInstruction.parts.0.text') !== null
                && data_get($request, 'contents.0.role') === 'user'
                && data_get($request, 'contents.1.role') === 'user';
        });
    }

    public function test_it_throws_when_the_assistant_is_disabled(): void
    {
        app(\App\Services\Configuration\SettingsRepository::class)->set('chatbot.enabled', false);

        $this->expectException(ChatbotDisabledException::class);

        $this->service->send('Helo', collect());
    }

    public function test_it_throws_when_the_api_key_is_missing(): void
    {
        config(['services.gemini.key' => '']);

        $this->expectException(ChatbotConfigurationException::class);

        $this->service->send('Helo', collect());
    }

    public function test_it_throws_when_the_api_returns_a_server_error(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'boom']], 500),
        ]);

        $this->expectException(ChatbotApiException::class);

        $this->service->send('Helo', collect());
    }

    public function test_it_throws_when_no_text_is_returned(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['finishReason' => 'SAFETY']],
            ]),
        ]);

        $this->expectException(ChatbotApiException::class);

        $this->service->send('Helo', collect());
    }
}
