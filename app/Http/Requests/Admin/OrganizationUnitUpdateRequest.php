<?php

namespace App\Http\Requests\Admin;

use App\Models\OrganizationUnit;
use App\Support\Hierarchy\HierarchyRules;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class OrganizationUnitUpdateRequest extends OrganizationUnitStoreRequest
{
    /**
     * The unit being edited, resolved from the route binding.
     */
    public function unit(): OrganizationUnit
    {
        /** @var OrganizationUnit $unit */
        $unit = $this->route('organization_unit');

        return $unit;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['code'] = [
            'required',
            'string',
            'max:50',
            Rule::unique('organization_units', 'code')->ignore($this->unit()->id),
        ];

        $rules['parent_id'] = [
            'nullable',
            'integer',
            Rule::exists('organization_units', 'id')->whereNull('parent_id'),
        ];

        return $rules;
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

                $unit = $this->unit();
                $parentId = $this->input('parent_id');

                $createsCycle = HierarchyRules::createsCycle(
                    $unit->id,
                    $parentId === null ? null : (int) $parentId,
                    $unit->descendantIds(),
                );

                if ($createsCycle) {
                    $validator->errors()->add('parent_id', 'Unit tidak boleh diletakkan di bawah dirinya sendiri atau keturunannya.');
                }

                if ($parentId !== null && $unit->children()->exists()) {
                    $validator->errors()->add('parent_id', 'Bahagian ini mempunyai unit anak, jadi ia tidak boleh menjadi unit di bawah bahagian lain.');
                }
            },
        ];
    }
}
