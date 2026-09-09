# M01 Pentadbiran Sistem & Konfigurasi — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menyiapkan semua tetapan yang boleh diubah tanpa menulis kod, memenuhi FR-ADM-01 hingga FR-ADM-08.

**Architecture:** Satu jadual kunci-nilai `system_settings` untuk tetapan skalar, dan enam jadual berasingan untuk tetapan yang sebenarnya senarai baris. Semua bacaan dan tulisan melalui `SettingsRepository` yang bercache. Setiap tab ialah pengawal kecil tersendiri di bawah `admin/settings`, berkongsi satu partial navigasi tab.

**Tech Stack:** Laravel 13, PHP 8.4, Spatie laravel-permission v8, Blade, Tailwind v4, Alpine.js, PHPUnit 12.

**Spec:** `docs/superpowers/specs/2026-09-08-m03-m01-asas-platform-design.md`

**Prasyarat:** Pelan M03 (`2026-09-08-m03-direktori-organisasi-lokasi.md`) mesti siap dahulu. Pelan ini menggunakan `App\Services\Audit\AuditRecorder` yang dicipta dalam Task 7 pelan tersebut.

---

## Nota Persekitaran

Projek ini **bukan repositori git**. Tiada langkah komit. Setiap tugasan berakhir dengan langkah semakan:

```bash
vendor/bin/pint --format agent
php artisan test
```

Jangan bergantung kepada jumlah ujian mutlak. Selepas setiap tugasan, syaratnya ialah **tiada ujian gagal** dan ujian baharu tugasan itu lulus.

---

## Struktur Fail

| Fail | Tanggungjawab |
|---|---|
| `app/Models/SystemSetting.php` | Baris tetapan skalar |
| `app/Services/Configuration/SettingsRepository.php` | Baca dan tulis tetapan bertaip, dengan cache |
| `app/Services/Configuration/HolidayCalendar.php` | Soalan kalendar tulen, tanpa pangkalan data |
| `app/Models/{OperatingHour,Holiday,RoleBookingRule,TicketPriority,ReferenceValue,NotificationTemplate}.php` | Satu entiti setiap fail |
| `app/Enums/{ReferenceValueType,NotificationChannel}.php` | Nilai yang sah |
| `app/Http/Controllers/Admin/Settings/*Controller.php` | Satu pengawal setiap tab |
| `resources/views/admin/settings/_tabs.blade.php` | Navigasi tab dikongsi |
| `resources/views/admin/settings/*.blade.php` | Satu paparan setiap tab |
| `database/seeders/SystemConfigurationSeeder.php` | Nilai lalai semua tetapan |

---

## Task 1: Jadual dan model tetapan skalar

**Files:**
- Create: `database/migrations/2026_09_10_000001_create_system_settings_table.php`
- Create: `app/Models/SystemSetting.php`
- Create: `database/factories/SystemSettingFactory.php`
- Test: `tests/Unit/Models/SystemSettingTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/Models/SystemSettingTest.php`:

```php
<?php

namespace Tests\Unit\Models;

use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_key_is_the_primary_key(): void
    {
        $setting = SystemSetting::create([
            'key' => 'checkin.threshold_minutes',
            'value' => '15',
            'value_type' => 'nombor',
            'group' => 'daftar-masuk',
        ]);

        $this->assertSame('checkin.threshold_minutes', $setting->getKey());
        $this->assertFalse($setting->incrementing);
    }

    public function test_settings_can_be_filtered_by_group(): void
    {
        SystemSetting::create(['key' => 'a.satu', 'value' => '1', 'value_type' => 'nombor', 'group' => 'umum']);
        SystemSetting::create(['key' => 'b.dua', 'value' => '2', 'value_type' => 'nombor', 'group' => 'daftar-masuk']);

        $this->assertSame(1, SystemSetting::query()->inGroup('umum')->count());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=SystemSettingTest`

Expected: FAIL with `Class "App\Models\SystemSetting" not found`.

- [ ] **Step 3: Write the migration**

Create `database/migrations/2026_09_10_000001_create_system_settings_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * M01 scalar configuration, following the KONFIGURASI data dictionary
     * in docs-claude/05-DRD §4.1.
     */
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->string('key', 100)->primary();
            $table->text('value');
            $table->enum('value_type', ['teks', 'nombor', 'boolean', 'json'])->default('teks');
            $table->string('group', 50)->index();
            $table->string('description', 500)->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
```

- [ ] **Step 4: Write the model and factory**

Create `app/Models/SystemSetting.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemSetting extends Model
{
    use HasFactory;

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'value',
        'value_type',
        'group',
        'description',
        'updated_by',
    ];

    /**
     * Restrict a query to one settings tab.
     *
     * @param  Builder<self>  $query
     */
    public function scopeInGroup(Builder $query, string $group): void
    {
        $query->where('group', $group);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
```

Create `database/factories/SystemSettingFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\SystemSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SystemSetting>
 */
class SystemSettingFactory extends Factory
{
    protected $model = SystemSetting::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => 'ujian.'.$this->faker->unique()->word(),
            'value' => '1',
            'value_type' => 'nombor',
            'group' => 'umum',
            'description' => null,
            'updated_by' => null,
        ];
    }
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=SystemSettingTest`

Expected: PASS, 2 tests.

- [ ] **Step 6: Checkpoint**

```bash
vendor/bin/pint --format agent
php artisan test
```

Expected: tiada ujian gagal.

---

## Task 2: Perkhidmatan tetapan bertaip dan bercache

**Files:**
- Create: `app/Services/Configuration/SettingsRepository.php`
- Test: `tests/Unit/Services/SettingsRepositoryTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/Services/SettingsRepositoryTest.php`:

```php
<?php

namespace Tests\Unit\Services;

use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Configuration\SettingsRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SettingsRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private function repository(): SettingsRepository
    {
        return app(SettingsRepository::class);
    }

    public function test_it_decodes_each_value_type(): void
    {
        SystemSetting::create(['key' => 'a.teks', 'value' => '"Selamat"', 'value_type' => 'teks', 'group' => 'umum']);
        SystemSetting::create(['key' => 'a.nombor', 'value' => '30', 'value_type' => 'nombor', 'group' => 'umum']);
        SystemSetting::create(['key' => 'a.boolean', 'value' => 'true', 'value_type' => 'boolean', 'group' => 'umum']);
        SystemSetting::create(['key' => 'a.json', 'value' => '{"x":1}', 'value_type' => 'json', 'group' => 'umum']);

        $repository = $this->repository();

        $this->assertSame('Selamat', $repository->get('a.teks'));
        $this->assertSame(30, $repository->get('a.nombor'));
        $this->assertTrue($repository->get('a.boolean'));
        $this->assertSame(['x' => 1], $repository->get('a.json'));
    }

    public function test_it_returns_the_default_for_an_unknown_key(): void
    {
        $this->assertSame('lalai', $this->repository()->get('tiada.kunci', 'lalai'));
    }

    public function test_it_reads_every_setting_in_a_single_query(): void
    {
        SystemSetting::create(['key' => 'a.satu', 'value' => '1', 'value_type' => 'nombor', 'group' => 'umum']);
        SystemSetting::create(['key' => 'a.dua', 'value' => '2', 'value_type' => 'nombor', 'group' => 'umum']);

        $repository = $this->repository();
        $repository->get('a.satu');

        DB::enableQueryLog();
        $repository->get('a.dua');
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertCount(0, $queries, 'Bacaan kedua sepatutnya datang daripada cache.');
    }

    public function test_writing_a_value_invalidates_the_cache(): void
    {
        SystemSetting::create(['key' => 'a.satu', 'value' => '1', 'value_type' => 'nombor', 'group' => 'umum']);

        $repository = $this->repository();
        $this->assertSame(1, $repository->get('a.satu'));

        $repository->set('a.satu', 99);

        $this->assertSame(99, $repository->get('a.satu'));
        $this->assertDatabaseHas('system_settings', ['key' => 'a.satu', 'value' => '99']);
    }

    public function test_writing_records_the_actor_and_an_audit_entry(): void
    {
        $actor = User::factory()->create();
        SystemSetting::create(['key' => 'a.satu', 'value' => '1', 'value_type' => 'nombor', 'group' => 'umum']);

        $this->repository()->set('a.satu', 5, $actor);

        $this->assertDatabaseHas('system_settings', ['key' => 'a.satu', 'updated_by' => $actor->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'setting.updated', 'record_type' => 'system_setting']);
    }

    public function test_writing_an_unknown_key_creates_it(): void
    {
        $this->repository()->set('baharu.kunci', true, null, 'boolean', 'umum');

        $this->assertTrue($this->repository()->get('baharu.kunci'));
        $this->assertDatabaseHas('system_settings', ['key' => 'baharu.kunci', 'value_type' => 'boolean']);
    }

    public function test_all_can_be_scoped_to_a_group(): void
    {
        SystemSetting::create(['key' => 'a.satu', 'value' => '1', 'value_type' => 'nombor', 'group' => 'umum']);
        SystemSetting::create(['key' => 'b.satu', 'value' => '2', 'value_type' => 'nombor', 'group' => 'daftar-masuk']);

        $this->assertSame(['a.satu' => 1], $this->repository()->all('umum')->all());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=SettingsRepositoryTest`

Expected: FAIL with `Target class [App\Services\Configuration\SettingsRepository] does not exist`.

- [ ] **Step 3: Write minimal implementation**

Create `app/Services/Configuration/SettingsRepository.php`:

```php
<?php

namespace App\Services\Configuration;

use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Audit\AuditRecorder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * The only way the rest of the application reads or writes M01 scalar
 * settings. Everything is loaded in one query and cached, because the
 * number of settings is small and almost every request needs several.
 */
class SettingsRepository
{
    private const CACHE_KEY = 'm01.system_settings';

    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * Decoded value for a key, or $default when the key does not exist.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $rows = $this->rows();

        if (! $rows->has($key)) {
            return $default;
        }

        return $this->decode($rows->get($key)['value'], $rows->get($key)['value_type']);
    }

    /**
     * Every setting, optionally limited to one group, as key => decoded value.
     *
     * @return Collection<string, mixed>
     */
    public function all(?string $group = null): Collection
    {
        return $this->rows()
            ->when($group !== null, fn (Collection $rows) => $rows->where('group', $group))
            ->map(fn (array $row) => $this->decode($row['value'], $row['value_type']));
    }

    /**
     * Write one setting, record the change, and drop the cache (FR-ADM-08).
     */
    public function set(
        string $key,
        mixed $value,
        ?User $actor = null,
        string $valueType = 'teks',
        string $group = 'umum',
    ): void {
        $setting = SystemSetting::query()->find($key);
        $before = $setting === null ? null : $this->decode($setting->value, $setting->value_type);

        $encoded = $this->encode($value);

        $setting = SystemSetting::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $encoded,
                'value_type' => $setting?->value_type ?? $valueType,
                'group' => $setting?->group ?? $group,
                'description' => $setting?->description,
                'updated_by' => $actor?->id,
            ]
        );

        $this->flush();

        if ($actor !== null) {
            $this->audit->record(
                $actor,
                'setting.updated',
                $setting,
                before: ['key' => $key, 'value' => $before],
                after: ['key' => $key, 'value' => $value],
            );
        }
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return Collection<string, array{value: string, value_type: string, group: string, description: ?string}>
     */
    private function rows(): Collection
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => SystemSetting::query()
            ->get(['key', 'value', 'value_type', 'group', 'description'])
            ->keyBy('key')
            ->map(fn (SystemSetting $setting) => [
                'value' => $setting->value,
                'value_type' => $setting->value_type,
                'group' => $setting->group,
                'description' => $setting->description,
            ]));
    }

    private function decode(string $raw, string $type): mixed
    {
        $decoded = json_decode($raw, true);

        return match ($type) {
            'nombor' => is_numeric($decoded) ? $decoded + 0 : 0,
            'boolean' => (bool) $decoded,
            'json' => is_array($decoded) ? $decoded : [],
            default => is_string($decoded) ? $decoded : $raw,
        };
    }

    private function encode(mixed $value): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE) ?: '""';
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=SettingsRepositoryTest`

Expected: PASS, 7 tests.

- [ ] **Step 5: Checkpoint**

```bash
vendor/bin/pint --format agent
php artisan test
```

Expected: tiada ujian gagal.

---

## Task 3: Pembantu global `setting()`

**Files:**
- Create: `app/Support/helpers.php`
- Modify: `composer.json`
- Test: `tests/Unit/Support/SettingHelperTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/Support/SettingHelperTest.php`:

```php
<?php

namespace Tests\Unit\Support;

use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingHelperTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_helper_reads_through_the_repository(): void
    {
        SystemSetting::create([
            'key' => 'umum.nama_organisasi',
            'value' => '"Jabatan Contoh"',
            'value_type' => 'teks',
            'group' => 'umum',
        ]);

        $this->assertSame('Jabatan Contoh', setting('umum.nama_organisasi'));
    }

    public function test_the_helper_falls_back_to_the_default(): void
    {
        $this->assertSame(15, setting('tiada.kunci', 15));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=SettingHelperTest`

Expected: FAIL with `Call to undefined function setting()`.

- [ ] **Step 3: Write the helper**

Create `app/Support/helpers.php`:

```php
<?php

use App\Services\Configuration\SettingsRepository;

if (! function_exists('setting')) {
    /**
     * Read one M01 configuration value.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return app(SettingsRepository::class)->get($key, $default);
    }
}
```

- [ ] **Step 4: Autoload the helper file**

In `composer.json`, change the `autoload` block so it reads:

```json
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Database\\Factories\\": "database/factories/",
            "Database\\Seeders\\": "database/seeders/"
        },
        "files": [
            "app/Support/helpers.php"
        ]
    },
```

Then regenerate the autoloader:

```bash
composer dump-autoload
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=SettingHelperTest`

Expected: PASS, 2 tests.

- [ ] **Step 6: Checkpoint**

```bash
vendor/bin/pint --format agent
php artisan test
```

Expected: tiada ujian gagal.

---

## Task 4: Rangka kawasan tetapan dan kawalan capaian

**Files:**
- Create: `app/Http/Controllers/Admin/Settings/GeneralSettingsController.php`
- Create: `resources/views/admin/settings/_tabs.blade.php`
- Create: `resources/views/admin/settings/general.blade.php`
- Modify: `routes/web.php`
- Modify: `resources/views/layouts/app.blade.php`
- Test: `tests/Feature/Admin/Settings/SettingsAccessTest.php`

Tab pertama ini memaparkan tetapan skalar kumpulan `umum` dan menjadi rangka untuk enam tab berikutnya.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Admin/Settings/SettingsAccessTest.php`:

```php
<?php

namespace Tests\Feature\Admin\Settings;

use App\Models\SystemSetting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsAccessTest extends TestCase
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

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('admin.settings.general'))->assertRedirect(route('login'));
    }

    public function test_the_four_permitted_roles_may_view_settings(): void
    {
        foreach (['pentadbir-sistem', 'pentadbir-fasiliti', 'penyelia-ict', 'pegawai-aset'] as $role) {
            $this->actingAs($this->userWithRole($role))
                ->get(route('admin.settings.general'))
                ->assertOk();
        }
    }

    public function test_other_roles_are_forbidden(): void
    {
        foreach (['kakitangan', 'setiausaha', 'pelulus', 'juruteknik'] as $role) {
            $this->actingAs($this->userWithRole($role))
                ->get(route('admin.settings.general'))
                ->assertForbidden();
        }
    }

    public function test_a_viewer_may_not_write_settings(): void
    {
        SystemSetting::create([
            'key' => 'umum.nama_organisasi',
            'value' => '"Lama"',
            'value_type' => 'teks',
            'group' => 'umum',
        ]);

        $this->actingAs($this->userWithRole('penyelia-ict'))
            ->put(route('admin.settings.general.update'), ['umum_nama_organisasi' => 'Baharu'])
            ->assertForbidden();

        $this->assertSame('Lama', setting('umum.nama_organisasi'));
    }

    public function test_the_administrator_may_write_settings(): void
    {
        SystemSetting::create([
            'key' => 'umum.nama_organisasi',
            'value' => '"Lama"',
            'value_type' => 'teks',
            'group' => 'umum',
        ]);

        $this->actingAs($this->userWithRole('pentadbir-sistem'))
            ->put(route('admin.settings.general.update'), ['umum_nama_organisasi' => 'Baharu'])
            ->assertRedirect(route('admin.settings.general'));

        $this->assertSame('Baharu', setting('umum.nama_organisasi'));
        $this->assertDatabaseHas('audit_logs', ['action' => 'setting.updated']);
    }

    public function test_the_sidebar_shows_the_settings_link_only_to_permitted_roles(): void
    {
        $this->actingAs($this->userWithRole('pegawai-aset'))
            ->get(route('dashboard'))
            ->assertSee(route('admin.settings.general'));

        $this->actingAs($this->userWithRole('kakitangan'))
            ->get(route('dashboard'))
            ->assertDontSee(route('admin.settings.general'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=SettingsAccessTest`

Expected: FAIL with `Route [admin.settings.general] not defined.`

- [ ] **Step 3: Write the controller**

Create `app/Http/Controllers/Admin/Settings/GeneralSettingsController.php`:

```php
<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Configuration\SettingsRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * M01 — organisation-wide scalar settings (FR-ADM-01, partial).
 */
class GeneralSettingsController extends Controller
{
    public function __construct(private readonly SettingsRepository $settings) {}

    public function edit(): View
    {
        return view('admin.settings.general', [
            'organisationName' => $this->settings->get('umum.nama_organisasi', config('app.name')),
            'timezone' => $this->settings->get('umum.zon_masa', 'Asia/Kuala_Lumpur'),
            'locale' => $this->settings->get('umum.bahasa_lalai', 'ms'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'umum_nama_organisasi' => ['required', 'string', 'max:150'],
            'umum_zon_masa' => ['nullable', 'string', 'timezone'],
            'umum_bahasa_lalai' => ['nullable', 'string', 'in:ms,en'],
        ]);

        /** @var User $actor */
        $actor = Auth::user();

        $this->settings->set('umum.nama_organisasi', $validated['umum_nama_organisasi'], $actor, 'teks', 'umum');
        $this->settings->set('umum.zon_masa', $validated['umum_zon_masa'] ?? 'Asia/Kuala_Lumpur', $actor, 'teks', 'umum');
        $this->settings->set('umum.bahasa_lalai', $validated['umum_bahasa_lalai'] ?? 'ms', $actor, 'teks', 'umum');

        return to_route('admin.settings.general')->with('status', 'Tetapan umum telah disimpan.');
    }
}
```

- [ ] **Step 4: Register the routes**

In `routes/web.php`, add the import:

```php
use App\Http\Controllers\Admin\Settings\GeneralSettingsController;
```

Then, inside the permission-based `Route::prefix('admin')->name('admin.')` group created by the M03 plan, append:

```php
            Route::prefix('settings')->name('settings.')->group(function (): void {
                Route::get('general', [GeneralSettingsController::class, 'edit'])
                    ->middleware('can:tetapan.lihat')
                    ->name('general');

                Route::put('general', [GeneralSettingsController::class, 'update'])
                    ->middleware('can:tetapan.kemaskini')
                    ->name('general.update');
            });
```

- [ ] **Step 5: Write the shared tab navigation**

Create `resources/views/admin/settings/_tabs.blade.php`:

```blade
@php
    $tabs = [
        ['route' => 'admin.settings.general', 'label' => 'Umum', 'pattern' => 'admin.settings.general*'],
        ['route' => 'admin.settings.operating-hours', 'label' => 'Waktu Operasi', 'pattern' => 'admin.settings.operating-hours*'],
        ['route' => 'admin.settings.holidays', 'label' => 'Cuti Umum', 'pattern' => 'admin.settings.holidays*'],
        ['route' => 'admin.settings.booking-rules', 'label' => 'Peraturan Tempahan', 'pattern' => 'admin.settings.booking-rules*'],
        ['route' => 'admin.settings.checkin', 'label' => 'Daftar Masuk', 'pattern' => 'admin.settings.checkin*'],
        ['route' => 'admin.settings.ticket-priorities', 'label' => 'Keutamaan Tiket', 'pattern' => 'admin.settings.ticket-priorities*'],
        ['route' => 'admin.settings.reference-values', 'label' => 'Nilai Rujukan', 'pattern' => 'admin.settings.reference-values*'],
        ['route' => 'admin.settings.notification-templates', 'label' => 'Templat Notifikasi', 'pattern' => 'admin.settings.notification-templates*'],
    ];
@endphp

<nav class="mb-6 flex flex-wrap gap-1 border-b border-slate-200" aria-label="Tab konfigurasi">
    @foreach ($tabs as $tab)
        @if (Route::has($tab['route']))
            <a href="{{ route($tab['route']) }}"
               class="-mb-px border-b-2 px-3 py-2 text-sm font-medium {{ request()->routeIs($tab['pattern']) ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
                {{ $tab['label'] }}
            </a>
        @endif
    @endforeach
</nav>
```

The `Route::has` guard lets this partial ship before the remaining tabs exist; each later task adds its own route and the tab appears on its own.

- [ ] **Step 6: Write the general tab view**

Create `resources/views/admin/settings/general.blade.php`:

```blade
@extends('layouts.app')

@section('title', 'Konfigurasi · Umum')

@section('content')
    <h2 class="mb-1 text-xl font-bold text-slate-900">Konfigurasi Sistem</h2>
    <p class="mb-4 text-sm text-slate-500">Tetapan yang boleh diubah tanpa menulis kod.</p>

    @include('admin.settings._tabs')

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.general.update') }}"
          class="max-w-2xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')

        <div class="space-y-4">
            <div>
                <label for="umum_nama_organisasi" class="block text-sm font-medium text-slate-700">Nama organisasi</label>
                <input type="text" id="umum_nama_organisasi" name="umum_nama_organisasi"
                       value="{{ old('umum_nama_organisasi', $organisationName) }}" required maxlength="150"
                       @disabled(! auth()->user()->can('tetapan.kemaskini'))
                       class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('umum_nama_organisasi')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="umum_zon_masa" class="block text-sm font-medium text-slate-700">Zon masa</label>
                <input type="text" id="umum_zon_masa" name="umum_zon_masa" value="{{ old('umum_zon_masa', $timezone) }}"
                       @disabled(! auth()->user()->can('tetapan.kemaskini'))
                       class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('umum_zon_masa')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="umum_bahasa_lalai" class="block text-sm font-medium text-slate-700">Bahasa lalai</label>
                <select id="umum_bahasa_lalai" name="umum_bahasa_lalai"
                        @disabled(! auth()->user()->can('tetapan.kemaskini'))
                        class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="ms" @selected(old('umum_bahasa_lalai', $locale) === 'ms')>Bahasa Melayu</option>
                    <option value="en" @selected(old('umum_bahasa_lalai', $locale) === 'en')>English</option>
                </select>
            </div>
        </div>

        @can('tetapan.kemaskini')
            <button type="submit" class="mt-6 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                Simpan
            </button>
        @endcan
    </form>
@endsection
```

- [ ] **Step 7: Add the sidebar link**

In `resources/views/layouts/app.blade.php`, inside the `<nav>` block and after the organisation unit link added by the M03 plan, add:

```blade
                @can('tetapan.lihat')
                    <a href="{{ route('admin.settings.general') }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.settings*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        Konfigurasi
                    </a>
                @endcan
```

- [ ] **Step 8: Run test to verify it passes**

Run: `php artisan test --filter=SettingsAccessTest`

Expected: PASS, 6 tests.

- [ ] **Step 9: Checkpoint**

```bash
vendor/bin/pint --format agent
php artisan test
```

Expected: tiada ujian gagal.

---

## Task 5: Waktu operasi, FR-ADM-01

**Files:**
- Create: `database/migrations/2026_09_10_000002_create_operating_hours_table.php`
- Create: `app/Models/OperatingHour.php`
- Create: `app/Http/Requests/Admin/OperatingHoursRequest.php`
- Create: `app/Http/Controllers/Admin/Settings/OperatingHourController.php`
- Create: `resources/views/admin/settings/operating-hours.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Admin/Settings/OperatingHoursTest.php`

Jadual ini polimorfik supaya M04 boleh menambah waktu khusus bilik tanpa jadual baharu. Baris dengan `owner_type` dan `owner_id` kosong ialah waktu lalai organisasi.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Admin/Settings/OperatingHoursTest.php`:

```php
<?php

namespace Tests\Feature\Admin\Settings;

use App\Models\OperatingHour;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperatingHoursTest extends TestCase
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

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        $days = [];

        foreach (range(0, 6) as $day) {
            $days[$day] = [
                'is_closed' => in_array($day, [0, 6], true) ? 1 : 0,
                'opens_at' => '08:00',
                'closes_at' => '17:30',
            ];
        }

        foreach ($overrides as $day => $values) {
            $days[$day] = array_merge($days[$day], $values);
        }

        return ['days' => $days];
    }

    public function test_the_tab_renders_seven_days(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.settings.operating-hours'))
            ->assertOk()
            ->assertSee('Isnin')
            ->assertSee('Ahad');
    }

    public function test_the_administrator_saves_default_organisation_hours(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.settings.operating-hours.update'), $this->payload())
            ->assertRedirect(route('admin.settings.operating-hours'));

        $this->assertSame(7, OperatingHour::query()->whereNull('owner_type')->count());
        $this->assertDatabaseHas('operating_hours', ['day_of_week' => 1, 'is_closed' => false]);
        $this->assertDatabaseHas('operating_hours', ['day_of_week' => 0, 'is_closed' => true]);
    }

    public function test_saving_twice_updates_rather_than_duplicates(): void
    {
        $this->actingAs($this->admin)->put(route('admin.settings.operating-hours.update'), $this->payload());
        $this->actingAs($this->admin)->put(
            route('admin.settings.operating-hours.update'),
            $this->payload([1 => ['closes_at' => '18:00']])
        );

        $this->assertSame(7, OperatingHour::query()->count());
        $this->assertStringStartsWith('18:00', OperatingHour::query()->where('day_of_week', 1)->value('closes_at'));
    }

    public function test_the_closing_time_must_be_after_the_opening_time(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.settings.operating-hours'))
            ->put(
                route('admin.settings.operating-hours.update'),
                $this->payload([2 => ['opens_at' => '17:00', 'closes_at' => '09:00']])
            )
            ->assertSessionHasErrors('days.2.closes_at');
    }

    public function test_a_closed_day_does_not_need_times(): void
    {
        $this->actingAs($this->admin)
            ->put(
                route('admin.settings.operating-hours.update'),
                $this->payload([3 => ['is_closed' => 1, 'opens_at' => '', 'closes_at' => '']])
            )
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('operating_hours', ['day_of_week' => 3, 'is_closed' => true]);
    }

    public function test_a_viewer_may_not_save(): void
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('penyelia-ict');

        $this->actingAs($viewer)
            ->put(route('admin.settings.operating-hours.update'), $this->payload())
            ->assertForbidden();
    }

    public function test_saving_writes_an_audit_entry(): void
    {
        $this->actingAs($this->admin)->put(route('admin.settings.operating-hours.update'), $this->payload());

        $this->assertDatabaseHas('audit_logs', ['action' => 'operating_hours.updated']);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=OperatingHoursTest`

Expected: FAIL with `Route [admin.settings.operating-hours] not defined.`

- [ ] **Step 3: Write the migration and model**

Create `database/migrations/2026_09_10_000002_create_operating_hours_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FR-ADM-01. A row with no owner is the organisation default; M04 rooms
     * become owners later without needing another table.
     */
    public function up(): void
    {
        Schema::create('operating_hours', function (Blueprint $table) {
            $table->id();
            $table->string('owner_type')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('opens_at')->nullable();
            $table->time('closes_at')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->timestamps();

            $table->unique(['owner_type', 'owner_id', 'day_of_week'], 'operating_hours_owner_day_unique');
            $table->index(['owner_type', 'owner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operating_hours');
    }
};
```

Create `app/Models/OperatingHour.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OperatingHour extends Model
{
    protected $fillable = [
        'owner_type',
        'owner_id',
        'day_of_week',
        'opens_at',
        'closes_at',
        'is_closed',
    ];

    /**
     * Malay day names, indexed the same way Carbon indexes days of week.
     *
     * @var array<int, string>
     */
    public const DAY_NAMES = [
        0 => 'Ahad',
        1 => 'Isnin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Khamis',
        5 => 'Jumaat',
        6 => 'Sabtu',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'is_closed' => 'boolean',
        ];
    }

    /**
     * The room or other entity these hours belong to, if any.
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Organisation-wide default hours.
     *
     * @param  Builder<self>  $query
     */
    public function scopeOrganisationDefault(Builder $query): void
    {
        $query->whereNull('owner_type')->whereNull('owner_id');
    }

    public function dayName(): string
    {
        return self::DAY_NAMES[$this->day_of_week] ?? '';
    }
}
```

- [ ] **Step 4: Write the form request**

Create `app/Http/Requests/Admin/OperatingHoursRequest.php`:

```php
<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class OperatingHoursRequest extends FormRequest
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
            'days' => ['required', 'array', 'size:7'],
            'days.*.is_closed' => ['nullable', 'boolean'],
            'days.*.opens_at' => ['nullable', 'date_format:H:i'],
            'days.*.closes_at' => ['nullable', 'date_format:H:i'],
        ];
    }

    /**
     * SRS §M01: the closing time must be after the opening time on any day
     * that is not marked closed.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                foreach ((array) $this->input('days', []) as $day => $values) {
                    if ((bool) ($values['is_closed'] ?? false)) {
                        continue;
                    }

                    $opens = $values['opens_at'] ?? null;
                    $closes = $values['closes_at'] ?? null;

                    if ($opens === null || $opens === '' || $closes === null || $closes === '') {
                        $validator->errors()->add("days.{$day}.opens_at", 'Waktu buka dan tutup wajib diisi bagi hari yang beroperasi.');

                        continue;
                    }

                    if (strtotime($closes) <= strtotime($opens)) {
                        $validator->errors()->add("days.{$day}.closes_at", 'Waktu tutup mesti selepas waktu buka.');
                    }
                }
            },
        ];
    }
}
```

- [ ] **Step 5: Write the controller**

Create `app/Http/Controllers/Admin/Settings/OperatingHourController.php`:

```php
<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OperatingHoursRequest;
use App\Models\OperatingHour;
use App\Models\User;
use App\Services\Audit\AuditRecorder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * M01 — organisation-wide operating hours (FR-ADM-01).
 */
class OperatingHourController extends Controller
{
    public function __construct(private readonly AuditRecorder $audit) {}

    public function edit(): View
    {
        $existing = OperatingHour::query()
            ->organisationDefault()
            ->get()
            ->keyBy('day_of_week');

        $days = collect(range(0, 6))->map(fn (int $day) => [
            'day' => $day,
            'name' => OperatingHour::DAY_NAMES[$day],
            'is_closed' => (bool) ($existing[$day]->is_closed ?? in_array($day, [0, 6], true)),
            'opens_at' => substr((string) ($existing[$day]->opens_at ?? '08:00'), 0, 5),
            'closes_at' => substr((string) ($existing[$day]->closes_at ?? '17:00'), 0, 5),
        ]);

        return view('admin.settings.operating-hours', ['days' => $days]);
    }

    public function update(OperatingHoursRequest $request): RedirectResponse
    {
        $before = OperatingHour::query()
            ->organisationDefault()
            ->get()
            ->mapWithKeys(fn (OperatingHour $hour) => [
                $hour->day_of_week => $hour->is_closed ? 'tutup' : $hour->opens_at.'-'.$hour->closes_at,
            ])
            ->all();

        $after = [];

        DB::transaction(function () use ($request, &$after): void {
            foreach ((array) $request->input('days') as $day => $values) {
                $isClosed = (bool) ($values['is_closed'] ?? false);

                OperatingHour::query()->updateOrCreate(
                    ['owner_type' => null, 'owner_id' => null, 'day_of_week' => (int) $day],
                    [
                        'is_closed' => $isClosed,
                        'opens_at' => $isClosed ? null : $values['opens_at'],
                        'closes_at' => $isClosed ? null : $values['closes_at'],
                    ]
                );

                $after[(int) $day] = $isClosed ? 'tutup' : $values['opens_at'].'-'.$values['closes_at'];
            }
        });

        /** @var User $actor */
        $actor = Auth::user();

        $this->audit->record(
            $actor,
            'operating_hours.updated',
            OperatingHour::query()->organisationDefault()->orderBy('day_of_week')->firstOrFail(),
            before: $before,
            after: $after,
        );

        return to_route('admin.settings.operating-hours')
            ->with('status', 'Waktu operasi telah disimpan.');
    }
}
```

- [ ] **Step 6: Register the routes**

In `routes/web.php`, add the import:

```php
use App\Http\Controllers\Admin\Settings\OperatingHourController;
```

Then, inside the `Route::prefix('settings')->name('settings.')` group, append:

```php
                Route::get('operating-hours', [OperatingHourController::class, 'edit'])
                    ->middleware('can:tetapan.lihat')
                    ->name('operating-hours');

                Route::put('operating-hours', [OperatingHourController::class, 'update'])
                    ->middleware('can:tetapan.kemaskini')
                    ->name('operating-hours.update');
```

- [ ] **Step 7: Write the view**

Create `resources/views/admin/settings/operating-hours.blade.php`:

```blade
@extends('layouts.app')

@section('title', 'Konfigurasi · Waktu Operasi')

@section('content')
    <h2 class="mb-1 text-xl font-bold text-slate-900">Konfigurasi Sistem</h2>
    <p class="mb-4 text-sm text-slate-500">Waktu operasi lalai organisasi. Waktu khusus bilik ditetapkan dalam modul bilik (M04).</p>

    @include('admin.settings._tabs')

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.operating-hours.update') }}"
          class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')

        <table class="min-w-full text-sm">
            <thead class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="py-2">Hari</th>
                    <th class="py-2">Tutup</th>
                    <th class="py-2">Buka</th>
                    <th class="py-2">Tutup pada</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($days as $day)
                    <tr x-data="{ closed: {{ $day['is_closed'] ? 'true' : 'false' }} }">
                        <td class="py-3 font-medium text-slate-800">{{ $day['name'] }}</td>
                        <td class="py-3">
                            <input type="hidden" name="days[{{ $day['day'] }}][is_closed]" value="0">
                            <input type="checkbox" name="days[{{ $day['day'] }}][is_closed]" value="1"
                                   x-model="closed" @checked($day['is_closed'])
                                   @disabled(! auth()->user()->can('tetapan.kemaskini'))
                                   class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        </td>
                        <td class="py-3">
                            <input type="time" name="days[{{ $day['day'] }}][opens_at]"
                                   value="{{ old("days.{$day['day']}.opens_at", $day['opens_at']) }}"
                                   :disabled="closed || {{ auth()->user()->can('tetapan.kemaskini') ? 'false' : 'true' }}"
                                   class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error("days.{$day['day']}.opens_at")<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </td>
                        <td class="py-3">
                            <input type="time" name="days[{{ $day['day'] }}][closes_at]"
                                   value="{{ old("days.{$day['day']}.closes_at", $day['closes_at']) }}"
                                   :disabled="closed || {{ auth()->user()->can('tetapan.kemaskini') ? 'false' : 'true' }}"
                                   class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error("days.{$day['day']}.closes_at")<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @can('tetapan.kemaskini')
            <button type="submit" class="mt-6 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                Simpan
            </button>
        @endcan
    </form>
@endsection
```

- [ ] **Step 8: Run test to verify it passes**

Run: `php artisan test --filter=OperatingHoursTest`

Expected: PASS, 7 tests.

- [ ] **Step 9: Checkpoint**

```bash
vendor/bin/pint --format agent
php artisan test
```

Expected: tiada ujian gagal.

---

## Task 6: Kalendar cuti umum, FR-ADM-02

**Files:**
- Create: `database/migrations/2026_09_10_000003_create_holidays_table.php`
- Create: `app/Models/Holiday.php`
- Create: `database/factories/HolidayFactory.php`
- Create: `app/Services/Configuration/HolidayCalendar.php`
- Test: `tests/Unit/Services/HolidayCalendarTest.php`

Tugasan ini membina data dan logik kalendar. Tab antara muka datang dalam Task 7.

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/Services/HolidayCalendarTest.php`:

```php
<?php

namespace Tests\Unit\Services;

use App\Models\Holiday;
use App\Services\Configuration\HolidayCalendar;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HolidayCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_one_off_holiday_matches_only_its_own_date(): void
    {
        Holiday::factory()->create([
            'date' => '2026-05-01',
            'name' => 'Hari Pekerja',
            'type' => 'cuti_umum',
            'recurs_annually' => false,
        ]);

        $calendar = app(HolidayCalendar::class);

        $this->assertTrue($calendar->isHoliday(CarbonImmutable::parse('2026-05-01')));
        $this->assertFalse($calendar->isHoliday(CarbonImmutable::parse('2027-05-01')));
    }

    public function test_an_annually_recurring_holiday_matches_the_same_day_every_year(): void
    {
        Holiday::factory()->create([
            'date' => '2026-08-31',
            'name' => 'Hari Kebangsaan',
            'type' => 'cuti_umum',
            'recurs_annually' => true,
        ]);

        $calendar = app(HolidayCalendar::class);

        $this->assertTrue($calendar->isHoliday(CarbonImmutable::parse('2026-08-31')));
        $this->assertTrue($calendar->isHoliday(CarbonImmutable::parse('2030-08-31')));
        $this->assertFalse($calendar->isHoliday(CarbonImmutable::parse('2030-09-01')));
    }

    public function test_a_deactivated_holiday_is_ignored(): void
    {
        Holiday::factory()->create([
            'date' => '2026-05-01',
            'type' => 'cuti_umum',
            'is_active' => false,
        ]);

        $this->assertFalse(app(HolidayCalendar::class)->isHoliday(CarbonImmutable::parse('2026-05-01')));
    }

    public function test_a_non_bookable_day_is_not_a_public_holiday_but_blocks_bookings(): void
    {
        Holiday::factory()->create([
            'date' => '2026-06-15',
            'name' => 'Penyelenggaraan Bangunan',
            'type' => 'hari_tanpa_tempahan',
        ]);

        $calendar = app(HolidayCalendar::class);

        $this->assertFalse($calendar->isHoliday(CarbonImmutable::parse('2026-06-15')));
        $this->assertFalse($calendar->isBookable(CarbonImmutable::parse('2026-06-15')));
    }

    public function test_a_public_holiday_also_blocks_bookings(): void
    {
        Holiday::factory()->create(['date' => '2026-05-01', 'type' => 'cuti_umum']);

        $this->assertFalse(app(HolidayCalendar::class)->isBookable(CarbonImmutable::parse('2026-05-01')));
    }

    public function test_an_ordinary_day_is_bookable(): void
    {
        $this->assertTrue(app(HolidayCalendar::class)->isBookable(CarbonImmutable::parse('2026-05-02')));
    }

    public function test_the_calendar_can_be_built_from_a_collection_without_touching_the_database(): void
    {
        $calendar = HolidayCalendar::fromCollection(collect([
            new Holiday(['date' => '2026-05-01', 'type' => 'cuti_umum', 'recurs_annually' => false, 'is_active' => true]),
        ]));

        $this->assertTrue($calendar->isHoliday(CarbonImmutable::parse('2026-05-01')));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=HolidayCalendarTest`

Expected: FAIL with `Class "App\Models\Holiday" not found`.

- [ ] **Step 3: Write the migration, model and factory**

Create `database/migrations/2026_09_10_000003_create_holidays_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FR-ADM-02: public holidays and days on which nothing may be booked.
     */
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('name', 150);
            $table->enum('type', ['cuti_umum', 'hari_tanpa_tempahan'])->default('cuti_umum');
            $table->boolean('recurs_annually')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['date', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
```

Create `app/Models/Holiday.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    public const TYPE_PUBLIC = 'cuti_umum';

    public const TYPE_NO_BOOKING = 'hari_tanpa_tempahan';

    protected $fillable = [
        'date',
        'name',
        'type',
        'recurs_annually',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'recurs_annually' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function typeLabel(): string
    {
        return $this->type === self::TYPE_PUBLIC ? 'Cuti umum' : 'Hari tanpa tempahan';
    }
}
```

Create `database/factories/HolidayFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\Holiday;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Holiday>
 */
class HolidayFactory extends Factory
{
    protected $model = Holiday::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => $this->faker->unique()->dateTimeBetween('2026-01-01', '2026-12-31')->format('Y-m-d'),
            'name' => 'Cuti '.$this->faker->unique()->word(),
            'type' => Holiday::TYPE_PUBLIC,
            'recurs_annually' => false,
            'is_active' => true,
        ];
    }
}
```

- [ ] **Step 4: Write the calendar service**

Create `app/Services/Configuration/HolidayCalendar.php`:

```php
<?php

namespace App\Services\Configuration;

use App\Models\Holiday;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Calendar questions for M01 (FR-ADM-02). All matching happens in memory
 * against a collection, so the rules can be unit tested with no fixtures
 * and reused by the M05 booking engine without extra queries.
 */
class HolidayCalendar
{
    /**
     * @param  Collection<int, Holiday>|null  $holidays
     */
    public function __construct(private ?Collection $holidays = null) {}

    /**
     * Build a calendar around an already-loaded collection.
     *
     * @param  Collection<int, Holiday>  $holidays
     */
    public static function fromCollection(Collection $holidays): self
    {
        return new self($holidays);
    }

    /**
     * Whether the date is a public holiday.
     */
    public function isHoliday(CarbonInterface $date): bool
    {
        return $this->matches($date, Holiday::TYPE_PUBLIC);
    }

    /**
     * Whether bookings are allowed on this date. Public holidays and
     * explicitly blocked days both return false.
     */
    public function isBookable(CarbonInterface $date): bool
    {
        return ! $this->isHoliday($date) && ! $this->matches($date, Holiday::TYPE_NO_BOOKING);
    }

    private function matches(CarbonInterface $date, string $type): bool
    {
        return $this->entries()
            ->where('type', $type)
            ->contains(fn (Holiday $holiday) => $this->covers($holiday, $date));
    }

    private function covers(Holiday $holiday, CarbonInterface $date): bool
    {
        if (! $holiday->is_active) {
            return false;
        }

        if ($holiday->recurs_annually) {
            return $holiday->date->format('m-d') === $date->format('m-d');
        }

        return $holiday->date->isSameDay($date);
    }

    /**
     * @return Collection<int, Holiday>
     */
    private function entries(): Collection
    {
        return $this->holidays ??= Holiday::query()->active()->get();
    }
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=HolidayCalendarTest`

Expected: PASS, 7 tests.

- [ ] **Step 6: Checkpoint**

```bash
vendor/bin/pint --format agent
php artisan test
```

Expected: tiada ujian gagal.

---

## Task 7: Tab cuti umum

**Files:**
- Create: `app/Http/Requests/Admin/HolidayRequest.php`
- Create: `app/Http/Controllers/Admin/Settings/HolidayController.php`
- Create: `resources/views/admin/settings/holidays.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Admin/Settings/HolidayManagementTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Admin/Settings/HolidayManagementTest.php`:

```php
<?php

namespace Tests\Feature\Admin\Settings;

use App\Models\Holiday;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HolidayManagementTest extends TestCase
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

    public function test_the_tab_lists_holidays_for_the_selected_year(): void
    {
        Holiday::factory()->create(['date' => '2026-05-01', 'name' => 'Hari Pekerja']);
        Holiday::factory()->create(['date' => '2027-05-01', 'name' => 'Hari Pekerja 2027']);

        $this->actingAs($this->admin)
            ->get(route('admin.settings.holidays', ['year' => 2026]))
            ->assertOk()
            ->assertSee('Hari Pekerja')
            ->assertDontSee('Hari Pekerja 2027');
    }

    public function test_the_administrator_adds_a_holiday(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.settings.holidays.store'), [
                'date' => '2026-08-31',
                'name' => 'Hari Kebangsaan',
                'type' => 'cuti_umum',
                'recurs_annually' => 1,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.settings.holidays'));

        $this->assertDatabaseHas('holidays', ['name' => 'Hari Kebangsaan', 'recurs_annually' => true]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'holiday.created']);
    }

    public function test_the_same_date_and_type_may_not_be_added_twice(): void
    {
        Holiday::factory()->create(['date' => '2026-05-01', 'type' => 'cuti_umum']);

        $this->actingAs($this->admin)
            ->from(route('admin.settings.holidays'))
            ->post(route('admin.settings.holidays.store'), [
                'date' => '2026-05-01',
                'name' => 'Duplikat',
                'type' => 'cuti_umum',
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('date');
    }

    public function test_the_administrator_deletes_a_holiday(): void
    {
        $holiday = Holiday::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.settings.holidays.destroy', $holiday))
            ->assertRedirect(route('admin.settings.holidays'));

        $this->assertDatabaseMissing('holidays', ['id' => $holiday->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'holiday.deleted']);
    }

    public function test_a_viewer_may_read_but_not_write(): void
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('pentadbir-fasiliti');

        $this->actingAs($viewer)->get(route('admin.settings.holidays'))->assertOk();

        $this->actingAs($viewer)
            ->post(route('admin.settings.holidays.store'), [
                'date' => '2026-08-31',
                'name' => 'Hari Kebangsaan',
                'type' => 'cuti_umum',
                'is_active' => 1,
            ])
            ->assertForbidden();
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=HolidayManagementTest`

Expected: FAIL with `Route [admin.settings.holidays] not defined.`

- [ ] **Step 3: Write the form request**

Create `app/Http/Requests/Admin/HolidayRequest.php`:

```php
<?php

namespace App\Http\Requests\Admin;

use App\Models\Holiday;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HolidayRequest extends FormRequest
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
            'date' => [
                'required',
                'date_format:Y-m-d',
                Rule::unique('holidays', 'date')->where('type', $this->input('type')),
            ],
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', Rule::in([Holiday::TYPE_PUBLIC, Holiday::TYPE_NO_BOOKING])],
            'recurs_annually' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date.unique' => 'Tarikh ini sudah wujud bagi jenis yang sama.',
        ];
    }
}
```

- [ ] **Step 4: Write the controller**

Create `app/Http/Controllers/Admin/Settings/HolidayController.php`:

```php
<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HolidayRequest;
use App\Models\Holiday;
use App\Models\User;
use App\Services\Audit\AuditRecorder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * M01 — public holidays and non-bookable days (FR-ADM-02).
 */
class HolidayController extends Controller
{
    public function __construct(private readonly AuditRecorder $audit) {}

    public function index(Request $request): View
    {
        $year = (int) $request->integer('year', (int) date('Y'));

        return view('admin.settings.holidays', [
            'year' => $year,
            'holidays' => Holiday::query()
                ->where(fn ($query) => $query
                    ->whereYear('date', $year)
                    ->orWhere('recurs_annually', true))
                ->orderBy('date')
                ->get(),
        ]);
    }

    public function store(HolidayRequest $request): RedirectResponse
    {
        $holiday = Holiday::create([
            'date' => $request->string('date')->value(),
            'name' => $request->string('name')->value(),
            'type' => $request->string('type')->value(),
            'recurs_annually' => $request->boolean('recurs_annually'),
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->audit->record($this->actor(), 'holiday.created', $holiday, after: [
            'date' => $holiday->date->toDateString(),
            'name' => $holiday->name,
            'type' => $holiday->type,
        ]);

        return to_route('admin.settings.holidays')->with('status', 'Cuti telah ditambah.');
    }

    public function destroy(Holiday $holiday): RedirectResponse
    {
        $snapshot = [
            'date' => $holiday->date->toDateString(),
            'name' => $holiday->name,
            'type' => $holiday->type,
        ];

        $holiday->delete();

        $this->audit->record($this->actor(), 'holiday.deleted', $holiday, before: $snapshot);

        return to_route('admin.settings.holidays')->with('status', 'Cuti telah dipadam.');
    }

    private function actor(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
```

- [ ] **Step 5: Register the routes**

In `routes/web.php`, add the import:

```php
use App\Http\Controllers\Admin\Settings\HolidayController;
```

Then append inside the settings group:

```php
                Route::get('holidays', [HolidayController::class, 'index'])
                    ->middleware('can:tetapan.lihat')
                    ->name('holidays');

                Route::post('holidays', [HolidayController::class, 'store'])
                    ->middleware('can:tetapan.kemaskini')
                    ->name('holidays.store');

                Route::delete('holidays/{holiday}', [HolidayController::class, 'destroy'])
                    ->middleware('can:tetapan.kemaskini')
                    ->name('holidays.destroy');
```

- [ ] **Step 6: Write the view**

Create `resources/views/admin/settings/holidays.blade.php`:

```blade
@extends('layouts.app')

@section('title', 'Konfigurasi · Cuti Umum')

@section('content')
    <h2 class="mb-1 text-xl font-bold text-slate-900">Konfigurasi Sistem</h2>
    <p class="mb-4 text-sm text-slate-500">Cuti umum dan hari yang tidak boleh ditempah.</p>

    @include('admin.settings._tabs')

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                <h3 class="text-sm font-semibold text-slate-800">Tahun {{ $year }}</h3>
                <form method="GET" action="{{ route('admin.settings.holidays') }}" class="flex items-center gap-2">
                    <input type="number" name="year" value="{{ $year }}" min="2020" max="2100"
                           class="w-28 rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <button type="submit" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Papar
                    </button>
                </form>
            </div>

            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Tarikh</th>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Jenis</th>
                        <th class="px-4 py-3">Berulang</th>
                        <th class="px-4 py-3 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($holidays as $holiday)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono text-xs text-slate-700">{{ $holiday->date->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-slate-800">{{ $holiday->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $holiday->typeLabel() }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $holiday->recurs_annually ? 'Ya' : 'Tidak' }}</td>
                            <td class="px-4 py-3 text-right">
                                @can('tetapan.kemaskini')
                                    <form method="POST" action="{{ route('admin.settings.holidays.destroy', $holiday) }}"
                                          onsubmit="return confirm('Padam cuti ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-medium text-rose-600 hover:underline">Padam</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">Tiada cuti direkodkan bagi tahun ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @can('tetapan.kemaskini')
            <form method="POST" action="{{ route('admin.settings.holidays.store') }}"
                  class="h-fit rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                @csrf
                <h3 class="mb-4 text-sm font-semibold text-slate-800">Tambah cuti</h3>

                <div class="space-y-4">
                    <div>
                        <label for="date" class="block text-sm font-medium text-slate-700">Tarikh</label>
                        <input type="date" id="date" name="date" value="{{ old('date') }}" required
                               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('date')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700">Nama</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required maxlength="150"
                               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-slate-700">Jenis</label>
                        <select id="type" name="type" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="cuti_umum" @selected(old('type') === 'cuti_umum')>Cuti umum</option>
                            <option value="hari_tanpa_tempahan" @selected(old('type') === 'hari_tanpa_tempahan')>Hari tanpa tempahan</option>
                        </select>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="hidden" name="recurs_annually" value="0">
                        <input type="checkbox" name="recurs_annually" value="1" @checked(old('recurs_annually'))
                               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        Berulang setiap tahun
                    </label>

                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))
                               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        Aktif
                    </label>
                </div>

                <button type="submit" class="mt-6 w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Tambah
                </button>
            </form>
        @endcan
    </div>
@endsection
```

- [ ] **Step 7: Run test to verify it passes**

Run: `php artisan test --filter=HolidayManagementTest`

Expected: PASS, 5 tests.

- [ ] **Step 8: Checkpoint**

```bash
vendor/bin/pint --format agent
php artisan test
```

Expected: tiada ujian gagal.

---

Baki tugasan diteruskan dalam bahagian kedua pelan ini: `2026-09-08-m01-konfigurasi-sistem-bahagian-2.md`.
