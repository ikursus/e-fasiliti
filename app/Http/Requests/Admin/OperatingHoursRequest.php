<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class OperatingHoursRequest extends FormRequest
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
            'days' => ['required', 'array', 'size:7'],
            'days.*.is_closed' => ['nullable', 'boolean'],
            'days.*.opens_at' => ['nullable', 'date_format:H:i'],
            'days.*.closes_at' => ['nullable', 'date_format:H:i'],
        ];
    }

    /**
     * SRS §M01: the closing time must be after the opening time on any day
     * that is not marked closed.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                foreach ((array) $this->input('days', []) as $day => $values) {
                    if ((bool) ($values['is_closed'] ?? false)) {
                        continue;
                    }

                    $opens = $values['opens_at'] ?? null;
                    $closes = $values['closes_at'] ?? null;

                    if ($opens === null || $opens === '' || $closes === null || $closes === '') {
                        $validator->errors()->add("days.{$day}.opens_at", 'Waktu buka dan tutup wajib diisi bagi hari yang beroperasi.');

                        continue;
                    }

                    if (strtotime($closes) <= strtotime($opens)) {
                        $validator->errors()->add("days.{$day}.closes_at", 'Waktu tutup mesti selepas waktu buka.');
                    }
                }
            },
        ];
    }
}
