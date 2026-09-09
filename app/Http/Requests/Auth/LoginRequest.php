<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials (FR-USR-10 / NFR-S05).
     *
     * @throws ValidationException When the credentials are invalid or the
     *                             account is rate limited / deactivated.
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = $this->only('email', 'password');

        $attempt = Auth::attempt($credentials, (bool) $this->boolean('remember'));

        if (! $attempt) {
            RateLimiter::hit($this->throttleKey(), 15 * 60);

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $user = $this->user();

        // Deactivated accounts are rejected even with valid credentials (FR-USR-07).
        if ($user instanceof User && ! $user->isActive()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => __('auth.inactive'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Prevent brute force by locking the key for 15 minutes after 5 failed
     * attempts (FR-USR-10).
     *
     * @throws ValidationException
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Unique rate limiter key for the login form (email + IP address).
     */
    public function throttleKey(): string
    {
        return Str::lower($this->string('email')).'|'.$this->ip();
    }
}
