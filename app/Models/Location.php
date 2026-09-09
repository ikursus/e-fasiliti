<?php

namespace App\Models;

use App\Enums\LocationLevel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'level',
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
            'level' => LocationLevel::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * Parent location in the physical hierarchy.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Direct child locations.
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Users with this location as their primary location.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'primary_location_id');
    }

    /**      * ICT assets currently placed at this location (M09, FR-AST-05).      */
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'location_id');
    }

    /**
     * Only records that are still in use (FR-ORG-04).
     *
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Children, and their children, all the way down.
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * Flat list of every descendant id below this record.
     *
     * Walks one level at a time rather than one node at a time, so the cost
     * is bounded by the depth of the hierarchy instead of growing with the
     * size of the subtree. Edit screens and the deactivation cascade both
     * call this on the request path.
     *
     * @return array<int, int>
     */
    public function descendantIds(): array
    {
        $ids = [];
        $frontier = [$this->id];

        // Cap the walk at the number of hierarchy levels, for the same
        // reason the ancestor walks above are capped: a corrupt cyclic row
        // must not spin this loop forever.
        $maxDepth = count(LocationLevel::cases());

        for ($depth = 0; $frontier !== [] && $depth < $maxDepth; $depth++) {
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

        // Cap the walk at the number of hierarchy levels: a legitimate
        // ancestor chain can never be longer, and a corrupt cyclic row
        // must not spin this loop forever.
        $maxSteps = count(LocationLevel::cases());

        for ($step = 0; $parent !== null && $step < $maxSteps; $step++) {
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

        // Cap the walk at the number of hierarchy levels: a legitimate
        // ancestor chain can never be longer, and a corrupt cyclic row
        // must degrade to a truncated path rather than exhaust memory.
        $maxSteps = count(LocationLevel::cases());

        for ($step = 0; $parent !== null && $step < $maxSteps; $step++) {
            array_unshift($names, $parent->name);
            $parent = $parent->parent;
        }

        return implode($separator, $names);
    }

    /**
     * Reasons this location may not be deleted (FR-ORG-03). An empty list
     * means deletion is safe. M04 rooms and M09 assets add a line here.
     *
     * @return array<int, string>
     */
    public function referenceSummary(): array
    {
        $reasons = [];

        if ($this->children()->exists()) {
            $reasons[] = 'lokasi anak';
        }

        if ($this->users()->exists()) {
            $reasons[] = 'pengguna';
        }

        if ($this->assets()->exists()) {
            $reasons[] = 'aset';
        }

        return $reasons;
    }
}
