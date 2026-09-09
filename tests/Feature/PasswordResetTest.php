<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_link_request_returns_success_for_existing_user(): void
    {
        User::factory()->create(['email' => 'kakitangan@e-fasiliti.test']);

        $this->post(route('password.email'), ['email' => 'kakitangan@e-fasiliti.test'])
            ->assertRedirect()
            ->assertSessionHas('status', __('passwords.sent'));
    }

    public function test_reset_link_request_is_silent_for_unknown_email(): void
    {
        $this->post(route('password.email'), ['email' => 'tiada@e-fasiliti.test'])
            ->assertRedirect()
            ->assertSessionHas('status', __('passwords.sent'));
    }

    public function test_password_can_be_reset_with_a_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'kakitangan@e-fasiliti.test',
            'password' => 'KataLaluanLama123!',
        ]);

        $token = Password::createToken($user);

        $this->post(route('password.store'), [
            'token' => $token,
            'email' => 'kakitangan@e-fasiliti.test',
            'password' => 'KataLaluanBaru123!',
            'password_confirmation' => 'KataLaluanBaru123!',
        ])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('status', __('passwords.reset'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_reset_fails_with_an_invalid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'kakitangan@e-fasiliti.test',
            'password' => 'KataLaluanLama123!',
        ]);

        $this->from(route('password.reset', 'token-palsu'))->post(route('password.store'), [
            'token' => 'token-palsu',
            'email' => 'kakitangan@e-fasiliti.test',
            'password' => 'KataLaluanBaru123!',
            'password_confirmation' => 'KataLaluanBaru123!',
        ])
            ->assertRedirect()
            ->assertSessionHasErrors('email', __('passwords.token'));

        $this->assertGuest();
    }

    public function test_reset_requires_matching_password_confirmation(): void
    {
        $user = User::factory()->create(['email' => 'kakitangan@e-fasiliti.test']);
        $token = Password::createToken($user);

        $this->post(route('password.store'), [
            'token' => $token,
            'email' => 'kakitangan@e-fasiliti.test',
            'password' => 'KataLaluanBaru123!',
            'password_confirmation' => 'tidak-sama',
        ])->assertSessionHasErrors('password');
    }
}
