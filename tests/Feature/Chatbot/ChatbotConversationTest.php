<?php

namespace Tests\Feature\Chatbot;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\User;
use App\Services\Configuration\SettingsRepository;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SystemConfigurationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatbotConversationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SystemConfigurationSeeder::class);
        config(['services.gemini.key' => 'test-key']);

        $this->user = User::factory()->create();
        $this->user->assignRole('kakitangan');
    }

    /**
     * @return array<string, mixed>
     */
    private function geminiResponse(string $text = 'Balasan pembantu.'): array
    {
        return [
            'candidates' => [
                ['content' => ['parts' => [['text' => $text]], 'role' => 'model']],
            ],
            'usageMetadata' => ['promptTokenCount' => 21, 'candidatesTokenCount' => 7],
        ];
    }

    public function test_a_message_is_answered_and_both_turns_are_stored(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response($this->geminiResponse('Halo! Bagaimana saya boleh membantu?')),
        ]);

        $session = ChatSession::factory()->for($this->user)->create();

        $this->actingAs($this->user)
            ->postJson(route('chatbot.messages.store', $session), ['message' => 'Apakah waktu operasi bilik mesyuarat?'])
            ->assertOk()
            ->assertJsonPath('reply', 'Halo! Bagaimana saya boleh membantu?');

        $this->assertDatabaseHas('chat_messages', [
            'chat_session_id' => $session->id,
            'role' => 'user',
            'content' => 'Apakah waktu operasi bilik mesyuarat?',
        ]);

        $this->assertDatabaseHas('chat_messages', [
            'chat_session_id' => $session->id,
            'role' => 'model',
            'completion_tokens' => 7,
        ]);

        // The first user turn names the conversation.
        $this->assertSame('Apakah waktu operasi bilik mesyuarat?', $session->refresh()->title);
    }

    public function test_the_payload_carries_system_instruction_generation_config_and_history(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response($this->geminiResponse()),
        ]);

        $session = ChatSession::factory()->for($this->user)->create();

        ChatMessage::factory()->fromUser()->count(2)->create(['chat_session_id' => $session->id]);
        ChatMessage::factory()->fromModel()->count(2)->create(['chat_session_id' => $session->id]);

        // Distinct timestamps (via query builder, bypassing fillable) so the
        // oldest-first history order is deterministic even though every row
        // lands in the same second.
        $ids = ChatMessage::query()->where('chat_session_id', $session->id)->orderBy('id')->pluck('id')->all();

        foreach ($ids as $index => $id) {
            DB::table('chat_messages')->where('id', $id)->update([
                'created_at' => now()->subMinutes(10 - $index),
            ]);
        }

        $this->actingAs($this->user)
            ->postJson(route('chatbot.messages.store', $session), ['message' => 'Soalan baharu'])
            ->assertOk();

        Http::assertSent(function ($request) {
            $contents = data_get($request, 'contents');

            return $request->hasHeader('x-goog-api-key', 'test-key')
                && str_contains($request->url(), '/models/gemini-3.6-flash:generateContent')
                && data_get($request, 'systemInstruction.parts.0.text') !== null
                && data_get($request, 'generationConfig.temperature') === 0.4
                && data_get($request, 'generationConfig.maxOutputTokens') === 1024
                && count($contents) === 5
                && $contents[4]['role'] === 'user'
                && $contents[4]['parts'][0]['text'] === 'Soalan baharu'
                && $contents[3]['role'] === 'model';
        });
    }

    public function test_history_sent_is_capped_to_the_configured_number_of_turns(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response($this->geminiResponse()),
        ]);

        $session = ChatSession::factory()->for($this->user)->create();
        ChatMessage::factory()->count(30)->create(['chat_session_id' => $session->id]);

        $this->actingAs($this->user)
            ->postJson(route('chatbot.messages.store', $session), ['message' => 'Soalan baharu'])
            ->assertOk();

        // max_history default 20 plus the new user turn.
        Http::assertSent(fn ($request) => count(data_get($request, 'contents')) === 21);
    }

    public function test_a_failed_api_call_leaves_no_messages_behind(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'boom']], 500),
        ]);

        $session = ChatSession::factory()->for($this->user)->create();

        $this->actingAs($this->user)
            ->postJson(route('chatbot.messages.store', $session), ['message' => 'Soalan'])
            ->assertStatus(503)
            ->assertJsonStructure(['message']);

        $this->assertDatabaseCount('chat_messages', 0);
    }

    public function test_a_disabled_assistant_returns_a_friendly_error(): void
    {
        app(SettingsRepository::class)->set('chatbot.enabled', false);

        $session = ChatSession::factory()->for($this->user)->create();

        $this->actingAs($this->user)
            ->postJson(route('chatbot.messages.store', $session), ['message' => 'Soalan'])
            ->assertStatus(503)
            ->assertJsonPath('message', 'Pembantu AI sedang dinyahaktifkan oleh pentadbir sistem.');

        $this->assertDatabaseCount('chat_messages', 0);
    }

    public function test_a_missing_api_key_returns_a_friendly_error(): void
    {
        config(['services.gemini.key' => '']);

        $session = ChatSession::factory()->for($this->user)->create();

        $this->actingAs($this->user)
            ->postJson(route('chatbot.messages.store', $session), ['message' => 'Soalan'])
            ->assertStatus(503);

        $this->assertDatabaseCount('chat_messages', 0);
    }

    public function test_users_cannot_send_messages_to_someone_elses_session(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response($this->geminiResponse()),
        ]);

        $other = ChatSession::factory()->create();

        $this->actingAs($this->user)
            ->postJson(route('chatbot.messages.store', $other), ['message' => 'Soalan'])
            ->assertNotFound();
    }

    public function test_users_may_delete_only_their_own_session(): void
    {
        $other = ChatSession::factory()->create();
        $own = ChatSession::factory()->for($this->user)->create();

        $this->actingAs($this->user)
            ->delete(route('chatbot.sessions.destroy', $other))
            ->assertNotFound();

        $this->actingAs($this->user)
            ->delete(route('chatbot.sessions.destroy', $own))
            ->assertRedirect(route('chatbot.index'));

        $this->assertDatabaseMissing('chat_sessions', ['id' => $own->id]);
    }

    public function test_validation_rejects_empty_and_oversized_messages(): void
    {
        $session = ChatSession::factory()->for($this->user)->create();

        $this->actingAs($this->user)
            ->postJson(route('chatbot.messages.store', $session), ['message' => ''])
            ->assertUnprocessable();

        $this->actingAs($this->user)
            ->postJson(route('chatbot.messages.store', $session), ['message' => str_repeat('a', 4001)])
            ->assertUnprocessable();

        $this->assertDatabaseCount('chat_messages', 0);
    }
}
