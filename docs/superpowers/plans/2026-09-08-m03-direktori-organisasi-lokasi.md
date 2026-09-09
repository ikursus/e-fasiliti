# M03 Direktori Organisasi & Lokasi — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menyiapkan pengurusan hierarki lokasi empat aras dan hierarki organisasi dua aras, berserta pemilih lokasi boleh guna semula, memenuhi FR-ORG-01 hingga FR-ORG-05.

**Architecture:** Adjacency list dalam jadual `locations` dan `organization_units` yang sudah wujud. Peraturan hierarki dikuatkuasakan oleh satu kelas peraturan yang dikongsi Form Request cipta dan kemas kini. Nyahaktif ialah tindakan utama; padam disekat oleh satu kaedah semakan rujukan tunggal yang M04 dan M09 akan tambah kemudian. Paparan ialah Blade rekursif dengan Alpine untuk buka dan tutup.

**Tech Stack:** Laravel 13, PHP 8.4, Spatie laravel-permission v8, Blade, Tailwind v4, Alpine.js, PHPUnit 12. Ujian berjalan pada SQLite dalam ingatan; pengeluaran pada MySQL.

**Spec:** `docs/superpowers/specs/2026-09-08-m03-m01-asas-platform-design.md`

---

## Nota Persekitaran

Projek ini **bukan repositori git**. Langkah komit tidak disertakan. Sebagai gantinya, setiap tugasan berakhir dengan langkah semakan yang menjalankan pemformat dan suite ujian penuh.

Arahan yang digunakan berulang kali:

```bash
php artisan test
vendor/bin/pint --format agent
```

Ujian ciri memerlukan manifest Vite. Jika `php artisan test` gagal dengan ralat "Vite manifest not found", jalankan `npm run build` sekali sahaja.

Garis dasar sebelum bermula: **44 ujian lulus**. Setiap tugasan mesti mengekalkan angka itu dan menambah ujian baharunya sendiri.

> **Angka "Expected: N ujian lulus" pada setiap checkpoint adalah anggaran, bukan syarat lulus.**
> Jumlah sebenar sudah menyimpang kerana beberapa tugasan menambah ujian regresi yang tidak dirancang.
> Selepas Task 7 selesai, jumlah sebenar ialah **82 ujian lulus** (pelan menganggar 74).
> Syarat lulus sebenar bagi setiap checkpoint: tiada ujian gagal, dan jumlah bertambah dengan ujian baharu tugasan itu.

---

## Struktur Fail

| Fail | Tanggungjawab |
|---|---|
| `app/Enums/LocationLevel.php` | Aras lokasi yang sah, aras induk yang dibenarkan, label paparan |
| `app/Models/Location.php` (ubah) | Hubungan, skop, laluan penuh, semakan keturunan, semakan rujukan |
| `app/Models/OrganizationUnit.php` (ubah) | Sama, dua aras |
| `app/Support/Hierarchy/HierarchyRules.php` | Peraturan induk dan kitaran yang dikongsi kedua-dua entiti |
| `app/Services/Audit/AuditRecorder.php` | Rakaman audit umum dengan nilai sebelum dan selepas |
| `app/Http/Requests/Admin/LocationStoreRequest.php` | Pengesahan cipta lokasi |
| `app/Http/Requests/Admin/LocationUpdateRequest.php` | Pengesahan kemas kini lokasi |
| `app/Http/Requests/Admin/OrganizationUnitStoreRequest.php` | Pengesahan cipta unit |
| `app/Http/Requests/Admin/OrganizationUnitUpdateRequest.php` | Pengesahan kemas kini unit |
| `app/Http/Controllers/Admin/LocationController.php` | CRUD lokasi dan nyahaktif |
| `app/Http/Controllers/Admin/OrganizationUnitController.php` | CRUD unit dan nyahaktif |
| `resources/views/admin/locations/*` | Pokok, borang, nod rekursif |
| `resources/views/admin/organization-units/*` | Pokok, borang, nod rekursif |
| `resources/views/components/ui/location-picker.blade.php` | Pemilih berjujukan untuk M04 dan M09 |
| `database/factories/LocationFactory.php` | Data ujian lokasi |
| `database/factories/OrganizationUnitFactory.php` | Data ujian unit |

---

## Task 1: Enum aras lokasi

**Files:**
- Create: `app/Enums/LocationLevel.php`
- Test: `tests/Unit/Enums/LocationLevelTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/Enums/LocationLevelTest.php`:

```php
<?php

namespace Tests\Unit\Enums;

use App\Enums\LocationLevel;
use PHPUnit\Framework\TestCase;

class LocationLevelTest extends TestCase
{
    public function test_kampus_is_the_root_level_and_has_no_parent_level(): void
    {
        $this->assertTrue(LocationLevel::Kampus->isRoot());
        $this->assertNull(LocationLevel::Kampus->parentLevel());
    }

    public function test_each_non_root_level_has_the_level_directly_above_it(): void
    {
        $this->assertSame(LocationLevel::Kampus, LocationLevel::Bangunan->parentLevel());
        $this->assertSame(LocationLevel::Bangunan, LocationLevel::Tingkat->parentLevel());
        $this->assertSame(LocationLevel::Tingkat, LocationLevel::Ruang->parentLevel());
    }

    public function test_labels_are_in_malay_title_case(): void
    {
        $this->assertSame('Kampus', LocationLevel::Kampus->label());
        $this->assertSame('Bangunan', LocationLevel::Bangunan->label());
        $this->assertSame('Tingkat', LocationLevel::Tingkat->label());
        $this->assertSame('Ruang', LocationLevel::Ruang->label());
    }

    public function test_values_returns_the_four_levels_in_hierarchy_order(): void
    {
        $this->assertSame(
            ['kampus', 'bangunan', 'tingkat', 'ruang'],
            LocationLevel::values()
        );
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=LocationLevelTest`

Expected: FAIL with `Class "App\Enums\LocationLevel" not found`.

- [ ] **Step 3: Write minimal implementation**

Create `app/Enums/LocationLevel.php`:

```php
<?php

namespace App\Enums;

enum LocationLevel: string
{
    case Kampus = 'kampus';
    case Bangunan = 'bangunan';
    case Tingkat = 'tingkat';
    case Ruang = 'ruang';

    /**
     * Whether this level sits at the top of the hierarchy (FR-ORG-01).
     */
    public function isRoot(): bool
    {
        return $this === self::Kampus;
    }

    /**
     * The level a parent must have for a location at this level.
     */
    public function parentLevel(): ?self
    {
        return match ($this) {
            self::Kampus => null,
            self::Bangunan => self::Kampus,
            self::Tingkat => self::Bangunan,
            self::Ruang => self::Tingkat,
        };
    }

    /**
     * Display label shown in the administration screens.
     */
    public function label(): string
    {
        return match ($this) {
            self::Kampus => 'Kampus',
            self::Bangunan => 'Bangunan',
            self::Tingkat => 'Tingkat',
            self::Ruang => 'Ruang',
        };
    }

    /**
     * Backing values in hierarchy order, top to bottom.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $level) => $level->value, self::cases());
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=LocationLevelTest`

Expected: PASS, 4 tests.

- [ ] **Step 5: Checkpoint**

```bash
vendor/bin/pint --format agent
php artisan test
```

Expected: pemformat bersih, 48 ujian lulus.

---

## Task 2: Kilang model untuk lokasi dan unit organisasi

**Files:**
- Create: `database/factories/LocationFactory.php`
- Create: `database/factories/OrganizationUnitFactory.php`
- Modify: `app/Models/Location.php`
- Modify: `app/Models/OrganizationUnit.php`
- Test: `tests/Unit/Factories/HierarchyFactoryTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/Factories/HierarchyFactoryTest.php`:

```php
<?php

namespace Tests\Unit\Factories;

use App\Models\Location;
use App\Models\OrganizationUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HierarchyFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_location_factory_creates_an_active_campus_by_default(): void
    {
        $location = Location::factory()->create();

        $this->assertSame('kampus', $location->level->value);
        $this->assertNull($location->parent_id);
        $this->assertTrue($location->is_active);
    }

    public function test_location_factory_can_build_a_child_at_a_given_level(): void
    {
        $campus = Location::factory()->create();
        $building = Location::factory()->bangunan()->childOf($campus)->create();

        $this->assertSame('bangunan', $building->level->value);
        $this->assertSame($campus->id, $building->parent_id);
    }

    public function test_organization_unit_factory_creates_an_active_division(): void
    {
        $unit = OrganizationUnit::factory()->create();

        $this->assertNull($unit->parent_id);
        $this->assertTrue($unit->is_active);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=HierarchyFactoryTest`

Expected: FAIL with `Call to undefined method App\Models\Location::factory()`.

- [ ] **Step 3: Write minimal implementation**

Create `database/factories/LocationFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Enums\LocationLevel;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    protected $model = Location::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->bothify('LOK-####')),
            'name' => 'Kampus '.$this->faker->unique()->city(),
            'level' => LocationLevel::Kampus,
            'parent_id' => null,
            'is_active' => true,
        ];
    }

    public function bangunan(): static
    {
        return $this->state(fn () => ['level' => LocationLevel::Bangunan]);
    }

    public function tingkat(): static
    {
        return $this->state(fn () => ['level' => LocationLevel::Tingkat]);
    }

    public function ruang(): static
    {
        return $this->state(fn () => ['level' => LocationLevel::Ruang]);
    }

    public function childOf(Location $parent): static
    {
        return $this->state(fn () => ['parent_id' => $parent->id]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
```

Create `database/factories/OrganizationUnitFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\OrganizationUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrganizationUnit>
 */
class OrganizationUnitFactory extends Factory
{
    protected $model = OrganizationUnit::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->bothify('UNIT-####')),
            'name' => 'Bahagian '.$this->faker->unique()->word(),
            'parent_id' => null,
            'is_active' => true,
        ];
    }

    public function childOf(OrganizationUnit $parent): static
    {
        return $this->state(fn () => ['parent_id' => $parent->id]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
```

In `app/Models/Location.php`, add the trait import and use it, and cast the level to the enum. Replace the top of the class so it reads:

```php
<?php

namespace App\Models;

use App\Enums\LocationLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    /**
     * Allowed hierarchy levels for a location.
     */
    public const LEVELS = ['kampus', 'bangunan', 'tingkat', 'ruang'];

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
            'is_active' => 'boolean',
            'level' => LocationLevel::class,
        ];
    }
```

Leave the existing `parent()`, `children()` and `users()` methods unchanged.

In `app/Models/OrganizationUnit.php`, add the same trait:

```php
use Illuminate\Database\Eloquent\Factories\HasFactory;
```

and inside the class body, immediately after the opening brace:

```php
    use HasFactory;
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=HierarchyFactoryTest`

Expected: PASS, 3 tests.

- [ ] **Step 5: Checkpoint**

```bash
vendor/bin/pint --format agent
php artisan test
```

Expected: 51 ujian lulus.

---

## Task 3: Kod lokasi unik dalam induk yang sama

**Files:**
- Create: `database/migrations/2026_09_09_000001_change_location_code_unique_scope.php`
- Test: `tests/Unit/Models/LocationSchemaTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/Models/LocationSchemaTest.php`:

```php
<?php

namespace Tests\Unit\Models;

use App\Models\Location;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_same_code_is_allowed_under_two_different_parents(): void
    {
        $campusA = Location::factory()->create(['code' => 'KAMPUS-A']);
        $campusB = Location::factory()->create(['code' => 'KAMPUS-B']);

        Location::factory()->bangunan()->childOf($campusA)->create(['code' => 'BLOK-A']);
        $second = Location::factory()->bangunan()->childOf($campusB)->create(['code' => 'BLOK-A']);

        $this->assertSame('BLOK-A', $second->code);
        $this->assertSame(2, Location::where('code', 'BLOK-A')->count());
    }

    public function test_the_same_code_is_rejected_under_the_same_parent(): void
    {
        $campus = Location::factory()->create();
        Location::factory()->bangunan()->childOf($campus)->create(['code' => 'BLOK-A']);

        $this->expectException(QueryException::class);

        Location::factory()->bangunan()->childOf($campus)->create(['code' => 'BLOK-A']);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=LocationSchemaTest`

Expected: FAIL — ujian pertama melemparkan `QueryException` kerana indeks unik menyeluruh masih wujud.

- [ ] **Step 3: Write minimal implementation**

Create `database/migrations/2026_09_09_000001_change_location_code_unique_scope.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * DRD §4.1: a location code is unique within its parent, not globally.
     * Root-level uniqueness is enforced in the application layer, because
     * both MySQL and SQLite treat NULL parent values as distinct.
     */
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropUnique('locations_code_unique');
            $table->unique(['parent_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropUnique(['parent_id', 'code']);
            $table->unique('code');
        });
    }
};
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=LocationSchemaTest`

Expected: PASS, 2 tests.

- [ ] **Step 5: Checkpoint**

```bash
vendor/bin/pint --format agent
php artisan test
```

Expected: 53 ujian lulus.

---

## Task 4: Peraturan hierarki yang dikongsi

**Files:**
- Create: `app/Support/Hierarchy/HierarchyRules.php`
- Test: `tests/Unit/Support/HierarchyRulesTest.php`

Kelas ini tidak menyentuh pangkalan data. Ia menerima nilai dan koleksi, jadi boleh diuji unit sepenuhnya dan digunakan semula oleh Form Request kedua-dua entiti.

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/Support/HierarchyRulesTest.php`:

```php
<?php

namespace Tests\Unit\Support;

use App\Enums\LocationLevel;
use App\Support\Hierarchy\HierarchyRules;
use PHPUnit\Framework\TestCase;

class HierarchyRulesTest extends TestCase
{
    public function test_a_campus_must_not_have_a_parent(): void
    {
        $this->assertFalse(HierarchyRules::parentLevelIsValid(LocationLevel::Kampus, LocationLevel::Kampus));
        $this->assertTrue(HierarchyRules::parentLevelIsValid(LocationLevel::Kampus, null));
    }

    public function test_a_building_must_hang_under_a_campus(): void
    {
        $this->assertTrue(HierarchyRules::parentLevelIsValid(LocationLevel::Bangunan, LocationLevel::Kampus));
        $this->assertFalse(HierarchyRules::parentLevelIsValid(LocationLevel::Bangunan, LocationLevel::Tingkat));
        $this->assertFalse(HierarchyRules::parentLevelIsValid(LocationLevel::Bangunan, null));
    }

    public function test_a_room_must_hang_under_a_floor(): void
    {
        $this->assertTrue(HierarchyRules::parentLevelIsValid(LocationLevel::Ruang, LocationLevel::Tingkat));
        $this->assertFalse(HierarchyRules::parentLevelIsValid(LocationLevel::Ruang, LocationLevel::Bangunan));
    }

    public function test_a_record_may_not_become_its_own_parent(): void
    {
        $this->assertTrue(HierarchyRules::createsCycle(7, 7, []));
    }

    public function test_a_record_may_not_be_moved_under_its_own_descendant(): void
    {
        // 7 is the record; 8 and 9 are its descendants.
        $this->assertTrue(HierarchyRules::createsCycle(7, 9, [8, 9]));
        $this->assertFalse(HierarchyRules::createsCycle(7, 4, [8, 9]));
    }

    public function test_no_cycle_when_the_parent_is_cleared(): void
    {
        $this->assertFalse(HierarchyRules::createsCycle(7, null, [8, 9]));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=HierarchyRulesTest`

Expected: FAIL with `Class "App\Support\Hierarchy\HierarchyRules" not found`.

- [ ] **Step 3: Write minimal implementation**

Create `app/Support/Hierarchy/HierarchyRules.php`:

```php
<?php

namespace App\Support\Hierarchy;

use App\Enums\LocationLevel;

/**
 * Pure hierarchy rules shared by the location and organisation unit
 * form requests (FR-ORG-01, FR-ORG-02). No database access, so these
 * rules can be unit tested without fixtures.
 */
class HierarchyRules
{
    /**
     * Whether a record at $level may hang under a parent at $parentLevel.
     * A null $parentLevel means "no parent selected".
     */
    public static function parentLevelIsValid(LocationLevel $level, ?LocationLevel $parentLevel): bool
    {
        return $level->parentLevel() === $parentLevel;
    }

    /**
     * Whether assigning $parentId to record $recordId would create a cycle.
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
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=HierarchyRulesTest`

Expected: PASS, 6 tests.

- [ ] **Step 5: Checkpoint**

```bash
vendor/bin/pint --format agent
php artisan test
```

Expected: 59 ujian lulus.

---

## Task 5: Model lokasi — keturunan, laluan penuh, semakan rujukan

**Files:**
- Modify: `app/Models/Location.php`
- Test: `tests/Unit/Models/LocationTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/Models/LocationTest.php`:

```php
<?php

namespace Tests\Unit\Models;

use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_path_joins_every_ancestor_name(): void
    {
        $campus = Location::factory()->create(['name' => 'Kampus Induk']);
        $building = Location::factory()->bangunan()->childOf($campus)->create(['name' => 'Blok A']);
        $floor = Location::factory()->tingkat()->childOf($building)->create(['name' => 'Tingkat 3']);
        $room = Location::factory()->ruang()->childOf($floor)->create(['name' => 'Bilik Mesyuarat 1']);

        $this->assertSame(
            'Kampus Induk / Blok A / Tingkat 3 / Bilik Mesyuarat 1',
            $room->fullPath()
        );
    }

    public function test_descendant_ids_returns_every_level_below(): void
    {
        $campus = Location::factory()->create();
        $building = Location::factory()->bangunan()->childOf($campus)->create();
        $floor = Location::factory()->tingkat()->childOf($building)->create();
        $room = Location::factory()->ruang()->childOf($floor)->create();

        $ids = $campus->descendantIds();

        sort($ids);
        $expected = [$building->id, $floor->id, $room->id];
        sort($expected);

        $this->assertSame($expected, $ids);
    }

    public function test_is_descendant_of_detects_indirect_ancestry(): void
    {
        $campus = Location::factory()->create();
        $building = Location::factory()->bangunan()->childOf($campus)->create();
        $floor = Location::factory()->tingkat()->childOf($building)->create();

        $this->assertTrue($floor->isDescendantOf($campus));
        $this->assertFalse($campus->isDescendantOf($floor));
    }

    public function test_active_scope_excludes_deactivated_records(): void
    {
        Location::factory()->create();
        Location::factory()->inactive()->create();

        $this->assertSame(1, Location::query()->active()->count());
    }

    public function test_reference_summary_is_empty_for_an_unused_location(): void
    {
        $location = Location::factory()->create();

        $this->assertSame([], $location->referenceSummary());
    }

    public function test_reference_summary_reports_children(): void
    {
        $campus = Location::factory()->create();
        Location::factory()->bangunan()->childOf($campus)->create();

        $this->assertContains('lokasi anak', $campus->referenceSummary());
    }

    public function test_reference_summary_reports_users(): void
    {
        $location = Location::factory()->create();
        User::factory()->create(['primary_location_id' => $location->id]);

        $this->assertContains('pengguna', $location->referenceSummary());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=LocationTest`

Expected: FAIL with `Call to undefined method App\Models\Location::fullPath()`.

- [ ] **Step 3: Write minimal implementation**

In `app/Models/Location.php`, add these imports below the existing ones:

```php
use Illuminate\Database\Eloquent\Builder;
```

Then append these methods inside the class, after the existing `users()` method:

```php
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
     * Restrict a query to one hierarchy level.
     *
     * @param  Builder<self>  $query
     */
    public function scopeOfLevel(Builder $query, LocationLevel $level): void
    {
        $query->where('level', $level->value);
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
     * @return array<int, int>
     */
    public function descendantIds(): array
    {
        $ids = [];

        foreach ($this->children()->get(['id', 'parent_id']) as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $child->descendantIds());
        }

        return $ids;
    }

    /**
     * Whether this record sits anywhere below the given ancestor.
     */
    public function isDescendantOf(self $ancestor): bool
    {
        $parent = $this->parent;

        while ($parent !== null) {
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

        while ($parent !== null) {
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

        return $reasons;
    }
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=LocationTest`

Expected: PASS, 7 tests.

- [ ] **Step 5: Checkpoint**

```bash
vendor/bin/pint --format agent
php artisan test
```

Expected: 66 ujian lulus.

---

## Task 6: Model unit organisasi — corak sama, dua aras

**Files:**
- Modify: `app/Models/OrganizationUnit.php`
- Test: `tests/Unit/Models/OrganizationUnitTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/Models/OrganizationUnitTest.php`:

```php
<?php

namespace Tests\Unit\Models;

use App\Models\OrganizationUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_path_joins_division_and_unit(): void
    {
        $division = OrganizationUnit::factory()->create(['name' => 'Bahagian Pentadbiran']);
        $unit = OrganizationUnit::factory()->childOf($division)->create(['name' => 'Unit ICT']);

        $this->assertSame('Bahagian Pentadbiran / Unit ICT', $unit->fullPath());
    }

    public function test_descendant_ids_returns_child_units(): void
    {
        $division = OrganizationUnit::factory()->create();
        $unit = OrganizationUnit::factory()->childOf($division)->create();

        $this->assertSame([$unit->id], $division->descendantIds());
    }

    public function test_is_division_is_true_only_without_a_parent(): void
    {
        $division = OrganizationUnit::factory()->create();
        $unit = OrganizationUnit::factory()->childOf($division)->create();

        $this->assertTrue($division->isDivision());
        $this->assertFalse($unit->isDivision());
    }

    public function test_active_scope_excludes_deactivated_records(): void
    {
        OrganizationUnit::factory()->create();
        OrganizationUnit::factory()->inactive()->create();

        $this->assertSame(1, OrganizationUnit::query()->active()->count());
    }

    public function test_reference_summary_reports_children_and_users(): void
    {
        $division = OrganizationUnit::factory()->create();
        OrganizationUnit::factory()->childOf($division)->create();

        $withUser = OrganizationUnit::factory()->create();
        User::factory()->create(['organization_unit_id' => $withUser->id]);

        $this->assertContains('unit anak', $division->referenceSummary());
        $this->assertContains('pengguna', $withUser->referenceSummary());
        $this->assertSame([], OrganizationUnit::factory()->create()->referenceSummary());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=OrganizationUnitTest`

Expected: FAIL with `Call to undefined method App\Models\OrganizationUnit::fullPath()`.

- [ ] **Step 3: Write minimal implementation**

In `app/Models/OrganizationUnit.php`, add the import:

```php
use Illuminate\Database\Eloquent\Builder;
```

Then append these methods inside the class, after the existing `users()` method:

```php
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
     * @return array<int, int>
     */
    public function descendantIds(): array
    {
        $ids = [];

        foreach ($this->children()->get(['id', 'parent_id']) as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $child->descendantIds());
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
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=OrganizationUnitTest`

Expected: PASS, 5 tests.

- [ ] **Step 5: Checkpoint**

```bash
vendor/bin/pint --format agent
php artisan test
```

Expected: 71 ujian lulus.

---

## Task 7: Perakam audit umum

**Files:**
- Create: `app/Services/Audit/AuditRecorder.php`
- Test: `tests/Unit/Services/AuditRecorderTest.php`

Kelas ini memenuhi FR-ADM-08 dan FR-AUD-01, dan digunakan oleh setiap penulisan M03 dan M01. `LoginAuditLogger` sedia ada tidak diubah.

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/Services/AuditRecorderTest.php`:

```php
<?php

namespace Tests\Unit\Services;

use App\Models\AuditLog;
use App\Models\Location;
use App\Models\User;
use App\Services\Audit\AuditRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class AuditRecorderTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_the_actor_action_and_target(): void
    {
        $actor = User::factory()->create();
        $location = Location::factory()->create(['name' => 'Kampus Induk']);

        app(AuditRecorder::class)->record($actor, 'location.created', $location);

        $log = AuditLog::query()->latest('id')->first();

        $this->assertSame($actor->id, $log->user_id);
        $this->assertSame('location.created', $log->action);
        $this->assertSame('success', $log->status);
        $this->assertSame('location', $log->record_type);
        $this->assertSame($location->id, $log->record_id);
    }

    public function test_it_records_only_the_fields_that_changed(): void
    {
        $actor = User::factory()->create();
        $location = Location::factory()->create(['name' => 'Nama Lama', 'code' => 'KOD-1']);

        app(AuditRecorder::class)->record(
            $actor,
            'location.updated',
            $location,
            before: ['name' => 'Nama Lama', 'code' => 'KOD-1'],
            after: ['name' => 'Nama Baharu', 'code' => 'KOD-1'],
        );

        $log = AuditLog::query()->latest('id')->first();

        $this->assertSame(['name' => 'Nama Lama'], $log->metadata['before']);
        $this->assertSame(['name' => 'Nama Baharu'], $log->metadata['after']);
    }

    public function test_it_omits_the_change_payload_when_nothing_changed(): void
    {
        $actor = User::factory()->create();
        $location = Location::factory()->create();

        app(AuditRecorder::class)->record(
            $actor,
            'location.updated',
            $location,
            before: ['name' => 'Sama'],
            after: ['name' => 'Sama'],
        );

        $log = AuditLog::query()->latest('id')->first();

        $this->assertNull($log->metadata);
    }

    public function test_it_keeps_the_whole_snapshot_when_a_record_is_deleted(): void
    {
        $actor = User::factory()->create();
        $location = Location::factory()->create();

        app(AuditRecorder::class)->record(
            $actor,
            'location.deleted',
            $location,
            before: ['code' => 'KOD-1', 'name' => 'Bilik Mesyuarat', 'parent_id' => null],
        );

        $log = AuditLog::query()->latest('id')->first();

        $this->assertSame(
            ['code' => 'KOD-1', 'name' => 'Bilik Mesyuarat'],
            $log->metadata['before'],
        );
        $this->assertSame(['code' => null, 'name' => null], $log->metadata['after']);
    }

    public function test_it_pads_a_field_missing_from_one_side_with_null(): void
    {
        $actor = User::factory()->create();
        $location = Location::factory()->create();

        app(AuditRecorder::class)->record(
            $actor,
            'location.updated',
            $location,
            before: ['name' => 'Sama'],
            after: ['name' => 'Sama', 'note' => 'Baharu'],
        );

        $log = AuditLog::query()->latest('id')->first();

        $this->assertSame(['note' => null], $log->metadata['before']);
        $this->assertSame(['note' => 'Baharu'], $log->metadata['after']);
    }

    public function test_it_truncates_a_user_agent_that_exceeds_the_column_width(): void
    {
        $this->app->instance('request', Request::create(
            '/',
            'GET',
            server: ['HTTP_USER_AGENT' => str_repeat('a', 400)],
        ));

        $actor = User::factory()->create();
        $location = Location::factory()->create();

        app(AuditRecorder::class)->record($actor, 'location.created', $location);

        $log = AuditLog::query()->latest('id')->first();

        $this->assertSame(255, strlen($log->user_agent));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=AuditRecorderTest`

Expected: FAIL with `Target class [App\Services\Audit\AuditRecorder] does not exist`.

- [ ] **Step 3: Write minimal implementation**

Create `app/Services/Audit/AuditRecorder.php`:

```php
<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Records configuration and reference-data changes in the audit trail with
 * before and after values (FR-ADM-08, FR-AUD-01). Login events keep using
 * the dedicated LoginAuditLogger.
 */
class AuditRecorder
{
    public function __construct(private readonly Request $request) {}

    /**
     * Both snapshots must hold Eloquent-cast values, not raw request input,
     * because fields are compared strictly. Snapshot the "after" side from a
     * refreshed model so an integer column never reads back as a form string.
     *
     * Pass "before" alone to record a deletion, and "after" alone to record a
     * creation; the missing side is stored as null per field.
     *
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     */
    public function record(
        User $actor,
        string $action,
        Model $target,
        array $before = [],
        array $after = [],
    ): AuditLog {
        $changed = $this->changedFields($before, $after);

        $metadata = [];

        if ($changed !== []) {
            $metadata['before'] = $this->pick($before, $changed);
            $metadata['after'] = $this->pick($after, $changed);
        }

        return AuditLog::create([
            'user_id' => $actor->id,
            'action' => $action,
            'status' => 'success',
            'record_type' => $this->recordType($target),
            'record_id' => $target->getKey(),
            'ip_address' => $this->request->ip(),
            'user_agent' => Str::limit($this->request->userAgent() ?? '', 255, ''),
            'metadata' => $metadata === [] ? null : $metadata,
        ]);
    }

    /**
     * Snake-cased short class name, e.g. "location", "system_setting".
     */
    private function recordType(Model $target): string
    {
        return Str::snake(class_basename($target));
    }

    /**
     * Names of the fields whose value differs between the two snapshots.
     *
     * Both sides are scanned, so a field dropped from "after" counts as a
     * change and a delete-time snapshot is not silently discarded.
     *
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     * @return array<int, string>
     */
    private function changedFields(array $before, array $after): array
    {
        $changed = [];

        foreach (array_keys($before + $after) as $field) {
            if (($before[$field] ?? null) !== ($after[$field] ?? null)) {
                $changed[] = $field;
            }
        }

        return $changed;
    }

    /**
     * The given fields from a snapshot, with absent fields recorded as null so
     * the before and after payloads always share the same keys.
     *
     * @param  array<string, mixed>  $values
     * @param  array<int, string>  $fields
     * @return array<string, mixed>
     */
    private function pick(array $values, array $fields): array
    {
        $picked = [];

        foreach ($fields as $field) {
            $picked[$field] = $values[$field] ?? null;
        }

        return $picked;
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=AuditRecorderTest`

Expected: PASS, 6 tests.

- [ ] **Step 5: Checkpoint**

```bash
vendor/bin/pint --format agent
php artisan test
```

Expected: 82 ujian lulus.

---

## Task 8: Kebenaran M03 dalam seeder

**Files:**
- Modify: `database/seeders/RolesAndPermissionsSeeder.php`
- Test: `tests/Feature/Admin/PermissionSeedingTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Admin/PermissionSeedingTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionSeedingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_every_role_may_view_locations(): void
    {
        foreach (array_keys(RolesAndPermissionsSeeder::ROLES) as $slug) {
            $this->assertTrue(
                Role::findByName($slug)->hasPermissionTo('lokasi.lihat'),
                "Peranan {$slug} sepatutnya boleh melihat lokasi."
            );
        }
    }

    public function test_only_three_roles_may_manage_locations(): void
    {
        $allowed = ['pentadbir-sistem', 'pentadbir-fasiliti', 'pegawai-aset'];

        foreach (array_keys(RolesAndPermissionsSeeder::ROLES) as $slug) {
            $this->assertSame(
                in_array($slug, $allowed, true),
                Role::findByName($slug)->hasPermissionTo('lokasi.kemaskini'),
                "Kebenaran lokasi.kemaskini salah bagi peranan {$slug}."
            );
        }
    }

    public function test_only_the_system_administrator_may_delete_locations(): void
    {
        $this->assertTrue(Role::findByName('pentadbir-sistem')->hasPermissionTo('lokasi.padam'));
        $this->assertFalse(Role::findByName('pentadbir-fasiliti')->hasPermissionTo('lokasi.padam'));
        $this->assertFalse(Role::findByName('pegawai-aset')->hasPermissionTo('lokasi.padam'));
    }

    public function test_only_the_system_administrator_may_manage_organisation_units(): void
    {
        $this->assertTrue(Role::findByName('pentadbir-sistem')->hasPermissionTo('unit-organisasi.kemaskini'));
        $this->assertFalse(Role::findByName('pentadbir-fasiliti')->hasPermissionTo('unit-organisasi.kemaskini'));
        $this->assertTrue(Role::findByName('kakitangan')->hasPermissionTo('unit-organisasi.lihat'));
    }

    public function test_four_roles_may_view_settings_but_only_one_may_change_them(): void
    {
        foreach (['pentadbir-sistem', 'pentadbir-fasiliti', 'penyelia-ict', 'pegawai-aset'] as $slug) {
            $this->assertTrue(Role::findByName($slug)->hasPermissionTo('tetapan.lihat'));
        }

        $this->assertTrue(Role::findByName('pentadbir-sistem')->hasPermissionTo('tetapan.kemaskini'));
        $this->assertFalse(Role::findByName('pentadbir-fasiliti')->hasPermissionTo('tetapan.kemaskini'));
        $this->assertFalse(Role::findByName('kakitangan')->hasPermissionTo('tetapan.lihat'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=PermissionSeedingTest`

Expected: FAIL with `Spatie\Permission\Exceptions\PermissionDoesNotExist: There is no permission named 'lokasi.lihat'`.

- [ ] **Step 3: Write minimal implementation**

In `database/seeders/RolesAndPermissionsSeeder.php`, add the M03 entries to the `PERMISSIONS` constant, immediately after the `'audit.lihat'` line:

```php
        // M03 — Direktori Organisasi & Lokasi
        'lokasi.lihat',
        'lokasi.cipta',
        'lokasi.kemaskini',
        'lokasi.padam',
        'unit-organisasi.lihat',
        'unit-organisasi.cipta',
        'unit-organisasi.kemaskini',
        'unit-organisasi.padam',
```

Then replace the whole `ROLE_PERMISSIONS` constant with:

```php
    /**
     * Permission grants per role, following the module/role matrix in
     * docs-claude/01-modules.md §3.
     *
     * @var array<string, array<int, string>>
     */
    public const ROLE_PERMISSIONS = [
        'kakitangan' => [
            'laporan.lihat',
            'lokasi.lihat',
            'unit-organisasi.lihat',
        ],
        'setiausaha' => [
            'laporan.lihat',
            'lokasi.lihat',
            'unit-organisasi.lihat',
        ],
        'pelulus' => [
            'laporan.lihat',
            'lokasi.lihat',
            'unit-organisasi.lihat',
        ],
        'pentadbir-fasiliti' => [
            'laporan.lihat',
            'tetapan.lihat',
            'lokasi.lihat',
            'lokasi.cipta',
            'lokasi.kemaskini',
            'unit-organisasi.lihat',
        ],
        'juruteknik' => [
            'laporan.lihat',
            'lokasi.lihat',
            'unit-organisasi.lihat',
        ],
        'penyelia-ict' => [
            'laporan.lihat',
            'audit.lihat',
            'tetapan.lihat',
            'lokasi.lihat',
            'unit-organisasi.lihat',
        ],
        'pegawai-aset' => [
            'laporan.lihat',
            'audit.lihat',
            'tetapan.lihat',
            'lokasi.lihat',
            'lokasi.cipta',
            'lokasi.kemaskini',
            'unit-organisasi.lihat',
        ],
        'pentadbir-sistem' => self::PERMISSIONS,
    ];
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=PermissionSeedingTest`

Expected: PASS, 5 tests.

- [ ] **Step 5: Checkpoint**

```bash
vendor/bin/pint --format agent
php artisan test
```

Expected: 79 ujian lulus.

---

## Task 9: Form Request lokasi

**Files:**
- Create: `app/Http/Requests/Admin/LocationStoreRequest.php`
- Create: `app/Http/Requests/Admin/LocationUpdateRequest.php`
- Test: `tests/Feature/Admin/LocationValidationTest.php`

Ujian pengesahan ditulis sebagai ujian ciri kerana ia perlu melalui laluan sebenar. Laluan dan pengawal belum wujud, jadi Task 10 menyiapkan pengawal; ujian dalam tugasan ini akan gagal sehingga Task 10 selesai. **Laksanakan Task 9 dan Task 10 berturut-turut tanpa henti**, dan jalankan ujian ini pada penghujung Task 10.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Admin/LocationValidationTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\Location;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('pentadbir-sistem');
    }

    public function test_a_campus_may_not_have_a_parent(): void
    {
        $campus = Location::factory()->create();

        $this->actingAs($this->admin)
            ->from(route('admin.locations.create'))
            ->post(route('admin.locations.store'), [
                'code' => 'KAMPUS-2',
                'name' => 'Kampus Kedua',
                'level' => 'kampus',
                'parent_id' => $campus->id,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('parent_id');
    }

    public function test_a_building_requires_a_campus_parent(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.locations.create'))
            ->post(route('admin.locations.store'), [
                'code' => 'BLOK-A',
                'name' => 'Blok A',
                'level' => 'bangunan',
                'parent_id' => null,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('parent_id');
    }

    public function test_a_room_may_not_hang_under_a_building(): void
    {
        $campus = Location::factory()->create();
        $building = Location::factory()->bangunan()->childOf($campus)->create();

        $this->actingAs($this->admin)
            ->from(route('admin.locations.create'))
            ->post(route('admin.locations.store'), [
                'code' => 'BILIK-1',
                'name' => 'Bilik Mesyuarat 1',
                'level' => 'ruang',
                'parent_id' => $building->id,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('parent_id');
    }

    public function test_the_code_must_be_unique_within_the_same_parent(): void
    {
        $campus = Location::factory()->create();
        Location::factory()->bangunan()->childOf($campus)->create(['code' => 'BLOK-A']);

        $this->actingAs($this->admin)
            ->from(route('admin.locations.create'))
            ->post(route('admin.locations.store'), [
                'code' => 'BLOK-A',
                'name' => 'Blok A Duplikat',
                'level' => 'bangunan',
                'parent_id' => $campus->id,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('code');
    }

    public function test_the_same_code_is_accepted_under_a_different_parent(): void
    {
        $campusA = Location::factory()->create();
        $campusB = Location::factory()->create();
        Location::factory()->bangunan()->childOf($campusA)->create(['code' => 'BLOK-A']);

        $this->actingAs($this->admin)
            ->post(route('admin.locations.store'), [
                'code' => 'BLOK-A',
                'name' => 'Blok A Kampus B',
                'level' => 'bangunan',
                'parent_id' => $campusB->id,
                'is_active' => 1,
            ])
            ->assertSessionHasNoErrors();
    }

    public function test_two_campuses_may_not_share_a_code(): void
    {
        Location::factory()->create(['code' => 'KAMPUS-1']);

        $this->actingAs($this->admin)
            ->from(route('admin.locations.create'))
            ->post(route('admin.locations.store'), [
                'code' => 'KAMPUS-1',
                'name' => 'Kampus Duplikat',
                'level' => 'kampus',
                'parent_id' => null,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('code');
    }

    public function test_a_location_may_not_become_its_own_parent(): void
    {
        $campus = Location::factory()->create();
        $building = Location::factory()->bangunan()->childOf($campus)->create();

        $this->actingAs($this->admin)
            ->from(route('admin.locations.edit', $building))
            ->put(route('admin.locations.update', $building), [
                'code' => $building->code,
                'name' => $building->name,
                'level' => 'bangunan',
                'parent_id' => $building->id,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('parent_id');
    }

    public function test_the_level_may_not_change_while_children_exist(): void
    {
        $campus = Location::factory()->create();
        $building = Location::factory()->bangunan()->childOf($campus)->create();
        Location::factory()->tingkat()->childOf($building)->create();

        $this->actingAs($this->admin)
            ->from(route('admin.locations.edit', $building))
            ->put(route('admin.locations.update', $building), [
                'code' => $building->code,
                'name' => $building->name,
                'level' => 'tingkat',
                'parent_id' => $campus->id,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('level');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=LocationValidationTest`

Expected: FAIL with `Route [admin.locations.create] not defined.`

- [ ] **Step 3: Write the store request**

Create `app/Http/Requests/Admin/LocationStoreRequest.php`:

```php
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
    public function authorize(): bool
    {
        return true;
    }

    /**
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
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $this->validateParentLevel($validator);
                $this->validateCodeIsUniqueWithinParent($validator);
            },
        ];
    }

    /**
     * FR-ORG-01: a parent must sit exactly one level above the child.
     */
    protected function validateParentLevel(Validator $validator): void
    {
        $level = LocationLevel::from((string) $this->input('level'));
        $parentId = $this->input('parent_id');

        $parentLevel = $parentId === null
            ? null
            : Location::query()->whereKey($parentId)->value('level');

        if ($parentLevel !== null && ! $parentLevel instanceof LocationLevel) {
            $parentLevel = LocationLevel::from((string) $parentLevel);
        }

        if (HierarchyRules::parentLevelIsValid($level, $parentLevel)) {
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
     * DRD §4.1: the code is unique within the same parent.
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
```

- [ ] **Step 4: Write the update request**

Create `app/Http/Requests/Admin/LocationUpdateRequest.php`:

```php
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

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $this->validateParentLevel($validator);
                $this->validateCodeIsUniqueWithinParent($validator, $this->location()->id);
                $this->validateNoCycle($validator);
                $this->validateLevelChange($validator);
            },
        ];
    }

    /**
     * FR-ORG-01: a location may not sit under itself or its own descendant.
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
```

- [ ] **Step 5: Run test to confirm the route error is still the only blocker**

Run: `php artisan test --filter=LocationValidationTest`

Expected: masih FAIL dengan `Route [admin.locations.create] not defined.` Ini dijangka. Teruskan terus ke Task 10.

---

## Task 10: Pengawal dan paparan lokasi

**Files:**
- Create: `app/Http/Controllers/Admin/LocationController.php`
- Create: `resources/views/admin/locations/index.blade.php`
- Create: `resources/views/admin/locations/_node.blade.php`
- Create: `resources/views/admin/locations/_form.blade.php`
- Create: `resources/views/admin/locations/create.blade.php`
- Create: `resources/views/admin/locations/edit.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Admin/LocationManagementTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Admin/LocationManagementTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\Location;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('pentadbir-sistem');
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('admin.locations.index'))->assertRedirect(route('login'));
    }

    public function test_every_role_may_view_the_location_tree(): void
    {
        foreach (array_keys(RolesAndPermissionsSeeder::ROLES) as $role) {
            $this->actingAs($this->userWithRole($role))
                ->get(route('admin.locations.index'))
                ->assertOk();
        }
    }

    public function test_the_tree_shows_children_under_their_parent(): void
    {
        $campus = Location::factory()->create(['name' => 'Kampus Induk']);
        Location::factory()->bangunan()->childOf($campus)->create(['name' => 'Blok A']);

        $this->actingAs($this->admin)
            ->get(route('admin.locations.index'))
            ->assertOk()
            ->assertSee('Kampus Induk')
            ->assertSee('Blok A');
    }

    public function test_search_filters_by_name_or_code(): void
    {
        Location::factory()->create(['name' => 'Kampus Induk', 'code' => 'KI-01']);
        Location::factory()->create(['name' => 'Kampus Cawangan', 'code' => 'KC-01']);

        $this->actingAs($this->admin)
            ->get(route('admin.locations.index', ['search' => 'Cawangan']))
            ->assertOk()
            ->assertSee('Kampus Cawangan')
            ->assertDontSee('Kampus Induk');
    }

    public function test_a_technician_may_not_open_the_create_form(): void
    {
        $this->actingAs($this->userWithRole('juruteknik'))
            ->get(route('admin.locations.create'))
            ->assertForbidden();
    }

    public function test_a_facility_administrator_may_create_a_location(): void
    {
        $this->actingAs($this->userWithRole('pentadbir-fasiliti'))
            ->post(route('admin.locations.store'), [
                'code' => 'KAMPUS-BARU',
                'name' => 'Kampus Baharu',
                'level' => 'kampus',
                'parent_id' => null,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.locations.index'));

        $this->assertDatabaseHas('locations', ['code' => 'KAMPUS-BARU', 'is_active' => true]);
    }

    public function test_creating_a_location_writes_an_audit_entry(): void
    {
        $this->actingAs($this->admin)->post(route('admin.locations.store'), [
            'code' => 'KAMPUS-AUDIT',
            'name' => 'Kampus Audit',
            'level' => 'kampus',
            'parent_id' => null,
            'is_active' => 1,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'location.created',
            'record_type' => 'location',
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_updating_a_location_records_the_previous_name(): void
    {
        $location = Location::factory()->create(['name' => 'Nama Lama', 'code' => 'KOD-A']);

        $this->actingAs($this->admin)->put(route('admin.locations.update', $location), [
            'code' => 'KOD-A',
            'name' => 'Nama Baharu',
            'level' => 'kampus',
            'parent_id' => null,
            'is_active' => 1,
        ])->assertRedirect(route('admin.locations.index'));

        $this->assertDatabaseHas('locations', ['id' => $location->id, 'name' => 'Nama Baharu']);

        $log = AuditLog::query()->where('action', 'location.updated')->latest('id')->first();
        $this->assertSame('Nama Lama', $log->metadata['before']['name']);
        $this->assertSame('Nama Baharu', $log->metadata['after']['name']);
    }

    public function test_renaming_a_location_keeps_the_same_record_for_history(): void
    {
        $location = Location::factory()->create(['name' => 'Nama Lama']);

        $this->actingAs($this->admin)->put(route('admin.locations.update', $location), [
            'code' => $location->code,
            'name' => 'Nama Baharu',
            'level' => 'kampus',
            'parent_id' => null,
            'is_active' => 1,
        ]);

        $this->assertSame($location->id, Location::where('name', 'Nama Baharu')->first()->id);
    }

    public function test_deactivating_a_parent_deactivates_its_descendants(): void
    {
        $campus = Location::factory()->create();
        $building = Location::factory()->bangunan()->childOf($campus)->create();
        $floor = Location::factory()->tingkat()->childOf($building)->create();

        $this->actingAs($this->admin)
            ->patch(route('admin.locations.toggle', $campus))
            ->assertRedirect(route('admin.locations.index'));

        $this->assertFalse($campus->fresh()->is_active);
        $this->assertFalse($building->fresh()->is_active);
        $this->assertFalse($floor->fresh()->is_active);
    }

    public function test_reactivating_a_location_does_not_touch_its_descendants(): void
    {
        $campus = Location::factory()->inactive()->create();
        $building = Location::factory()->bangunan()->inactive()->childOf($campus)->create();

        $this->actingAs($this->admin)->patch(route('admin.locations.toggle', $campus));

        $this->assertTrue($campus->fresh()->is_active);
        $this->assertFalse($building->fresh()->is_active);
    }

    public function test_a_facility_administrator_may_not_delete(): void
    {
        $location = Location::factory()->create();

        $this->actingAs($this->userWithRole('pentadbir-fasiliti'))
            ->delete(route('admin.locations.destroy', $location))
            ->assertForbidden();
    }

    public function test_deletion_is_blocked_when_the_location_has_children(): void
    {
        $campus = Location::factory()->create();
        Location::factory()->bangunan()->childOf($campus)->create();

        $this->actingAs($this->admin)
            ->from(route('admin.locations.index'))
            ->delete(route('admin.locations.destroy', $campus))
            ->assertRedirect(route('admin.locations.index'))
            ->assertSessionHasErrors('id');

        $this->assertDatabaseHas('locations', ['id' => $campus->id]);
    }

    public function test_deletion_is_blocked_when_a_user_references_the_location(): void
    {
        $location = Location::factory()->create();
        User::factory()->create(['primary_location_id' => $location->id]);

        $this->actingAs($this->admin)
            ->from(route('admin.locations.index'))
            ->delete(route('admin.locations.destroy', $location))
            ->assertSessionHasErrors('id');

        $this->assertDatabaseHas('locations', ['id' => $location->id]);
    }

    public function test_an_unreferenced_location_is_deleted_and_audited(): void
    {
        $location = Location::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.locations.destroy', $location))
            ->assertRedirect(route('admin.locations.index'));

        $this->assertDatabaseMissing('locations', ['id' => $location->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'location.deleted']);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=LocationManagementTest`

Expected: FAIL with `Route [admin.locations.index] not defined.`

- [ ] **Step 3: Write the controller**

Create `app/Http/Controllers/Admin/LocationController.php`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LocationLevel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LocationStoreRequest;
use App\Http\Requests\Admin\LocationUpdateRequest;
use App\Models\Location;
use App\Models\User;
use App\Services\Audit\AuditRecorder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * M03 — physical location hierarchy (FR-ORG-01, FR-ORG-03, FR-ORG-04).
 */
class LocationController extends Controller
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * Render the location tree, or a flat result list when searching.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));

        if ($search !== '') {
            $matches = Location::query()
                ->with('parent')
                ->where(fn ($query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%"))
                ->orderBy('level')
                ->orderBy('name')
                ->get();

            return view('admin.locations.index', [
                'roots' => collect(),
                'matches' => $matches,
                'search' => $search,
            ]);
        }

        $roots = Location::query()
            ->whereNull('parent_id')
            ->with('descendants')
            ->orderBy('name')
            ->get();

        return view('admin.locations.index', [
            'roots' => $roots,
            'matches' => null,
            'search' => '',
        ]);
    }

    public function create(): View
    {
        return view('admin.locations.create', [
            'location' => new Location(['is_active' => true, 'level' => LocationLevel::Kampus]),
            'parents' => $this->parentOptions(),
            'levels' => LocationLevel::cases(),
        ]);
    }

    public function store(LocationStoreRequest $request): RedirectResponse
    {
        $location = Location::create([
            'code' => $request->string('code')->upper()->value(),
            'name' => $request->string('name')->value(),
            'level' => $request->string('level')->value(),
            'parent_id' => $request->input('parent_id'),
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->audit->record($this->actor(), 'location.created', $location, after: $location->only([
            'code', 'name', 'parent_id',
        ]));

        return to_route('admin.locations.index')
            ->with('status', 'Lokasi telah dicipta.');
    }

    public function edit(Location $location): View
    {
        return view('admin.locations.edit', [
            'location' => $location,
            'parents' => $this->parentOptions($location),
            'levels' => LocationLevel::cases(),
        ]);
    }

    public function update(LocationUpdateRequest $request, Location $location): RedirectResponse
    {
        $before = $location->only(['code', 'name', 'level', 'parent_id', 'is_active']);

        $location->update([
            'code' => $request->string('code')->upper()->value(),
            'name' => $request->string('name')->value(),
            'level' => $request->string('level')->value(),
            'parent_id' => $request->input('parent_id'),
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->audit->record(
            $this->actor(),
            'location.updated',
            $location,
            before: $this->normalise($before),
            after: $this->normalise($location->refresh()->only(['code', 'name', 'level', 'parent_id', 'is_active'])),
        );

        return to_route('admin.locations.index')
            ->with('status', 'Lokasi telah dikemas kini.');
    }

    /**
     * Deactivate or reactivate. Deactivating cascades to descendants so a
     * closed building does not leave bookable rooms behind (FR-ORG-04).
     */
    public function toggle(Location $location): RedirectResponse
    {
        $activating = ! $location->is_active;

        DB::transaction(function () use ($location, $activating): void {
            $location->update(['is_active' => $activating]);

            if (! $activating) {
                Location::query()
                    ->whereIn('id', $location->descendantIds())
                    ->update(['is_active' => false]);
            }
        });

        $this->audit->record(
            $this->actor(),
            $activating ? 'location.activated' : 'location.deactivated',
            $location,
            before: ['is_active' => ! $activating],
            after: ['is_active' => $activating],
        );

        return to_route('admin.locations.index')->with(
            'status',
            $activating ? 'Lokasi telah diaktifkan semula.' : 'Lokasi dan keturunannya telah dinyahaktifkan.'
        );
    }

    /**
     * Hard delete, only for records nothing points at (FR-ORG-03).
     */
    public function destroy(Location $location): RedirectResponse
    {
        $reasons = $location->referenceSummary();

        if ($reasons !== []) {
            return back()->withErrors([
                'id' => 'Lokasi ini tidak boleh dipadam kerana masih dirujuk oleh: '.implode(', ', $reasons).'. Nyahaktifkan lokasi sebagai gantinya.',
            ]);
        }

        $snapshot = $location->only(['code', 'name', 'level', 'parent_id']);
        $location->delete();

        $this->audit->record($this->actor(), 'location.deleted', $location, before: $this->normalise($snapshot));

        return to_route('admin.locations.index')
            ->with('status', 'Lokasi telah dipadam.');
    }

    /**
     * Every location that may act as a parent, with its full path label.
     *
     * @return \Illuminate\Support\Collection<int, array{id: int, label: string, level: string}>
     */
    private function parentOptions(?Location $exclude = null): \Illuminate\Support\Collection
    {
        $excluded = $exclude === null ? [] : array_merge([$exclude->id], $exclude->descendantIds());

        return Location::query()
            ->with('parent')
            ->whereNotIn('id', $excluded)
            ->orderBy('name')
            ->get()
            ->map(fn (Location $location) => [
                'id' => $location->id,
                'label' => $location->fullPath(),
                'level' => $location->level->value,
            ])
            ->values();
    }

    /**
     * Cast enum and boolean values so audit payloads stay JSON-comparable.
     *
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function normalise(array $values): array
    {
        return array_map(
            fn ($value) => $value instanceof LocationLevel ? $value->value : $value,
            $values
        );
    }

    private function actor(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
```

- [ ] **Step 4: Register the routes**

In `routes/web.php`, add the import beside the existing controller imports:

```php
use App\Http\Controllers\Admin\LocationController;
```

Then, inside the `Route::middleware('auth')->group(...)` closure and **before** the existing `Route::prefix('admin')->middleware('role:pentadbir-sistem')` group, add:

```php
    /**
     * Directory administration (M03). Shared by three roles, so access is
     * granted per permission rather than per role.
     */
    Route::prefix('admin')
        ->name('admin.')
        ->group(function (): void {
            Route::get('locations', [LocationController::class, 'index'])
                ->middleware('can:lokasi.lihat')
                ->name('locations.index');

            Route::get('locations/create', [LocationController::class, 'create'])
                ->middleware('can:lokasi.cipta')
                ->name('locations.create');

            Route::post('locations', [LocationController::class, 'store'])
                ->middleware('can:lokasi.cipta')
                ->name('locations.store');

            Route::get('locations/{location}/edit', [LocationController::class, 'edit'])
                ->middleware('can:lokasi.kemaskini')
                ->name('locations.edit');

            Route::put('locations/{location}', [LocationController::class, 'update'])
                ->middleware('can:lokasi.kemaskini')
                ->name('locations.update');

            Route::patch('locations/{location}/toggle', [LocationController::class, 'toggle'])
                ->middleware('can:lokasi.kemaskini')
                ->name('locations.toggle');

            Route::delete('locations/{location}', [LocationController::class, 'destroy'])
                ->middleware('can:lokasi.padam')
                ->name('locations.destroy');
        });
```

- [ ] **Step 5: Write the tree node partial**

Create `resources/views/admin/locations/_node.blade.php`:

```blade
@php($canManage = auth()->user()->can('lokasi.kemaskini'))
@php($canDelete = auth()->user()->can('lokasi.padam'))

<li x-data="{ open: true }" class="border-l border-slate-200 pl-4">
    <div class="flex flex-wrap items-center gap-2 py-2">
        @if ($node->descendants->isNotEmpty())
            <button type="button" @click="open = !open"
                    class="flex h-5 w-5 items-center justify-center rounded text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                    :aria-expanded="open ? 'true' : 'false'"
                    aria-label="Buka atau tutup cabang">
                <span x-show="open">&minus;</span>
                <span x-show="!open" x-cloak>+</span>
            </button>
        @else
            <span class="inline-block h-5 w-5"></span>
        @endif

        <span class="font-medium text-slate-900">{{ $node->name }}</span>
        <span class="rounded bg-slate-100 px-2 py-0.5 font-mono text-xs text-slate-600">{{ $node->code }}</span>
        <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700">{{ $node->level->label() }}</span>

        @unless ($node->is_active)
            <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">Tidak aktif</span>
        @endunless

        @if ($canManage)
            <span class="ml-auto flex items-center gap-3 text-sm">
                <a href="{{ route('admin.locations.edit', $node) }}" class="font-medium text-indigo-600 hover:underline">Edit</a>

                <form method="POST" action="{{ route('admin.locations.toggle', $node) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="font-medium text-slate-600 hover:underline">
                        {{ $node->is_active ? 'Nyahaktif' : 'Aktifkan' }}
                    </button>
                </form>

                @if ($canDelete)
                    <form method="POST" action="{{ route('admin.locations.destroy', $node) }}"
                          onsubmit="return confirm('Padam lokasi ini secara kekal?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="font-medium text-rose-600 hover:underline">Padam</button>
                    </form>
                @endif
            </span>
        @endif
    </div>

    @if ($node->descendants->isNotEmpty())
        <ul x-show="open" x-cloak>
            @foreach ($node->descendants->sortBy('name') as $child)
                @include('admin.locations._node', ['node' => $child])
            @endforeach
        </ul>
    @endif
</li>
```

**Why `descendants` and not `children` here.** Eloquent eager loading is keyed by relation name. The controller calls `->with('descendants')`, which fills the `descendants` relation at every level and leaves `children` unloaded. Reading `$node->children` in this partial would still render correctly, but it would lazy-load one query per node, which is exactly the N+1 the recursive eager load exists to prevent. Both relations run the same underlying query, so `descendants` is the correct name to read here.

- [ ] **Step 6: Write the index view**

Create `resources/views/admin/locations/index.blade.php`:

```blade
@extends('layouts.app')

@section('title', 'Lokasi · Pentadbiran')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Direktori Lokasi</h2>
            <p class="mt-1 text-sm text-slate-500">Hierarki kampus, bangunan, tingkat dan ruang.</p>
        </div>
        @can('lokasi.cipta')
            <a href="{{ route('admin.locations.create') }}"
               class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                + Lokasi Baharu
            </a>
        @endcan
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @error('id')
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $message }}</div>
    @enderror

    <form method="GET" action="{{ route('admin.locations.index') }}" class="mb-4 flex gap-2">
        <input type="search" name="search" value="{{ $search }}" placeholder="Cari nama atau kod"
               class="w-full max-w-sm rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <button type="submit" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
            Cari
        </button>
        @if ($search !== '')
            <a href="{{ route('admin.locations.index') }}" class="px-3 py-2 text-sm font-medium text-slate-500 hover:underline">Kosongkan</a>
        @endif
    </form>

    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        @if ($matches !== null)
            @forelse ($matches as $match)
                <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 py-2 last:border-0">
                    <span class="font-medium text-slate-900">{{ $match->name }}</span>
                    <span class="rounded bg-slate-100 px-2 py-0.5 font-mono text-xs text-slate-600">{{ $match->code }}</span>
                    <span class="text-xs text-slate-500">{{ $match->fullPath() }}</span>
                    @can('lokasi.kemaskini')
                        <a href="{{ route('admin.locations.edit', $match) }}" class="ml-auto text-sm font-medium text-indigo-600 hover:underline">Edit</a>
                    @endcan
                </div>
            @empty
                <p class="py-6 text-center text-sm text-slate-500">Tiada lokasi sepadan dengan carian.</p>
            @endforelse
        @else
            <ul>
                @forelse ($roots as $root)
                    @include('admin.locations._node', ['node' => $root])
                @empty
                    <li class="py-6 text-center text-sm text-slate-500">Tiada lokasi didaftarkan lagi.</li>
                @endforelse
            </ul>
        @endif
    </div>
@endsection
```

- [ ] **Step 7: Write the shared form partial and the two form pages**

Create `resources/views/admin/locations/_form.blade.php`:

```blade
@csrf

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="code" class="block text-sm font-medium text-slate-700">Kod</label>
        <input type="text" id="code" name="code" value="{{ old('code', $location->code) }}" required maxlength="30"
               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('code')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Nama</label>
        <input type="text" id="name" name="name" value="{{ old('name', $location->name) }}" required maxlength="150"
               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="level" class="block text-sm font-medium text-slate-700">Aras</label>
        <select id="level" name="level" required
                class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @foreach ($levels as $level)
                <option value="{{ $level->value }}"
                    @selected(old('level', $location->level?->value) === $level->value)>{{ $level->label() }}</option>
            @endforeach
        </select>
        @error('level')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="parent_id" class="block text-sm font-medium text-slate-700">Lokasi induk</label>
        <select id="parent_id" name="parent_id"
                class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Tiada (aras kampus)</option>
            @foreach ($parents as $parent)
                <option value="{{ $parent['id'] }}" @selected((string) old('parent_id', $location->parent_id) === (string) $parent['id'])>
                    {{ $parent['label'] }}
                </option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-slate-500">Induk mesti berada tepat satu aras di atas aras yang dipilih.</p>
        @error('parent_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
</div>

<label class="mt-4 flex items-center gap-2 text-sm text-slate-700">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $location->is_active ?? true))
           class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
    Aktif
</label>

<div class="mt-6 flex items-center gap-3">
    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
        Simpan
    </button>
    <a href="{{ route('admin.locations.index') }}" class="text-sm font-medium text-slate-500 hover:underline">Batal</a>
</div>
```

Create `resources/views/admin/locations/create.blade.php`:

```blade
@extends('layouts.app')

@section('title', 'Lokasi Baharu · Pentadbiran')

@section('content')
    <h2 class="mb-4 text-xl font-bold text-slate-900">Lokasi Baharu</h2>

    <form method="POST" action="{{ route('admin.locations.store') }}"
          class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @include('admin.locations._form')
    </form>
@endsection
```

Create `resources/views/admin/locations/edit.blade.php`:

```blade
@extends('layouts.app')

@section('title', 'Sunting Lokasi · Pentadbiran')

@section('content')
    <h2 class="mb-1 text-xl font-bold text-slate-900">Sunting Lokasi</h2>
    <p class="mb-4 text-sm text-slate-500">{{ $location->fullPath() }}</p>

    <form method="POST" action="{{ route('admin.locations.update', $location) }}"
          class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @method('PUT')
        @include('admin.locations._form')
    </form>
@endsection
```

- [ ] **Step 8: Run both location test files**

Run: `php artisan test --filter="LocationManagementTest|LocationValidationTest"`

Expected: PASS, 16 dan 8 ujian.

- [ ] **Step 9: Checkpoint**

```bash
vendor/bin/pint --format agent
php artisan test
```

Expected: 103 ujian lulus.

---

## Task 11: Komponen pemilih lokasi

**Files:**
- Create: `resources/views/components/ui/location-picker.blade.php`
- Test: `tests/Feature/Admin/LocationPickerTest.php`

Komponen ini ialah antara muka M03 kepada M04 dan M09 (FR-ORG-05). Ia memaparkan empat turun-turun berjujukan yang ditapis di sebelah pelanggan daripada satu muatan JSON.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Admin/LocationPickerTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class LocationPickerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_active_locations_as_a_json_payload(): void
    {
        $campus = Location::factory()->create(['name' => 'Kampus Induk']);
        Location::factory()->bangunan()->childOf($campus)->create(['name' => 'Blok A']);

        $html = Blade::render('<x-ui.location-picker name="primary_location_id" />');

        $this->assertStringContainsString('Kampus Induk', $html);
        $this->assertStringContainsString('Blok A', $html);
        $this->assertStringContainsString('name="primary_location_id"', $html);
    }

    public function test_it_omits_deactivated_locations(): void
    {
        Location::factory()->create(['name' => 'Kampus Aktif']);
        Location::factory()->inactive()->create(['name' => 'Kampus Ditutup']);

        $html = Blade::render('<x-ui.location-picker name="primary_location_id" />');

        $this->assertStringContainsString('Kampus Aktif', $html);
        $this->assertStringNotContainsString('Kampus Ditutup', $html);
    }

    public function test_it_marks_the_selected_location(): void
    {
        $campus = Location::factory()->create(['name' => 'Kampus Induk']);

        $html = Blade::render(
            '<x-ui.location-picker name="primary_location_id" :selected="$id" />',
            ['id' => $campus->id]
        );

        $this->assertStringContainsString('value="'.$campus->id.'"', $html);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=LocationPickerTest`

Expected: FAIL with `Unable to locate a class or view for component [ui.location-picker]`.

- [ ] **Step 3: Write minimal implementation**

Create `resources/views/components/ui/location-picker.blade.php`:

```blade
@props([
    'name',
    'selected' => null,
    'label' => 'Lokasi',
    'required' => false,
])

@php
    $locations = \App\Models\Location::query()
        ->active()
        ->orderBy('name')
        ->get(['id', 'code', 'name', 'level', 'parent_id'])
        ->map(fn ($location) => [
            'id' => $location->id,
            'code' => $location->code,
            'name' => $location->name,
            'level' => $location->level->value,
            'parent_id' => $location->parent_id,
        ])
        ->values();

    $selectedId = old($name, $selected);
    $levels = \App\Enums\LocationLevel::cases();
@endphp

<div x-data="locationPicker(@js($locations), @js($selectedId))" class="space-y-3">
    <span class="block text-sm font-medium text-slate-700">{{ $label }}</span>

    @foreach ($levels as $level)
        <div>
            <label for="{{ $name }}_{{ $level->value }}" class="block text-xs font-medium text-slate-500">
                {{ $level->label() }}
            </label>
            <select id="{{ $name }}_{{ $level->value }}"
                    x-model="chosen['{{ $level->value }}']"
                    @change="clearBelow('{{ $level->value }}')"
                    class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">— Pilih {{ $level->label() }} —</option>
                <template x-for="option in optionsFor('{{ $level->value }}')" :key="option.id">
                    <option :value="option.id" x-text="option.name + ' (' + option.code + ')'"></option>
                </template>
            </select>
        </div>
    @endforeach

    <input type="hidden" name="{{ $name }}" :value="value()" @required($required)>

    {{-- Server-rendered fallback so the options are visible without JavaScript. --}}
    <noscript>
        <select name="{{ $name }}" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
            <option value="">— Pilih lokasi —</option>
            @foreach ($locations as $location)
                <option value="{{ $location['id'] }}" @selected((string) $selectedId === (string) $location['id'])>
                    {{ $location['name'] }} ({{ $location['code'] }})
                </option>
            @endforeach
        </select>
    </noscript>
</div>

@once
    @push('scripts')
        <script>
            function locationPicker(locations, selectedId) {
                const levels = ['kampus', 'bangunan', 'tingkat', 'ruang'];

                return {
                    locations,
                    chosen: { kampus: '', bangunan: '', tingkat: '', ruang: '' },

                    init() {
                        if (! selectedId) {
                            return;
                        }

                        let node = this.locations.find((item) => String(item.id) === String(selectedId));

                        while (node) {
                            this.chosen[node.level] = String(node.id);
                            node = this.locations.find((item) => String(item.id) === String(node.parent_id));
                        }
                    },

                    optionsFor(level) {
                        const index = levels.indexOf(level);

                        if (index === 0) {
                            return this.locations.filter((item) => item.parent_id === null);
                        }

                        const parentId = this.chosen[levels[index - 1]];

                        if (! parentId) {
                            return [];
                        }

                        return this.locations.filter((item) => String(item.parent_id) === String(parentId));
                    },

                    clearBelow(level) {
                        const index = levels.indexOf(level);

                        levels.slice(index + 1).forEach((lower) => {
                            this.chosen[lower] = '';
                        });
                    },

                    value() {
                        for (const level of [...levels].reverse()) {
                            if (this.chosen[level]) {
                                return this.chosen[level];
                            }
                        }

                        return '';
                    },
                };
            }
        </script>
    @endpush
@endonce
```

- [ ] **Step 4: Confirm the layout renders the pushed scripts stack**

Run: `grep -n "@stack('scripts')" resources/views/layouts/app.blade.php`

If the command prints nothing, open `resources/views/layouts/app.blade.php` and add `@stack('scripts')` on its own line immediately before the closing `</body>` tag.

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=LocationPickerTest`

Expected: PASS, 3 tests.

- [ ] **Step 6: Checkpoint**

```bash
vendor/bin/pint --format agent
php artisan test
```

Expected: 106 ujian lulus.

---

## Task 12: Guna pemilih lokasi dalam borang pengguna

**Files:**
- Modify: `resources/views/admin/users/_form.blade.php`
- Modify: `app/Http/Controllers/Admin/UserController.php`
- Test: `tests/Feature/Admin/UserManagementTest.php` (tambah satu ujian)

Ini membuktikan komponen berfungsi dalam borang sebenar, dan membuang senarai lokasi rata yang sedia ada.

- [ ] **Step 1: Write the failing test**

Append this method to `tests/Feature/Admin/UserManagementTest.php`, inside the existing class:

```php
    public function test_the_user_form_uses_the_location_tree_picker(): void
    {
        $campus = \App\Models\Location::factory()->create(['name' => 'Kampus Induk']);
        \App\Models\Location::factory()->bangunan()->childOf($campus)->create(['name' => 'Blok A']);

        $this->actingAs($this->admin)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('primary_location_id_kampus')
            ->assertSee('Blok A');
    }
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=test_the_user_form_uses_the_location_tree_picker`

Expected: FAIL — `primary_location_id_kampus` tidak dijumpai dalam respons.

- [ ] **Step 3: Replace the flat location select in the user form**

In `resources/views/admin/users/_form.blade.php`, find the block that renders the `primary_location_id` select (it loops over `$locations`) and replace that whole block with:

```blade
<div class="sm:col-span-2">
    <x-ui.location-picker name="primary_location_id" :selected="$user->primary_location_id ?? null" label="Lokasi utama" />
    @error('primary_location_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
</div>
```

- [ ] **Step 4: Drop the now-unused controller data**

In `app/Http/Controllers/Admin/UserController.php`, remove the `'locations' => Location::all(['id', 'name']),` line from both `create()` and `edit()`, and remove the now-unused `use App\Models\Location;` import.

- [ ] **Step 5: Run the full user management suite**

Run: `php artisan test --filter=UserManagementTest`

Expected: PASS, 11 tests.

- [ ] **Step 6: Checkpoint**

```bash
vendor/bin/pint --format agent
php artisan test
```

Expected: 107 ujian lulus.

---

## Task 13: Form Request unit organisasi

**Files:**
- Create: `app/Http/Requests/Admin/OrganizationUnitStoreRequest.php`
- Create: `app/Http/Requests/Admin/OrganizationUnitUpdateRequest.php`

Laluan belum wujud sehingga Task 14, jadi tugasan ini tidak mempunyai langkah ujian sendiri. **Laksanakan Task 13 dan Task 14 berturut-turut.**

- [ ] **Step 1: Write the store request**

Create `app/Http/Requests/Admin/OrganizationUnitStoreRequest.php`:

```php
<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrganizationUnitStoreRequest extends FormRequest
{
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
}
```

- [ ] **Step 2: Write the update request**

Create `app/Http/Requests/Admin/OrganizationUnitUpdateRequest.php`:

```php
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

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

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
```

- [ ] **Step 3: Confirm nothing broke**

Run: `php artisan test`

Expected: 107 ujian lulus, tiada yang gagal. Fail baharu belum digunakan oleh mana-mana laluan.

---

## Task 14: Pengawal dan paparan unit organisasi

**Files:**
- Create: `app/Http/Controllers/Admin/OrganizationUnitController.php`
- Create: `resources/views/admin/organization-units/index.blade.php`
- Create: `resources/views/admin/organization-units/_form.blade.php`
- Create: `resources/views/admin/organization-units/create.blade.php`
- Create: `resources/views/admin/organization-units/edit.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Admin/OrganizationUnitManagementTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Admin/OrganizationUnitManagementTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\OrganizationUnit;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationUnitManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('pentadbir-sistem');
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_every_role_may_view_the_unit_tree(): void
    {
        foreach (array_keys(RolesAndPermissionsSeeder::ROLES) as $role) {
            $this->actingAs($this->userWithRole($role))
                ->get(route('admin.organization-units.index'))
                ->assertOk();
        }
    }

    public function test_a_facility_administrator_may_not_create_units(): void
    {
        $this->actingAs($this->userWithRole('pentadbir-fasiliti'))
            ->get(route('admin.organization-units.create'))
            ->assertForbidden();
    }

    public function test_the_administrator_may_create_a_division(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.organization-units.store'), [
                'code' => 'BHG-BARU',
                'name' => 'Bahagian Baharu',
                'parent_id' => null,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.organization-units.index'));

        $this->assertDatabaseHas('organization_units', ['code' => 'BHG-BARU']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'organization_unit.created']);
    }

    public function test_a_unit_may_not_hang_under_another_unit(): void
    {
        $division = OrganizationUnit::factory()->create();
        $unit = OrganizationUnit::factory()->childOf($division)->create();

        $this->actingAs($this->admin)
            ->from(route('admin.organization-units.create'))
            ->post(route('admin.organization-units.store'), [
                'code' => 'TERLALU-DALAM',
                'name' => 'Unit Aras Ketiga',
                'parent_id' => $unit->id,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('parent_id');
    }

    public function test_a_division_with_children_may_not_become_a_unit(): void
    {
        $division = OrganizationUnit::factory()->create();
        OrganizationUnit::factory()->childOf($division)->create();
        $other = OrganizationUnit::factory()->create();

        $this->actingAs($this->admin)
            ->from(route('admin.organization-units.edit', $division))
            ->put(route('admin.organization-units.update', $division), [
                'code' => $division->code,
                'name' => $division->name,
                'parent_id' => $other->id,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('parent_id');
    }

    public function test_deactivating_a_division_deactivates_its_units(): void
    {
        $division = OrganizationUnit::factory()->create();
        $unit = OrganizationUnit::factory()->childOf($division)->create();

        $this->actingAs($this->admin)
            ->patch(route('admin.organization-units.toggle', $division))
            ->assertRedirect(route('admin.organization-units.index'));

        $this->assertFalse($division->fresh()->is_active);
        $this->assertFalse($unit->fresh()->is_active);
    }

    public function test_deletion_is_blocked_when_a_user_belongs_to_the_unit(): void
    {
        $unit = OrganizationUnit::factory()->create();
        User::factory()->create(['organization_unit_id' => $unit->id]);

        $this->actingAs($this->admin)
            ->from(route('admin.organization-units.index'))
            ->delete(route('admin.organization-units.destroy', $unit))
            ->assertSessionHasErrors('id');

        $this->assertDatabaseHas('organization_units', ['id' => $unit->id]);
    }

    public function test_an_unreferenced_unit_is_deleted(): void
    {
        $unit = OrganizationUnit::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.organization-units.destroy', $unit))
            ->assertRedirect(route('admin.organization-units.index'));

        $this->assertDatabaseMissing('organization_units', ['id' => $unit->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'organization_unit.deleted']);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=OrganizationUnitManagementTest`

Expected: FAIL with `Route [admin.organization-units.index] not defined.`

- [ ] **Step 3: Write the controller**

Create `app/Http/Controllers/Admin/OrganizationUnitController.php`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrganizationUnitStoreRequest;
use App\Http\Requests\Admin\OrganizationUnitUpdateRequest;
use App\Models\OrganizationUnit;
use App\Models\User;
use App\Services\Audit\AuditRecorder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * M03 — organisational hierarchy, divisions and units (FR-ORG-02).
 */
class OrganizationUnitController extends Controller
{
    public function __construct(private readonly AuditRecorder $audit) {}

    public function index(): View
    {
        return view('admin.organization-units.index', [
            'divisions' => OrganizationUnit::query()
                ->whereNull('parent_id')
                ->with(['children' => fn ($query) => $query->orderBy('name')])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.organization-units.create', [
            'unit' => new OrganizationUnit(['is_active' => true]),
            'divisions' => $this->divisionOptions(),
        ]);
    }

    public function store(OrganizationUnitStoreRequest $request): RedirectResponse
    {
        $unit = OrganizationUnit::create([
            'code' => $request->string('code')->upper()->value(),
            'name' => $request->string('name')->value(),
            'parent_id' => $request->input('parent_id'),
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->audit->record($this->actor(), 'organization_unit.created', $unit, after: $unit->only([
            'code', 'name', 'parent_id',
        ]));

        return to_route('admin.organization-units.index')
            ->with('status', 'Unit organisasi telah dicipta.');
    }

    public function edit(OrganizationUnit $organizationUnit): View
    {
        return view('admin.organization-units.edit', [
            'unit' => $organizationUnit,
            'divisions' => $this->divisionOptions($organizationUnit),
        ]);
    }

    public function update(OrganizationUnitUpdateRequest $request, OrganizationUnit $organizationUnit): RedirectResponse
    {
        $before = $organizationUnit->only(['code', 'name', 'parent_id', 'is_active']);

        $organizationUnit->update([
            'code' => $request->string('code')->upper()->value(),
            'name' => $request->string('name')->value(),
            'parent_id' => $request->input('parent_id'),
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->audit->record(
            $this->actor(),
            'organization_unit.updated',
            $organizationUnit,
            before: $before,
            after: $organizationUnit->refresh()->only(['code', 'name', 'parent_id', 'is_active']),
        );

        return to_route('admin.organization-units.index')
            ->with('status', 'Unit organisasi telah dikemas kini.');
    }

    public function toggle(OrganizationUnit $organizationUnit): RedirectResponse
    {
        $activating = ! $organizationUnit->is_active;

        DB::transaction(function () use ($organizationUnit, $activating): void {
            $organizationUnit->update(['is_active' => $activating]);

            if (! $activating) {
                OrganizationUnit::query()
                    ->whereIn('id', $organizationUnit->descendantIds())
                    ->update(['is_active' => false]);
            }
        });

        $this->audit->record(
            $this->actor(),
            $activating ? 'organization_unit.activated' : 'organization_unit.deactivated',
            $organizationUnit,
            before: ['is_active' => ! $activating],
            after: ['is_active' => $activating],
        );

        return to_route('admin.organization-units.index')->with(
            'status',
            $activating ? 'Unit telah diaktifkan semula.' : 'Unit dan unit anaknya telah dinyahaktifkan.'
        );
    }

    public function destroy(OrganizationUnit $organizationUnit): RedirectResponse
    {
        $reasons = $organizationUnit->referenceSummary();

        if ($reasons !== []) {
            return back()->withErrors([
                'id' => 'Unit ini tidak boleh dipadam kerana masih dirujuk oleh: '.implode(', ', $reasons).'. Nyahaktifkan unit sebagai gantinya.',
            ]);
        }

        $snapshot = $organizationUnit->only(['code', 'name', 'parent_id']);
        $organizationUnit->delete();

        $this->audit->record($this->actor(), 'organization_unit.deleted', $organizationUnit, before: $snapshot);

        return to_route('admin.organization-units.index')
            ->with('status', 'Unit organisasi telah dipadam.');
    }

    /**
     * Divisions available as a parent, excluding the record being edited.
     *
     * @return \Illuminate\Support\Collection<int, OrganizationUnit>
     */
    private function divisionOptions(?OrganizationUnit $exclude = null): \Illuminate\Support\Collection
    {
        return OrganizationUnit::query()
            ->whereNull('parent_id')
            ->when($exclude !== null, fn ($query) => $query->whereKeyNot($exclude->id))
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }

    private function actor(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
```

- [ ] **Step 4: Register the routes**

In `routes/web.php`, add the import:

```php
use App\Http\Controllers\Admin\OrganizationUnitController;
```

Then add these routes inside the same permission-based `Route::prefix('admin')->name('admin.')` group created in Task 10, after the location routes:

```php
            Route::get('organization-units', [OrganizationUnitController::class, 'index'])
                ->middleware('can:unit-organisasi.lihat')
                ->name('organization-units.index');

            Route::get('organization-units/create', [OrganizationUnitController::class, 'create'])
                ->middleware('can:unit-organisasi.cipta')
                ->name('organization-units.create');

            Route::post('organization-units', [OrganizationUnitController::class, 'store'])
                ->middleware('can:unit-organisasi.cipta')
                ->name('organization-units.store');

            Route::get('organization-units/{organization_unit}/edit', [OrganizationUnitController::class, 'edit'])
                ->middleware('can:unit-organisasi.kemaskini')
                ->name('organization-units.edit');

            Route::put('organization-units/{organization_unit}', [OrganizationUnitController::class, 'update'])
                ->middleware('can:unit-organisasi.kemaskini')
                ->name('organization-units.update');

            Route::patch('organization-units/{organization_unit}/toggle', [OrganizationUnitController::class, 'toggle'])
                ->middleware('can:unit-organisasi.kemaskini')
                ->name('organization-units.toggle');

            Route::delete('organization-units/{organization_unit}', [OrganizationUnitController::class, 'destroy'])
                ->middleware('can:unit-organisasi.padam')
                ->name('organization-units.destroy');
```

Route model binding uses the snake-cased parameter `{organization_unit}`, which Laravel resolves to the `OrganizationUnit` model automatically.

- [ ] **Step 5: Write the index view**

Create `resources/views/admin/organization-units/index.blade.php`:

```blade
@extends('layouts.app')

@section('title', 'Unit Organisasi · Pentadbiran')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Direktori Organisasi</h2>
            <p class="mt-1 text-sm text-slate-500">Bahagian dan unit di bawahnya.</p>
        </div>
        @can('unit-organisasi.cipta')
            <a href="{{ route('admin.organization-units.create') }}"
               class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                + Unit Baharu
            </a>
        @endcan
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @error('id')
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $message }}</div>
    @enderror

    <div class="space-y-4">
        @forelse ($divisions as $division)
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-semibold text-slate-900">{{ $division->name }}</span>
                    <span class="rounded bg-slate-100 px-2 py-0.5 font-mono text-xs text-slate-600">{{ $division->code }}</span>
                    @unless ($division->is_active)
                        <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">Tidak aktif</span>
                    @endunless

                    @can('unit-organisasi.kemaskini')
                        <span class="ml-auto flex items-center gap-3 text-sm">
                            <a href="{{ route('admin.organization-units.edit', $division) }}" class="font-medium text-indigo-600 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.organization-units.toggle', $division) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="font-medium text-slate-600 hover:underline">
                                    {{ $division->is_active ? 'Nyahaktif' : 'Aktifkan' }}
                                </button>
                            </form>
                            @can('unit-organisasi.padam')
                                <form method="POST" action="{{ route('admin.organization-units.destroy', $division) }}"
                                      onsubmit="return confirm('Padam unit ini secara kekal?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-medium text-rose-600 hover:underline">Padam</button>
                                </form>
                            @endcan
                        </span>
                    @endcan
                </div>

                <ul class="mt-3 space-y-2 border-l border-slate-200 pl-4">
                    @forelse ($division->children as $unit)
                        <li class="flex flex-wrap items-center gap-2 text-sm">
                            <span class="text-slate-800">{{ $unit->name }}</span>
                            <span class="rounded bg-slate-100 px-2 py-0.5 font-mono text-xs text-slate-600">{{ $unit->code }}</span>
                            @unless ($unit->is_active)
                                <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">Tidak aktif</span>
                            @endunless
                            @can('unit-organisasi.kemaskini')
                                <a href="{{ route('admin.organization-units.edit', $unit) }}" class="ml-auto font-medium text-indigo-600 hover:underline">Edit</a>
                            @endcan
                        </li>
                    @empty
                        <li class="text-sm text-slate-500">Tiada unit di bawah bahagian ini.</li>
                    @endforelse
                </ul>
            </div>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-500 shadow-sm">
                Tiada bahagian didaftarkan lagi.
            </div>
        @endforelse
    </div>
@endsection
```

- [ ] **Step 6: Write the form partial and the two form pages**

Create `resources/views/admin/organization-units/_form.blade.php`:

```blade
@csrf

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="code" class="block text-sm font-medium text-slate-700">Kod</label>
        <input type="text" id="code" name="code" value="{{ old('code', $unit->code) }}" required maxlength="50"
               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('code')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Nama</label>
        <input type="text" id="name" name="name" value="{{ old('name', $unit->name) }}" required maxlength="150"
               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <label for="parent_id" class="block text-sm font-medium text-slate-700">Bahagian induk</label>
        <select id="parent_id" name="parent_id"
                class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Tiada (rekod ini ialah sebuah bahagian)</option>
            @foreach ($divisions as $division)
                <option value="{{ $division->id }}" @selected((string) old('parent_id', $unit->parent_id) === (string) $division->id)>
                    {{ $division->name }} ({{ $division->code }})
                </option>
            @endforeach
        </select>
        @error('parent_id')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
</div>

<label class="mt-4 flex items-center gap-2 text-sm text-slate-700">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $unit->is_active ?? true))
           class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
    Aktif
</label>

<div class="mt-6 flex items-center gap-3">
    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
        Simpan
    </button>
    <a href="{{ route('admin.organization-units.index') }}" class="text-sm font-medium text-slate-500 hover:underline">Batal</a>
</div>
```

Create `resources/views/admin/organization-units/create.blade.php`:

```blade
@extends('layouts.app')

@section('title', 'Unit Baharu · Pentadbiran')

@section('content')
    <h2 class="mb-4 text-xl font-bold text-slate-900">Unit Organisasi Baharu</h2>

    <form method="POST" action="{{ route('admin.organization-units.store') }}"
          class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @include('admin.organization-units._form')
    </form>
@endsection
```

Create `resources/views/admin/organization-units/edit.blade.php`:

```blade
@extends('layouts.app')

@section('title', 'Sunting Unit · Pentadbiran')

@section('content')
    <h2 class="mb-1 text-xl font-bold text-slate-900">Sunting Unit Organisasi</h2>
    <p class="mb-4 text-sm text-slate-500">{{ $unit->fullPath() }}</p>

    <form method="POST" action="{{ route('admin.organization-units.update', $unit) }}"
          class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @method('PUT')
        @include('admin.organization-units._form')
    </form>
@endsection
```

- [ ] **Step 7: Run test to verify it passes**

Run: `php artisan test --filter=OrganizationUnitManagementTest`

Expected: PASS, 8 tests.

- [ ] **Step 8: Checkpoint**

```bash
vendor/bin/pint --format agent
php artisan test
```

Expected: 115 ujian lulus.

---

## Task 15: Pautan navigasi dan seeder yang diperluas

**Files:**
- Modify: `resources/views/layouts/app.blade.php`
- Modify: `database/seeders/OrganizationStructureSeeder.php`
- Test: `tests/Feature/Admin/DirectoryNavigationTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Admin/DirectoryNavigationTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectoryNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_the_sidebar_links_to_the_directories_for_every_role(): void
    {
        $this->actingAs($this->userWithRole('kakitangan'))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('admin.locations.index'))
            ->assertSee(route('admin.organization-units.index'));
    }

    public function test_the_sidebar_hides_user_administration_from_non_administrators(): void
    {
        $this->actingAs($this->userWithRole('kakitangan'))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('admin.users.index'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=DirectoryNavigationTest`

Expected: FAIL — pautan direktori tiada dalam bar sisi; ujian kedua mungkin turut gagal kerana pautan pentadbiran dipaparkan tanpa syarat.

- [ ] **Step 3: Update the sidebar**

In `resources/views/layouts/app.blade.php`, inside the `<nav>` block, wrap the existing user and role links in a role check and add the two directory links. The nav body becomes:

```blade
            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    Papan Pemuka
                </a>

                @can('lokasi.lihat')
                    <a href="{{ route('admin.locations.index') }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.locations*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        Lokasi
                    </a>
                @endcan

                @can('unit-organisasi.lihat')
                    <a href="{{ route('admin.organization-units.index') }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.organization-units*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        Unit Organisasi
                    </a>
                @endcan

                @role('pentadbir-sistem')
                    <a href="{{ route('admin.users.index') }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.users*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        Pengguna
                    </a>

                    <a href="{{ route('admin.roles.index') }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.roles*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        Peranan
                    </a>
                @endrole
            </nav>
```

Keep every other line of the layout as it is, including the dashboard link markup already present if it differs only in wording.

- [ ] **Step 4: Extend the structure seeder with rooms**

In `database/seeders/OrganizationStructureSeeder.php`, replace the `$locationData` array with:

```php
        $locationData = [
            ['code' => 'KAMPUS-PUTRAJAYA', 'name' => 'Kampus Putrajaya', 'level' => 'kampus', 'parent' => null],
            ['code' => 'BANG-A', 'name' => 'Bangunan A', 'level' => 'bangunan', 'parent' => 'KAMPUS-PUTRAJAYA'],
            ['code' => 'BANG-A-T1', 'name' => 'Aras 1 Bangunan A', 'level' => 'tingkat', 'parent' => 'BANG-A'],
            ['code' => 'BANG-A-T2', 'name' => 'Aras 2 Bangunan A', 'level' => 'tingkat', 'parent' => 'BANG-A'],
            ['code' => 'BM-A-1-01', 'name' => 'Bilik Mesyuarat Utama', 'level' => 'ruang', 'parent' => 'BANG-A-T1'],
            ['code' => 'BM-A-2-01', 'name' => 'Bilik Perbincangan 2A', 'level' => 'ruang', 'parent' => 'BANG-A-T2'],
            ['code' => 'PEJ-A-2-02', 'name' => 'Pejabat Unit ICT', 'level' => 'ruang', 'parent' => 'BANG-A-T2'],
        ];
```

Also change the `firstOrCreate` lookup for locations so it matches the new scoped uniqueness. Replace the location loop body with:

```php
        foreach ($locationData as $location) {
            $parentId = $location['parent'] !== null ? $locationIds[$location['parent']] : null;

            $created = Location::firstOrCreate(
                ['code' => $location['code'], 'parent_id' => $parentId],
                [
                    'name' => $location['name'],
                    'level' => $location['level'],
                    'is_active' => true,
                ]
            );

            $locationIds[$location['code']] = $created->id;
        }
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=DirectoryNavigationTest`

Expected: PASS, 2 tests.

- [ ] **Step 6: Verify the seeders still run against a real database**

Run: `php artisan migrate:fresh --seed`

Expected: semua migrasi berjalan, seeder selesai tanpa ralat, dan lapan akaun demo dicipta.

- [ ] **Step 7: Checkpoint**

```bash
vendor/bin/pint --format agent
php artisan test
```

Expected: 117 ujian lulus.

---

## Task 16: Kemas kini memory-bank

**Files:**
- Modify: `memory-bank/progress.md`
- Modify: `memory-bank/activeContext.md`

- [ ] **Step 1: Update progress.md**

In the "What works" section, add:

```markdown
- **M03 — Direktori Organisasi & Lokasi (siap)** — pokok lokasi empat aras dengan buka/tutup Alpine, CRUD lokasi dan unit organisasi, nyahaktif melata ke keturunan, padam disekat oleh `referenceSummary()`, kod lokasi unik dalam induk (DRD §4.1), komponen `<x-ui.location-picker>` dipakai borang pengguna. Kebenaran `lokasi.*` dan `unit-organisasi.*` mengikut matriks modul §3.
- **Perakam audit umum** — `App\Services\Audit\AuditRecorder` merekod nilai sebelum/selepas bagi medan yang berubah sahaja (FR-ADM-08).
```

In "What's left to build", remove the mention of M03 from the domain module line so it reads:

```markdown
- Modul domain (M01, M04–M15): konfigurasi, tempahan, aduan, aset, kalendar, laporan, notifikasi, dsb.
```

- [ ] **Step 2: Update activeContext.md**

Replace the "Current Focus" and "Next Steps" sections with:

```markdown
## Current Focus
- **M03 Direktori Organisasi & Lokasi selesai.** Pokok lokasi, CRUD, nyahaktif melata, pemilih lokasi boleh guna semula, dan audit perubahan.

## Next Steps
- M01 Pentadbiran Sistem & Konfigurasi, ikut `docs/superpowers/plans/2026-09-08-m01-konfigurasi-sistem.md`.
```

Add to "Notes / Gotchas":

```markdown
- Kod lokasi kini unik dalam induk yang sama, bukan menyeluruh. Keunikan aras kampus dikuatkuasakan dalam `LocationStoreRequest`, bukan indeks, kerana NULL dianggap berbeza oleh MySQL dan SQLite.
- Laluan M03 menggunakan middleware `can:`, bukan `role:`, kerana tiga peranan berkongsi capaian.
```

- [ ] **Step 3: Final verification**

```bash
vendor/bin/pint --format agent
php artisan test
```

Expected: pemformat bersih, **117 ujian lulus**, tiada gagal.

- [ ] **Step 4: Manual smoke test**

Run: `php artisan serve`

Then in a browser:
1. Log in as `admin@e-fasiliti.test`.
2. Open Lokasi. Confirm the tree shows the campus, building, floors and rooms from the seeder, and that the expand and collapse control works.
3. Create a room under a floor. Confirm it appears in the tree.
4. Try to delete the campus. Confirm the refusal message names the blocking references.
5. Deactivate the building. Confirm the floors and rooms below it are marked inactive too.
6. Open Pengguna, then the create form, and confirm the location picker narrows from campus down to room.

---

## Ringkasan Liputan Spec

| Keperluan | Tugasan |
|---|---|
| FR-ORG-01 hierarki empat aras | Task 1, 4, 5, 9, 10 |
| FR-ORG-02 hierarki organisasi dua aras | Task 6, 13, 14 |
| FR-ORG-03 halang padam yang masih diguna | Task 5, 6, 10, 14 |
| FR-ORG-04 nama semula tanpa jejas sejarah | Task 10 (ujian `test_renaming_a_location_keeps_the_same_record_for_history`) |
| FR-ORG-05 pemilih lokasi berbentuk pokok | Task 11, 12 |
| Matriks kebenaran modul §3 | Task 8, 10, 14 |
| Audit perubahan (asas FR-ADM-08) | Task 7 |
| DRD §4.1 kod unik dalam induk | Task 3, 9 |
