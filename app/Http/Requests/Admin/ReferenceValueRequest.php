<?php

namespace App\Http\Requests\Admin;

use App\Enums\ReferenceValueType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReferenceValueRequest extends FormRequest
{
    /**
     * Access is enforced by the can: middleware on the route.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The code is compared in upper case because the controller stores it
     * that way, so "kotak" must collide with an existing "KOTAK".
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge(['code' => $this->string('code')->upper()->value()]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(ReferenceValueType::class)],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('reference_values', 'code')->where('type', $this->input('type')),
            ],
            'label' => ['required', 'string', 'max:150'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.unique' => 'Kod ini sudah wujud dalam senarai yang sama.',
        ];
    }
}
