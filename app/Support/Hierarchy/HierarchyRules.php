<?php

namespace App\Support\Hierarchy;

use App\Enums\LocationLevel;

/**
 * Hierarchy validation rules for locations and organisation units.
 * No database access, so these rules can be unit tested without fixtures.
 *
 * The `createsCycle()` method is entity-agnostic and shared by both the location
 * and organisation unit form requests (FR-ORG-01, FR-ORG-02).
 *
 * The `parentLevelIsValid()` method applies to the location hierarchy only,
 * since it validates against enumerated LocationLevel values.
 */
class HierarchyRules
{
    /**
     * Whether a record at $level may hang under a parent at $parentLevel.
     * A null $parentLevel means "no parent selected".
     *
     * Location hierarchy only: validates using the LocationLevel enum.
     */
    public static function parentLevelIsValid(LocationLevel $level, ?LocationLevel $parentLevel): bool
    {
        return $level->parentLevel() === $parentLevel;
    }

    /**
     * Whether assigning $parentId to record $recordId would create a cycle.
     *
     * Shared by both location and organisation unit hierarchies.
     *
     * @param  array<int, int>  $descendantIds  every descendant of $recordId
     */
    public static function createsCycle(int $recordId, ?int $parentId, array $descendantIds): bool
    {
        if ($parentId === null) {
            return false;
        }

        if ($parentId === $recordId) {
            return true;
        }

        return in_array($parentId, $descendantIds, true);
    }
}
