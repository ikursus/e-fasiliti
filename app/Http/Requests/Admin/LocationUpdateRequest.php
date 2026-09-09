<?php

namespace App\Http\Requests\Admin;

use App\Enums\LocationLevel;
use App\Models\Location;
use App\Support\Hierarchy\HierarchyRules;
use Illuminate\Contracts\Validation\Validator;

class LocationUpdateRequest extends LocationStoreRequest
{
    /**
     * The location being edited, resolved from the route binding.
     */
    public function location(): Location
    {
        /** @var Location $location */
        $location = $this->route('location');

        return $location;
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

                $this->validateParentLevel($validator);
                $this->validateParentIsActive($validator);
                $this->validateCodeIsUniqueWithinParent($validator, $this->location()->id);
                $this->validateNoCycle($validator);
                $this->validateLevelChange($validator);
            },
        ];
    }

    /**
     * FR-ORG-01 and DI-07: a location may not sit under itself or its own
     * descendant.
     */
    protected function validateNoCycle(Validator $validator): void
    {
        $location = $this->location();
        $parentId = $this->input('parent_id');

        $createsCycle = HierarchyRules::createsCycle(
            $location->id,
            $parentId === null ? null : (int) $parentId,
            $location->descendantIds(),
        );

        if ($createsCycle) {
            $validator->errors()->add('parent_id', 'Lokasi tidak boleh diletakkan di bawah dirinya sendiri atau keturunannya.');
        }
    }

    /**
     * Changing the level of a record that already has children would leave
     * those children hanging under an invalid parent.
     */
    protected function validateLevelChange(Validator $validator): void
    {
        $location = $this->location();
        $newLevel = LocationLevel::from((string) $this->input('level'));

        if ($newLevel === $location->level) {
            return;
        }

        if ($location->children()->exists()) {
            $validator->errors()->add('level', 'Aras tidak boleh ditukar kerana lokasi ini mempunyai lokasi anak.');
        }
    }
}
