<?php

namespace App\Http\Requests\Admin;

use App\Enums\LocationLevel;
use App\Models\Location;
use App\Support\Hierarchy\HierarchyRules;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LocationStoreRequest extends FormRequest
{
    private ?Location $parentRecord = null;

    private bool $parentLoaded = false;

    /**
     * Access is enforced by the can: middleware on the route.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Field lengths follow the LOKASI entity in DRD §3.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:30'],
            'name' => ['required', 'string', 'max:150'],
            'level' => ['required', Rule::enum(LocationLevel::class)],
            'parent_id' => ['nullable', 'integer', 'exists:locations,id'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Hierarchy and scoped-uniqueness rules that plain rules cannot express.
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

                $this->validateParentLevel($validator);
                $this->validateParentIsActive($validator);
                $this->validateCodeIsUniqueWithinParent($validator);
            },
        ];
    }

    /**
     * The selected parent, loaded once per request. Returns null when the
     * submitted location sits at the root of the hierarchy.
     */
    protected function parentRecord(): ?Location
    {
        if (! $this->parentLoaded) {
            $parentId = $this->input('parent_id');

            $this->parentRecord = $parentId === null
                ? null
                : Location::query()->find($parentId);

            $this->parentLoaded = true;
        }

        return $this->parentRecord;
    }

    /**
     * FR-ORG-01: a parent must sit exactly one level above the child.
     */
    protected function validateParentLevel(Validator $validator): void
    {
        $level = LocationLevel::from((string) $this->input('level'));

        if (HierarchyRules::parentLevelIsValid($level, $this->parentRecord()?->level)) {
            return;
        }

        $validator->errors()->add(
            'parent_id',
            $level->isRoot()
                ? 'Aras kampus tidak boleh mempunyai lokasi induk.'
                : sprintf('Lokasi aras %s mesti berada di bawah satu %s.', $level->label(), $level->parentLevel()->label())
        );
    }

    /**
     * Deactivating a location cascades to its descendants, so an active
     * record under an inactive parent would silently undo that cascade and
     * leave, say, a bookable room inside a closed building. Keep the
     * invariant symmetric: a location may only be active if its parent is.
     */
    protected function validateParentIsActive(Validator $validator): void
    {
        $parent = $this->parentRecord();

        if ($parent === null || $parent->is_active || ! $this->boolean('is_active')) {
            return;
        }

        $validator->errors()->add(
            'is_active',
            'Lokasi ini tidak boleh aktif kerana lokasi induknya tidak aktif.'
        );
    }

    /**
     * DRD §4.1: the code is unique within the same parent. Root-level rows
     * are checked here too, because the database unique index treats every
     * null parent as distinct.
     */
    protected function validateCodeIsUniqueWithinParent(Validator $validator, ?int $ignoreId = null): void
    {
        $exists = Location::query()
            ->where('parent_id', $this->input('parent_id'))
            ->whereRaw('LOWER(code) = ?', [mb_strtolower((string) $this->input('code'))])
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();

        if ($exists) {
            $validator->errors()->add('code', 'Kod ini telah digunakan di bawah lokasi induk yang sama.');
        }
    }
}
