<?php

namespace App\Http\Requests\Ticket;

use App\Enums\TicketPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * FR-TKT-08: the supervisor may change priority, but only with a written
 * reason, and the SLA targets are recomputed afterwards.
 */
class UpdatePriorityRequest extends FormRequest
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
            'keutamaan' => ['required', Rule::enum(TicketPriority::class)],
            'sebab' => ['required', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'keutamaan.required' => 'Sila pilih keutamaan baharu.',
            'sebab.required' => 'Sebab perubahan keutamaan wajib diisi.',
        ];
    }
}
