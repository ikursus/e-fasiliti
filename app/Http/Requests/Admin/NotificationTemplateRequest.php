<?php

namespace App\Http\Requests\Admin;

use App\Models\NotificationTemplate;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class NotificationTemplateRequest extends FormRequest
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
            'subject' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string', 'max:20000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * FR-ADM-06: only the placeholders declared for this template may be
     * used, so a typo never ships an unresolved token to a real recipient.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var NotificationTemplate $template */
                $template = $this->route('notification_template');

                $allowed = $template->allowedPlaceholders();

                foreach (['subject', 'body'] as $field) {
                    $used = NotificationTemplate::placeholdersIn((string) $this->input($field));
                    $unknown = array_diff($used, $allowed);

                    if ($unknown !== []) {
                        $validator->errors()->add(
                            $field,
                            'Pemegang tempat tidak dikenali: '.implode(', ', $unknown).'.'
                        );
                    }
                }
            },
        ];
    }
}
