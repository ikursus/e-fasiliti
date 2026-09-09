<?php

namespace App\Http\Requests\Admin;

use App\Enums\LocationLevel;
use App\Enums\ReferenceValueType;
use App\Models\Location;
use App\Models\Room;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoomStoreRequest extends FormRequest
{
    private ?Location $locationRecord = null;

    private bool $locationLoaded = false;

    /**
     * Access is enforced by the can: middleware on the route.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Field lengths follow the BILIK entity in DRD §4.2. Layouts and
     * facilities must come from the seeded reference lists so the M05
     * filters never meet an unknown value.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:30'],
            'name' => ['required', 'string', 'max:150'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'base_capacity' => ['required', 'integer', 'min:1'],
            'requires_approval' => ['nullable', 'boolean'],
            'allowed_roles' => ['nullable', 'array'],
            'allowed_roles.*' => ['string', 'distinct', Rule::exists('roles', 'name')],
            'min_duration_minutes' => ['required', 'integer', 'min:1'],
            'max_duration_minutes' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],

            'layouts' => ['required', 'array', 'min:1'],
            'layouts.*.layout_code' => [
                'required',
                'string',
                'max:50',
                'distinct',
                Rule::exists('reference_values', 'code')
                    ->where('type', ReferenceValueType::SusunAturBilik->value)
                    ->where('is_active', true),
            ],
            'layouts.*.capacity' => ['required', 'integer', 'min:1'],
            'layouts.*.is_default' => ['nullable', 'boolean'],

            'facilities' => ['nullable', 'array'],
            'facilities.*' => [
                'string',
                'distinct',
                Rule::exists('reference_values', 'code')
                    ->where('type', ReferenceValueType::KemudahanBilik->value)
                    ->where('is_active', true),
            ],
        ];
    }

    /**
     * Cross-field rules plain rules cannot express.
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

                $this->validateLocationIsARuang($validator);
                $this->validateLocationIsActive($validator);
                $this->validateDurations($validator);
                $this->validateSingleDefaultLayout($validator);
                $this->validateCodeIsUnique($validator);
            },
        ];
    }

    /**
     * The selected location, loaded once per request.
     */
    protected function locationRecord(): ?Location
    {
        if (! $this->locationLoaded) {
            $locationId = $this->input('location_id');

            $this->locationRecord = $locationId === null
                ? null
                : Location::query()->find($locationId);

            $this->locationLoaded = true;
        }

        return $this->locationRecord;
    }

    /**
     * DRD §4.2: the room's location must sit at ruang level.
     */
    protected function validateLocationIsARuang(Validator $validator): void
    {
        $location = $this->locationRecord();

        if ($location !== null && $location->level === LocationLevel::Ruang) {
            return;
        }

        $validator->errors()->add('location_id', 'Bilik mesti diletakkan di bawah lokasi aras ruang.');
    }

    /**
     * Same two-way invariant as the location hierarchy: an active room
     * cannot sit inside an inactive building or campus.
     */
    protected function validateLocationIsActive(Validator $validator): void
    {
        $location = $this->locationRecord();

        if ($location === null || $location->is_active || ! $this->boolean('is_active')) {
            return;
        }

        $validator->errors()->add('is_active', 'Bilik tidak boleh aktif kerana lokasinya tidak aktif.');
    }

    /**
     * FR-ADM-03 validation rule, applied per room: minimum below maximum.
     */
    protected function validateDurations(Validator $validator): void
    {
        $min = (int) $this->input('min_duration_minutes');
        $max = (int) $this->input('max_duration_minutes');

        if ($min < $max) {
            return;
        }

        $validator->errors()->add('max_duration_minutes', 'Tempoh maksimum mesti melebihi tempoh minimum.');
    }

    /**
     * DRD §4.2 SUSUN_ATUR_BILIK: exactly one default layout per room, so
     * M05 always has an unambiguous capacity to compare participants with.
     */
    protected function validateSingleDefaultLayout(Validator $validator): void
    {
        $defaults = collect((array) $this->input('layouts', []))
            ->filter(fn ($layout) => (bool) ($layout['is_default'] ?? false))
            ->count();

        if ($defaults === 1) {
            return;
        }

        $validator->errors()->add(
            'layouts',
            $defaults === 0
                ? 'Satu susun atur mesti ditandakan sebagai lalai.'
                : 'Hanya satu susun atur boleh ditandakan sebagai lalai.'
        );
    }

    /**
     * The code is unique across the whole catalogue (DRD §4.2). Compared
     * case-insensitively so the same row is rejected on MySQL and SQLite
     * alike.
     */
    protected function validateCodeIsUnique(Validator $validator, ?int $ignoreId = null): void
    {
        $exists = Room::query()
            ->whereRaw('LOWER(code) = ?', [mb_strtolower((string) $this->input('code'))])
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();

        if ($exists) {
            $validator->errors()->add('code', 'Kod bilik ini telah digunakan.');
        }
    }
}
