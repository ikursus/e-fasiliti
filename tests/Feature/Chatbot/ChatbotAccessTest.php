<?php

namespace Tests\Feature\Chatbot;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('chatbot.index'))
            ->assertRedirect(route('login'));
    }

    public function test_every_role_holds_chatbot_guna_and_may_open_the_chat(): void
    {
        foreach (RolesAndPermissionsSeeder::ROLES as $slug => $label) {
            $user = User::factory()->create();
            $user->assignRole($slug);

            $this->actingAs($user)
                ->get(route('chatbot.index'))
                ->assertOk();
        }
    }

    public function test_a_user_without_any_role_is_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('chatbot.index'))
            ->assertForbidden();
    }

    public function test_only_roles_holding_chatbot_tetapan_may_open_the_settings(): void
    {
        $staff = User::factory()->create();
        $staff->assignRole('kakitangan');

        $this->actingAs($staff)
            ->get(route('admin.settings.chatbot'))
            ->assertForbidden();

        $admin = User::factory()->create();
        $admin->assignRole('pentadbir-sistem');

        $this->actingAs($admin)
            ->get(route('admin.settings.chatbot'))
            ->assertOk();
    }
}
