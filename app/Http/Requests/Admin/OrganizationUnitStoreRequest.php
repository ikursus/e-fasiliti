<?php

namespace App\Http\Requests\Admin;

use App\Models\OrganizationUnit;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrganizationUnitStoreRequest extends FormRequest
{
    private ?OrganizationUnit $parentRecord = null;

    private bool $parentLoaded = false;

    /**
     * Access is enforced by the can: middleware on the route.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * FR-ORG-02: two levels only. A unit with a parent is a unit; a unit
     * without one is a division, so a parent may never itself have a parent.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('organization_units', 'code')],
            'name' => ['required', 'string', 'max:150'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('organization_units', 'id')->whereNull('parent_id'),
            ],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'parent_id.exists' => 'Induk mesti sebuah bahagian, bukan unit.',
        ];
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

                $this->validateParentIsActive($validator);
            },
        ];
    }

    /**
     * The selected division, loaded once per request. Null for a division.
     */
    protected function parentRecord(): ?OrganizationUnit
    {
        if (! $this->parentLoaded) {
            $parentId = $this->input('parent_id');

            $this->parentRecord = $parentId === null
                ? null
                : OrganizationUnit::query()->find($parentId);

            $this->parentLoaded = true;
        }

        return $this->parentRecord;
    }

    /**
     * Deactivating a division cascades to its units, so an active unit under
     * an inactive division would silently undo that cascade. Keep the
     * invariant symmetric: a unit may only be active if its division is.
     */
    protected function validateParentIsActive(Validator $validator): void
    {
        $parent = $this->parentRecord();

        if ($parent === null || $parent->is_active || ! $this->boolean('is_active')) {
            return;
        }

        $validator->errors()->add(
            'is_active',
            'Unit ini tidak boleh aktif kerana bahagian induknya tidak aktif.'
        );
    }
}
