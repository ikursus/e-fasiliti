<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_for_guests(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertViewIs('auth.login')
            ->assertSee('Log Masuk');
    }

    public function test_valid_credentials_redirect_to_dashboard_and_record_audit_log(): void
    {
        $user = User::factory()->create([
            'email' => 'kakitangan@e-fasiliti.test',
            'password' => 'KataLaluan123!',
        ]);

        $this->post(route('login.store'), [
            'email' => 'kakitangan@e-fasiliti.test',
            'password' => 'KataLaluan123!',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);

        $user->refresh();

        $this->assertNotNull($user->last_login_at);
        $this->assertSame('127.0.0.1', $user->last_login_ip);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'login',
            'status' => 'success',
            'ip_address' => '127.0.0.1',
        ]);
    }

    public function test_invalid_credentials_return_a_validation_error(): void
    {
        User::factory()->create([
            'email' => 'kakitangan@e-fasiliti.test',
            'password' => 'KataLaluan123!',
        ]);

        $this->post(route('login.store'), [
            'email' => 'kakitangan@e-fasiliti.test',
            'password' => 'salah',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_deactivated_account_cannot_log_in_with_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'kakitangan@e-fasiliti.test',
            'password' => 'KataLaluan123!',
            'is_active' => false,
        ]);

        $this->from(route('login'))->post(route('login.store'), [
            'email' => 'kakitangan@e-fasiliti.test',
            'password' => 'KataLaluan123!',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => __('auth.inactive')]);

        $this->assertGuest();
    }

    public function test_account_is_locked_after_five_failed_attempts(): void
    {
        User::factory()->create([
            'email' => 'kakitangan@e-fasiliti.test',
            'password' => 'KataLaluan123!',
        ]);

        foreach (range(1, 5) as $attempt) {
            $this->post(route('login.store'), [
                'email' => 'kakitangan@e-fasiliti.test',
                'password' => 'salah-'.$attempt,
            ])->assertSessionHasErrors('email');
        }

        $this->post(route('login.store'), [
            'email' => 'kakitangan@e-fasiliti.test',
            'password' => 'KataLaluan123!',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();

        $this->assertTrue(
            str_contains(session('errors')->first('email'), 'Terlalu banyak'),
            'The throttle message should be shown after five failed attempts.'
        );
    }

    public function test_logout_ends_the_session_and_redirects_home(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect('/');

        $this->assertGuest();

        $this->get(route('dashboard'))->assertRedirect(route('login'));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'logout',
        ]);
    }

    public function test_successful_login_resets_the_throttle_counter(): void
    {
        $user = User::factory()->create([
            'email' => 'kakitangan@e-fasiliti.test',
            'password' => 'KataLaluan123!',
        ]);

        foreach (range(1, 3) as $attempt) {
            $this->post(route('login.store'), [
                'email' => 'kakitangan@e-fasiliti.test',
                'password' => 'salah-'.$attempt,
            ])->assertSessionHasErrors('email');
        }

        $this->post(route('login.store'), [
            'email' => 'kakitangan@e-fasiliti.test',
            'password' => 'KataLaluan123!',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }
}
