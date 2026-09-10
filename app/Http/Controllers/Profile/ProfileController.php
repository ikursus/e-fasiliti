<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\ProfilePasswordRequest;
use App\Http\Requests\Profile\ProfilePhotoRequest;
use App\Http\Requests\Profile\ProfileUpdateRequest;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Self-service profile module. Every action operates on the authenticated
 * user only, so no route binds another user's model.
 */
class ProfileController extends Controller
{
    /**
     * Show the profile form (details, photo and read-only organisation info).
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the authenticated user's own details.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        /** @var array{name: string, email: string} $before */
        $before = $user->only(['name', 'email']);

        $user->fill($request->safe()->only(['name', 'email']));
        $user->save();

        // Per .ai/rules/admin.md: take the "after" snapshot from a refreshed
        // model so both sides hold Eloquent-cast values for strict comparison.
        /** @var array{name: string, email: string} $after */
        $after = $user->refresh()->only(['name', 'email']);

        $this->audit('profile.updated', $request, [
            'changed_fields' => array_keys(array_diff_assoc($after, $before)),
        ]);

        return to_route('profile.edit')
            ->with('status', 'Maklumat profil telah dikemas kini.');
    }

    /**
     * Replace the authenticated user's profile photo. The new file is stored
     * first, then the database is updated, then the old file is removed.
     */
    public function updatePhoto(ProfilePhotoRequest $request): RedirectResponse
    {
        $user = $request->user();

        $path = $request->file('photo')->store("profile/{$user->id}", 'public');

        $oldPath = $user->profile_photo_path;

        $user->profile_photo_path = $path;
        $user->save();

        if (is_string($oldPath) && $oldPath !== $path) {
            Storage::disk('public')->delete($oldPath);
        }

        $this->audit('profile.photo.updated', $request, ['path' => $path]);

        return to_route('profile.edit')
            ->with('status', 'Gambar profil telah dikemas kini.');
    }

    /**
     * Change the authenticated user's own password. The current password is
     * verified by ProfilePasswordRequest, then every other session of this
     * user is terminated (the current session stays signed in).
     */
    public function updatePassword(ProfilePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();

        // The "hashed" cast on the model hashes the plaintext automatically
        // (NFR-S02) — the plain value is never stored.
        $user->password = $request->input('password');
        $user->save();

        // Dev/prod use the database session driver: drop every other session
        // of this user so other devices are logged out after the change.
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', $request->session()->getId())
            ->delete();

        // Never record the password itself in the audit trail.
        $this->audit('profile.password.updated', $request);

        return to_route('profile.edit')
            ->with('status', 'Kata laluan anda telah dikemas kini.');
    }

    /**
     * Record a self-service profile action in the audit trail (FR-AUD-03).
     *
     * @param  array<string, mixed>  $metadata
     */
    private function audit(string $action, Request $request, array $metadata = []): void
    {
        /** @var User $actor */
        $actor = Auth::user();

        AuditLog::create([
            'user_id' => $actor->id,
            'action' => $action,
            'status' => 'success',
            'record_type' => 'user',
            'record_id' => $actor->id,
            'ip_address' => $request->ip(),
            'user_agent' => str($request->userAgent() ?? '')->limit(255),
            'metadata' => $metadata,
        ]);
    }
}
