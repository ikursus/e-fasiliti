<?php

namespace App\Http\Requests\Admin;

use App\Models\Holiday;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HolidayRequest extends FormRequest
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
            'date' => ['required', 'date_format:Y-m-d'],
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', Rule::in([Holiday::TYPE_PUBLIC, Holiday::TYPE_NO_BOOKING])],
            'recurs_annually' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * One entry per date and type.
     *
     * This cannot use Rule::unique. The date cast writes a full datetime, so
     * the stored value is "2026-05-01 00:00:00". MySQL truncates that into a
     * DATE column and an equality match works, but SQLite keeps the time and
     * the match silently fails, letting the request through to the database
     * unique index and turning a validation error into a 500. whereDate
     * compares the date part and behaves the same on both drivers.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $exists = Holiday::query()
                    ->whereDate('date', $this->input('date'))
                    ->where('type', $this->input('type'))
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('date', 'Tarikh ini sudah wujud bagi jenis yang sama.');
                }
            },
        ];
    }
}
