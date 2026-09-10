<?php

namespace App\Http\Requests\Admin;

use App\Models\Asset;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class AssetUpdateRequest extends AssetStoreRequest
{
    /**
     * The asset being edited, resolved from the route binding.
     */
    public function asset(): Asset
    {
        /** @var Asset $asset */
        $asset = $this->route('asset');

        return $asset;
    }

    /**
     * Unique checks ignore the record itself, and a change of location,
     * owner or status must carry a reason (FR-AST-06).
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'registration_number' => ['required', 'string', 'max:50', Rule::unique('assets', 'registration_number')->ignore($this->asset()->id)],
            'serial_number' => ['nullable', 'string', 'max:100', Rule::unique('assets', 'serial_number')->ignore($this->asset()->id)],
            'reason' => ['nullable', 'string', 'max:500'],
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

                $this->validateReasonForMovement($validator);
            },
        ];
    }

    /**
     * Every owner, location or status change must carry a reason so the
     * history can answer "why did this move?" (FR-AST-06).
     */
    protected function validateReasonForMovement(Validator $validator): void
    {
        $asset = $this->asset();

        $moves = collect(['location_id', 'responsible_user_id', 'status'])->contains(
            fn (string $field): bool => (string) $this->input($field)
                !== ($field === 'status' ? $asset->status->value : (string) $asset->{$field}),
        );

        if ($moves && trim((string) $this->input('reason')) === '') {
            $validator->errors()->add(
                'reason',
                'Sebab perubahan wajib diisi apabila lokasi, pemilik atau status aset berubah.'
            );
        }
    }
}
