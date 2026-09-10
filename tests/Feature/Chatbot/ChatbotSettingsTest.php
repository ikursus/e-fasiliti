<?php

namespace Tests\Feature\Chatbot;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\Chatbot\GeminiChatService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SystemConfigurationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatbotSettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SystemConfigurationSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('pentadbir-sistem');
    }

    public function test_the_administrator_sees_the_current_settings(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.settings.chatbot'))
            ->assertOk()
            ->assertSee('gemini-3.6-flash')
            ->assertSee('chatbot_system_prompt', false);
    }

    public function test_the_administrator_can_update_the_settings(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.settings.chatbot'))
            ->put(route('admin.settings.chatbot.update'), [
                'chatbot_enabled' => '1',
                'chatbot_model' => 'gemini-2.5-pro',
                'chatbot_temperature' => '0.7',
                'chatbot_max_output_tokens' => '2048',
                'chatbot_max_history' => '30',
                'chatbot_system_prompt' => 'Anda ialah pembantu ujian.',
            ])
            ->assertRedirect(route('admin.settings.chatbot'))
            ->assertSessionHas('status');

        $this->assertSame('gemini-2.5-pro', setting('chatbot.model'));
        $this->assertSame(0.7, setting('chatbot.temperature'));
        $this->assertSame(2048, setting('chatbot.max_output_tokens'));
        $this->assertSame(30, setting('chatbot.max_history'));
        $this->assertSame('Anda ialah pembantu ujian.', setting('chatbot.system_prompt'));
        $this->assertTrue(setting('chatbot.enabled'));
    }

    public function test_unchecking_the_checkbox_disables_the_assistant(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.settings.chatbot.update'), [
                'chatbot_enabled' => '0',
                'chatbot_model' => 'gemini-3.6-flash',
                'chatbot_temperature' => '0.4',
                'chatbot_max_output_tokens' => '1024',
                'chatbot_max_history' => '20',
                'chatbot_system_prompt' => 'Arahan.',
            ])
            ->assertRedirect(route('admin.settings.chatbot'));

        $this->assertFalse(setting('chatbot.enabled'));
    }

    public function test_temperature_above_two_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.settings.chatbot.update'), [
                'chatbot_enabled' => '1',
                'chatbot_model' => 'gemini-3.6-flash',
                'chatbot_temperature' => '5',
                'chatbot_max_output_tokens' => '1024',
                'chatbot_max_history' => '20',
                'chatbot_system_prompt' => 'Arahan.',
            ])
            ->assertSessionHasErrors('chatbot_temperature');

        $this->assertSame(0.4, setting('chatbot.temperature'));
    }

    public function test_a_model_name_with_url_characters_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.settings.chatbot.update'), [
                'chatbot_enabled' => '1',
                'chatbot_model' => 'models/evil:generateContent',
                'chatbot_temperature' => '0.4',
                'chatbot_max_output_tokens' => '1024',
                'chatbot_max_history' => '20',
                'chatbot_system_prompt' => 'Arahan.',
            ])
            ->assertSessionHasErrors('chatbot_model');
    }

    private const TEST_KEY = 'AIzaKunciUjian123';

    /**
     * A complete, valid form submission. Overrides replace single fields.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'chatbot_enabled' => '1',
            'chatbot_model' => 'gemini-3.6-flash',
            'chatbot_temperature' => '0.4',
            'chatbot_max_output_tokens' => '1024',
            'chatbot_max_history' => '20',
            'chatbot_system_prompt' => 'Arahan.',
        ], $overrides);
    }

    private function saveKey(string $key): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.settings.chatbot.update'), $this->payload(['chatbot_api_key' => $key]))
            ->assertSessionHasNoErrors();
    }

    public function test_the_administrator_saves_an_api_key_from_the_screen(): void
    {
        $this->saveKey(self::TEST_KEY);

        $this->assertSame(self::TEST_KEY, setting(GeminiChatService::API_KEY_SETTING));
    }

    public function test_the_saved_key_is_encrypted_at_rest(): void
    {
        $this->saveKey(self::TEST_KEY);

        $stored = (string) DB::table('system_settings')
            ->where('key', GeminiChatService::API_KEY_SETTING)
            ->value('value');

        $this->assertNotSame('', $stored);
        $this->assertStringNotContainsString(self::TEST_KEY, $stored);
    }

    public function test_a_blank_key_field_keeps_the_existing_key(): void
    {
        $this->saveKey(self::TEST_KEY);

        $this->actingAs($this->admin)
            ->put(route('admin.settings.chatbot.update'), $this->payload(['chatbot_model' => 'gemini-2.5-pro']))
            ->assertSessionHasNoErrors();

        $this->assertSame(self::TEST_KEY, setting(GeminiChatService::API_KEY_SETTING));
        $this->assertSame('gemini-2.5-pro', setting('chatbot.model'));
    }

    public function test_removing_the_key_falls_back_to_the_environment(): void
    {
        config(['services.gemini.key' => 'KUNCI-PERSEKITARAN']);

        $this->saveKey(self::TEST_KEY);

        $this->actingAs($this->admin)
            ->put(route('admin.settings.chatbot.update'), $this->payload(['chatbot_api_key_remove' => '1']))
            ->assertSessionHasNoErrors();

        $this->assertSame('', setting(GeminiChatService::API_KEY_SETTING));

        $gemini = app(GeminiChatService::class);

        $this->assertTrue($gemini->hasApiKey());
        $this->assertFalse($gemini->apiKeyIsFromSettings());
    }

    public function test_the_screen_never_renders_the_saved_key(): void
    {
        $this->saveKey(self::TEST_KEY);

        $this->actingAs($this->admin)
            ->get(route('admin.settings.chatbot'))
            ->assertOk()
            ->assertDontSee(self::TEST_KEY)
            ->assertSee('ditetapkan melalui skrin ini');
    }

    public function test_the_audit_trail_records_the_change_without_the_key(): void
    {
        $this->saveKey(self::TEST_KEY);

        $log = AuditLog::query()
            ->where('record_id', GeminiChatService::API_KEY_SETTING)
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertTrue($log->metadata['value_redacted']);
        $this->assertStringNotContainsString(self::TEST_KEY, (string) json_encode($log->metadata));
    }

    public function test_a_key_containing_invalid_characters_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.settings.chatbot.update'), $this->payload(['chatbot_api_key' => 'kunci dengan ruang']))
            ->assertSessionHasErrors('chatbot_api_key');

        $this->assertSame('', setting(GeminiChatService::API_KEY_SETTING));
    }

    public function test_the_connection_test_reports_a_working_key(): void
    {
        $this->saveKey(self::TEST_KEY);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => 'ok']]]]],
            ]),
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.settings.chatbot.test'))
            ->assertRedirect(route('admin.settings.chatbot'))
            ->assertSessionHas('status');

        Http::assertSent(fn ($request) => $request->hasHeader('x-goog-api-key', self::TEST_KEY));
    }

    public function test_the_connection_test_reports_a_rejected_key(): void
    {
        $this->saveKey(self::TEST_KEY);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'API key not valid']], 400),
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.settings.chatbot.test'))
            ->assertRedirect(route('admin.settings.chatbot'))
            ->assertSessionHasErrors('chatbot_test');
    }

    public function test_the_connection_test_refuses_without_a_key(): void
    {
        config(['services.gemini.key' => '']);

        Http::fake();

        $this->actingAs($this->admin)
            ->post(route('admin.settings.chatbot.test'))
            ->assertSessionHasErrors('chatbot_test');

        Http::assertNothingSent();
    }

    public function test_a_role_without_the_permission_cannot_test_the_connection(): void
    {
        $staff = User::factory()->create();
        $staff->assignRole('kakitangan');

        $this->actingAs($staff)
            ->post(route('admin.settings.chatbot.test'))
            ->assertForbidden();
    }
}
