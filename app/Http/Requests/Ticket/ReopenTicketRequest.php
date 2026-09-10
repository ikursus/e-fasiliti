<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FR-TKT-19: reopening requires a reason so the technician knows what is
 * still broken instead of rediscovering it.
 */
class ReopenTicketRequest extends FormRequest
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
            'alasan' => ['required', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'alasan.required' => 'Nyatakan masalah yang masih berlaku supaya juruteknik tahu apa yang perlu diteruskan.',
        ];
    }
}
