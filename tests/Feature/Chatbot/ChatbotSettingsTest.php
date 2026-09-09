<?php

namespace Tests\Feature\Chatbot;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SystemConfigurationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->assertSee('gemini-2.5-flash')
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
                'chatbot_model' => 'gemini-2.5-flash',
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
                'chatbot_model' => 'gemini-2.5-flash',
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
}
