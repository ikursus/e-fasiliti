<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_sensitive_attributes_cannot_be_changed_through_profile_update(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $originalPassword = $user->password;

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'is_active' => false,
                'password' => 'Pentadbir123!',
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
}
