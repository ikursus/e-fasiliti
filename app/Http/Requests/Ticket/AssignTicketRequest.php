<?php

namespace App\Http\Requests\Ticket;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Assignment payload for both single (FR-TKT-09) and bulk assignment
 * (UC-11, 4a). The assignee must hold the juruteknik role.
 */
class AssignTicketRequest extends FormRequest
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
            'juruteknik_id' => ['required', 'integer', Rule::exists('users', 'id')->where('is_active', true)],
            'ids' => ['nullable', 'array', 'min:1', 'max:50'],
            'ids.*' => ['required', 'integer', 'exists:tickets,id'],
        ];
    }

    /**
     * The technician role is what the workload screen is built from; a user
     * without it must not appear assignable.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $user = User::query()->find($this->integer('juruteknik_id'));

                if ($user !== null && ! $user->hasRole('juruteknik')) {
                    $validator->errors()->add(
                        'juruteknik_id',
                        'Pengguna ini tidak berperanan sebagai juruteknik ICT.'
                    );
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'juruteknik_id.required' => 'Sila pilih juruteknik.',
            'ids.required' => 'Pilih sekurang-kurangnya satu tiket untuk agihan pukal.',
            'ids.max' => 'Agihan pukal dibenarkan sehingga 50 tiket sekali gus.',
        ];
    }
}
