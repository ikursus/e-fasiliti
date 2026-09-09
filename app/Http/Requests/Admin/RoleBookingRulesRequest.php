<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class RoleBookingRulesRequest extends FormRequest
{
    /**
     * Access is enforced by the can: middleware on the route.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'rules' => ['required', 'array'],
            'rules.*.min_duration_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
            'rules.*.max_duration_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
            'rules.*.max_advance_days' => ['required', 'integer', 'min:1', 'max:730'],
        ];
    }

    /**
     * SRS §M01: the minimum duration must be shorter than the maximum.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                foreach ((array) $this->input('rules', []) as $roleId => $values) {
                    $min = (int) ($values['min_duration_minutes'] ?? 0);
                    $max = (int) ($values['max_duration_minutes'] ?? 0);

                    if ($min >= $max) {
                        $validator->errors()->add(
                            "rules.{$roleId}.max_duration_minutes",
                            'Tempoh maksimum mesti lebih panjang daripada tempoh minimum.'
                        );
                    }
                }
            },
        ];
    }
}
