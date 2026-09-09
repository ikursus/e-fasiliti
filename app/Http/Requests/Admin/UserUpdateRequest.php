<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserUpdateRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:200'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'password' => ['nullable', Password::default(), 'confirmed'],
            'roles' => ['required', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
            'organization_unit_id' => ['nullable', 'exists:organization_units,id'],
            'primary_location_id' => ['nullable', 'exists:locations,id'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
