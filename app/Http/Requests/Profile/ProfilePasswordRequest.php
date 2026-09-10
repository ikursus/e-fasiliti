<?php

namespace App\Http\Requests\Profile;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class ProfilePasswordRequest extends FormRequest
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
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            // Verified with the same guard->validate() pattern as
            // ConfirmablePasswordController so the error message stays the
            // Bahasa Melayu one from lang/ms/auth.php.
            'current_password' => [
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $valid = Auth::guard('web')->validate([
                        'email' => (string) $this->user()->email,
                        'password' => (string) $value,
                    ]);

                    if (! $valid) {
                        $fail(__('auth.password'));
                    }
                },
            ],
            'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers(), 'confirmed'],
        ];
    }
}
