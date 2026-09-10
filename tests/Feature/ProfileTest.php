<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_own_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertViewIs('profile.edit')
            ->assertViewHas('user', fn ($viewUser) => $viewUser->is($user))
            ->assertSee($user->name);
    }

    public function test_user_can_update_own_name_and_email(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('profile.update'), [
                'name' => 'Nama Baharu',
                'email' => 'nama.baharu@example.com',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nama Baharu',
            'email' => 'nama.baharu@example.com',
        ]);
    }

    public function test_validation_rejects_missing_name_and_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => '',
                'email' => 'taken@example.com',
            ])
            ->assertSessionHasErrors(['name', 'email']);

        $this->assertDatabaseMissing('users', ['id' => $user->id, 'email' => 'taken@example.com']);
    }

    /**
     * The payload deliberately carries is_active and password so the test can
     * prove the endpoint drops them. Both values are fakes, never credentials.
     */
    public function test_sensitive_attributes_cannot_be_changed_through_profile_update(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $originalPassword = $user->password;

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'is_active' => false,
                'password' => 'fake-value-the-endpoint-must-ignore',
            ])
            ->assertSessionHasNoErrors();

        $user->refresh();

        $this->assertTrue($user->is_active);
        $this->assertSame($originalPassword, $user->password);
    }

    public function test_user_can_upload_a_jpg_profile_photo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('profile.photo.update'), [
                'photo' => UploadedFile::fake()->image('avatar.jpg'),
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status');

        $user->refresh();

        $this->assertNotNull($user->profile_photo_path);
        $this->assertStringStartsWith("profile/{$user->id}/", (string) $user->profile_photo_path);
        Storage::disk('public')->assertExists((string) $user->profile_photo_path);
        $this->assertStringContainsString('/storage/profile/', $user->profilePhotoUrl());
    }

    public function test_new_photo_replaces_the_old_photo_file(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('profile.photo.update'), [
            'photo' => UploadedFile::fake()->image('first.jpg'),
        ]);

        $oldPath = (string) $user->refresh()->profile_photo_path;

        $this->actingAs($user)->put(route('profile.photo.update'), [
            'photo' => UploadedFile::fake()->image('second.png'),
        ]);

        $newPath = (string) $user->refresh()->profile_photo_path;

        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('public')->assertExists($newPath);
        Storage::disk('public')->assertMissing($oldPath);
    }

    public function test_upload_rejects_non_image_files(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('profile.photo.update'), [
                'photo' => UploadedFile::fake()->create('evil.php', 10),
            ])
            ->assertSessionHasErrors('photo');

        $this->assertNull($user->refresh()->profile_photo_path);
    }

    public function test_upload_rejects_files_larger_than_two_megabytes(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('profile.photo.update'), [
                'photo' => UploadedFile::fake()->image('big.jpg')->size(3072),
            ])
            ->assertSessionHasErrors('photo');

        $this->assertNull($user->refresh()->profile_photo_path);
    }

    public function test_profile_changes_are_recorded_in_the_audit_log(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Nama Audit',
            'email' => $user->email,
        ]);

        $this->actingAs($user)->put(route('profile.photo.update'), [
            'photo' => UploadedFile::fake()->image('avatar.png'),
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'profile.updated',
            'record_type' => 'user',
            'record_id' => $user->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'profile.photo.updated',
        ]);
    }

    public function test_guest_cannot_change_password(): void
    {
        $this->put(route('profile.password.update'), [])
            ->assertRedirect(route('login'));
    }

    public function test_user_can_change_own_password(): void
    {
        $user = User::factory()->create();
        $oldHash = $user->password;

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('profile.password.update'), [
                'current_password' => 'password',
                'password' => 'fake-new-password-Aa1',
                'password_confirmation' => 'fake-new-password-Aa1',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status');

        $user->refresh();

        $this->assertNotSame($oldHash, $user->password);
        $this->assertTrue(Hash::check('fake-new-password-Aa1', $user->password));
    }

    public function test_change_password_rejects_wrong_current_password(): void
    {
        $user = User::factory()->create();
        $oldHash = $user->password;

        $this->actingAs($user)
            ->put(route('profile.password.update'), [
                'current_password' => 'not-the-real-current-password',
                'password' => 'fake-new-password-Aa1',
                'password_confirmation' => 'fake-new-password-Aa1',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertSame($oldHash, $user->refresh()->password);
    }

    public function test_change_password_requires_matching_confirmation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('profile.password.update'), [
                'current_password' => 'password',
                'password' => 'fake-new-password-Aa1',
                'password_confirmation' => 'fake-new-password-Aa2',
            ])
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }

    public function test_change_password_enforces_minimum_strength(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('profile.password.update'), [
                'current_password' => 'password',
                'password' => 'fake-weak-password',
                'password_confirmation' => 'fake-weak-password',
            ])
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }

    public function test_current_session_stays_authenticated_after_password_change(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('profile.password.update'), [
                'current_password' => 'password',
                'password' => 'fake-new-password-Aa1',
                'password_confirmation' => 'fake-new-password-Aa1',
            ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_password_change_is_recorded_in_the_audit_log_without_the_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'password',
            'password' => 'fake-new-password-Aa1',
            'password_confirmation' => 'fake-new-password-Aa1',
        ]);

        $log = AuditLog::query()
            ->where('user_id', $user->id)
            ->where('action', 'profile.password.updated')
            ->first();

        $this->assertNotNull($log);
        $this->assertStringNotContainsString('fake-new-password-Aa1', (string) json_encode($log->metadata));
    }

    public function test_other_sessions_are_logged_out_after_password_change(): void
    {
        config(['session.driver' => 'database']);

        $user = User::factory()->create();

        $otherSessionId = Str::random(40);

        DB::table('sessions')->insert([
            'id' => $otherSessionId,
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'fake-agent-other-device',
            'payload' => base64_encode('fake-payload'),
            'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'password',
            'password' => 'fake-new-password-Aa1',
            'password_confirmation' => 'fake-new-password-Aa1',
        ]);

        $this->assertDatabaseMissing('sessions', ['id' => $otherSessionId]);
    }
}
