<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganizationUnit extends Model
{
    use HasFactory;

    /**
     * FR-ORG-02: divisions and the units beneath them, and nothing deeper.
     */
    public const MAX_DEPTH = 2;

    protected $fillable = [
        'code',
        'name',
        'parent_id',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Parent organisational unit of this unit.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Direct child organisational units.
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Users assigned to this organisational unit.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'organization_unit_id');
    }

    /**
     * Only units that are still in use.
     *
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Top-level units are divisions; everything else is a unit (FR-ORG-02).
     */
    public function isDivision(): bool
    {
        return $this->parent_id === null;
    }

    /**
     * Flat list of every descendant id below this record.
     *
     * Walks one level at a time rather than one node at a time, so a division
     * with many units costs a fixed number of queries instead of one per unit.
     *
     * @return array<int, int>
     */
    public function descendantIds(): array
    {
        $ids = [];
        $frontier = [$this->id];

        // Validation forbids a third level, but a corrupt cyclic row must not
        // spin this loop forever, so cap the walk the same way the ancestor
        // walks below are capped.
        for ($depth = 0; $frontier !== [] && $depth < self::MAX_DEPTH; $depth++) {
            $frontier = static::query()
                ->whereIn('parent_id', $frontier)
                ->pluck('id')
                ->all();

            $ids = array_merge($ids, $frontier);
        }

        return $ids;
    }

    /**
     * Whether this record sits anywhere below the given ancestor.
     */
    public function isDescendantOf(self $ancestor): bool
    {
        $parent = $this->parent;

        // The organisation hierarchy is two levels deep, so a legitimate
        // ancestor chain is at most one step. The cap stops a corrupt
        // parent_id cycle from spinning this loop forever.
        for ($step = 0; $parent !== null && $step < self::MAX_DEPTH; $step++) {
            if ($parent->is($ancestor)) {
                return true;
            }

            $parent = $parent->parent;
        }

        return false;
    }

    /**
     * Names of every ancestor and this record, top to bottom.
     */
    public function fullPath(string $separator = ' / '): string
    {
        $names = [$this->name];
        $parent = $this->parent;

        // Capped for the same reason as isDescendantOf(): a cycle degrades
        // to a truncated path rather than exhausting memory.
        for ($step = 0; $parent !== null && $step < self::MAX_DEPTH; $step++) {
            array_unshift($names, $parent->name);
            $parent = $parent->parent;
        }

        return implode($separator, $names);
    }

    /**
     * Reasons this unit may not be deleted. Empty means deletion is safe.
     *
     * @return array<int, string>
     */
    public function referenceSummary(): array
    {
        $reasons = [];

        if ($this->children()->exists()) {
            $reasons[] = 'unit anak';
        }

        if ($this->users()->exists()) {
            $reasons[] = 'pengguna';
        }

        return $reasons;
    }
}
