<?php

namespace App\Http\Requests\Admin;

use App\Models\Room;
use Illuminate\Contracts\Validation\Validator;

class RoomUpdateRequest extends RoomStoreRequest
{
    /**
     * The room being edited, resolved from the route binding.
     */
    public function room(): Room
    {
        /** @var Room $room */
        $room = $this->route('room');

        return $room;
    }

    /**
     * The edit screen also maintains the room's own operating hours, using
     * the same seven-day shape as the organisation defaults (FR-ADM-01).
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'days' => ['required', 'array', 'size:7'],
            'days.*.is_closed' => ['nullable', 'boolean'],
            'days.*.opens_at' => ['nullable', 'date_format:H:i'],
            'days.*.closes_at' => ['nullable', 'date_format:H:i'],
        ]);
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $this->validateLocationIsARuang($validator);
                $this->validateLocationIsActive($validator);
                $this->validateDurations($validator);
                $this->validateSingleDefaultLayout($validator);
                $this->validateCodeIsUnique($validator, $this->room()->id);
                $this->validateDayTimes($validator);
            },
        ];
    }

    /**
     * Operating days must carry both times, and closing must follow
     * opening — identical to the organisation defaults in M01.
     */
    protected function validateDayTimes(Validator $validator): void
    {
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
    }
}
