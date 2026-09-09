# M01 Pentadbiran Sistem & Konfigurasi — Implementation Plan, Bahagian 2

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menyiapkan baki FR-ADM: peraturan tempahan per peranan, ambang daftar masuk, aras keutamaan tiket, senarai nilai rujukan, dan templat notifikasi.

**Prasyarat:** `2026-09-08-m01-konfigurasi-sistem.md` Task 1 hingga 7 mesti siap. Pelan ini menggunakan `SettingsRepository`, partial `admin/settings/_tabs.blade.php`, dan kumpulan laluan `Route::prefix('settings')->name('settings.')` yang dicipta di sana.

**Nota:** projek bukan repositori git. Setiap tugasan berakhir dengan `vendor/bin/pint --format agent` dan `php artisan test`. Syaratnya ialah tiada ujian gagal.

---

## Task 8: Peraturan tempahan per peranan, FR-ADM-03

**Files:**
- Create: `database/migrations/2026_09_10_000004_create_role_booking_rules_table.php`
- Create: `app/Models/RoleBookingRule.php`
- Create: `app/Http/Requests/Admin/RoleBookingRulesRequest.php`
- Create: `app/Http/Controllers/Admin/Settings/RoleBookingRuleController.php`
- Create: `resources/views/admin/settings/booking-rules.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Admin/Settings/RoleBookingRulesTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Admin/Settings/RoleBookingRulesTest.php`:

```php
<?php

namespace Tests\Feature\Admin\Settings;

use App\Models\RoleBookingRule;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleBookingRulesTest extends TestCase
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
        $rules = [];

        foreach (Role::all() as $role) {
            $rules[$role->id] = [
                'min_duration_minutes' => 30,
                'max_duration_minutes' => 240,
                'max_advance_days' => 90,
            ];
        }

        foreach ($overrides as $roleId => $values) {
            $rules[$roleId] = array_merge($rules[$roleId], $values);
        }

        return ['rules' => $rules];
    }

    public function test_the_tab_lists_every_role(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.settings.booking-rules'))
            ->assertOk()
            ->assertSee('kakitangan')
            ->assertSee('pentadbir-fasiliti');
    }

    public function test_the_administrator_saves_a_rule_for_every_role(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.settings.booking-rules.update'), $this->payload())
            ->assertRedirect(route('admin.settings.booking-rules'));

        $this->assertSame(8, RoleBookingRule::query()->count());
        $this->assertDatabaseHas('role_booking_rules', ['min_duration_minutes' => 30, 'max_duration_minutes' => 240]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'booking_rules.updated']);
    }

    public function test_the_minimum_duration_must_be_shorter_than_the_maximum(): void
    {
        $roleId = Role::findByName('kakitangan')->id;

        $this->actingAs($this->admin)
            ->from(route('admin.settings.booking-rules'))
            ->put(
                route('admin.settings.booking-rules.update'),
                $this->payload([$roleId => ['min_duration_minutes' => 300, 'max_duration_minutes' => 120]])
            )
            ->assertSessionHasErrors("rules.{$roleId}.max_duration_minutes");
    }

    public function test_saving_twice_updates_rather_than_duplicates(): void
    {
        $roleId = Role::findByName('kakitangan')->id;

        $this->actingAs($this->admin)->put(route('admin.settings.booking-rules.update'), $this->payload());
        $this->actingAs($this->admin)->put(
            route('admin.settings.booking-rules.update'),
            $this->payload([$roleId => ['max_advance_days' => 30]])
        );

        $this->assertSame(8, RoleBookingRule::query()->count());
        $this->assertSame(30, RoleBookingRule::query()->where('role_id', $roleId)->value('max_advance_days'));
    }

    public function test_a_viewer_may_not_save(): void
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('pegawai-aset');

        $this->actingAs($viewer)
            ->put(route('admin.settings.booking-rules.update'), $this->payload())
            ->assertForbidden();
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=RoleBookingRulesTest`

Expected: FAIL with `Route [admin.settings.booking-rules] not defined.`

- [ ] **Step 3: Write the migration and model**

Create `database/migrations/2026_09_10_000004_create_role_booking_rules_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FR-ADM-03: minimum duration, maximum duration and how far ahead each
     * role may book. Consumed by the M05 booking engine.
     */
    public function up(): void
    {
        Schema::create('role_booking_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->unique()->constrained('roles')->cascadeOnDelete();
            $table->unsignedInteger('min_duration_minutes')->default(30);
            $table->unsignedInteger('max_duration_minutes')->default(240);
            $table->unsignedInteger('max_advance_days')->default(90);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_booking_rules');
    }
};
```

Create `app/Models/RoleBookingRule.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role;

class RoleBookingRule extends Model
{
    protected $fillable = [
        'role_id',
        'min_duration_minutes',
        'max_duration_minutes',
        'max_advance_days',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_duration_minutes' => 'integer',
            'max_duration_minutes' => 'integer',
            'max_advance_days' => 'integer',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
```

- [ ] **Step 4: Write the form request**

Create `app/Http/Requests/Admin/RoleBookingRulesRequest.php`:

```php
<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class RoleBookingRulesRequest extends FormRequest
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
            'rules' => ['required', 'array'],
            'rules.*.min_duration_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
            'rules.*.max_duration_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
            'rules.*.max_advance_days' => ['required', 'integer', 'min:1', 'max:730'],
        ];
    }

    /**
     * SRS §M01: the minimum duration must be shorter than the maximum.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                foreach ((array) $this->input('rules', []) as $roleId => $values) {
                    $min = (int) ($values['min_duration_minutes'] ?? 0);
                    $max = (int) ($values['max_duration_minutes'] ?? 0);

                    if ($min >= $max) {
                        $validator->errors()->add(
                            "rules.{$roleId}.max_duration_minutes",
                            'Tempoh maksimum mesti lebih panjang daripada tempoh minimum.'
                        );
                    }
                }
            },
        ];
    }
}
```

- [ ] **Step 5: Write the controller**

Create `app/Http/Controllers/Admin/Settings/RoleBookingRuleController.php`:

```php
<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleBookingRulesRequest;
use App\Models\RoleBookingRule;
use App\Models\User;
use App\Services\Audit\AuditRecorder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

/**
 * M01 — booking limits per role (FR-ADM-03). Consumed by M05.
 */
class RoleBookingRuleController extends Controller
{
    public function __construct(private readonly AuditRecorder $audit) {}

    public function edit(): View
    {
        $existing = RoleBookingRule::query()->get()->keyBy('role_id');

        $rows = Role::query()->orderBy('name')->get()->map(fn (Role $role) => [
            'id' => $role->id,
            'name' => $role->name,
            'min_duration_minutes' => $existing[$role->id]->min_duration_minutes ?? 30,
            'max_duration_minutes' => $existing[$role->id]->max_duration_minutes ?? 240,
            'max_advance_days' => $existing[$role->id]->max_advance_days ?? 90,
        ]);

        return view('admin.settings.booking-rules', ['rows' => $rows]);
    }

    public function update(RoleBookingRulesRequest $request): RedirectResponse
    {
        $before = RoleBookingRule::query()->get()->mapWithKeys(fn (RoleBookingRule $rule) => [
            (string) $rule->role_id => $rule->min_duration_minutes.'/'.$rule->max_duration_minutes.'/'.$rule->max_advance_days,
        ])->all();

        $after = [];

        DB::transaction(function () use ($request, &$after): void {
            foreach ((array) $request->input('rules') as $roleId => $values) {
                RoleBookingRule::query()->updateOrCreate(
                    ['role_id' => (int) $roleId],
                    [
                        'min_duration_minutes' => (int) $values['min_duration_minutes'],
                        'max_duration_minutes' => (int) $values['max_duration_minutes'],
                        'max_advance_days' => (int) $values['max_advance_days'],
                    ]
                );

                $after[(string) $roleId] = $values['min_duration_minutes'].'/'.$values['max_duration_minutes'].'/'.$values['max_advance_days'];
            }
        });

        /** @var User $actor */
        $actor = Auth::user();

        $this->audit->record(
            $actor,
            'booking_rules.updated',
            RoleBookingRule::query()->orderBy('id')->firstOrFail(),
            before: $before,
            after: $after,
        );

        return to_route('admin.settings.booking-rules')
            ->with('status', 'Peraturan tempahan telah disimpan.');
    }
}
```

- [ ] **Step 6: Register the routes**

In `routes/web.php`, add the import:

```php
use App\Http\Controllers\Admin\Settings\RoleBookingRuleController;
```

Then append inside the settings group:

```php
                Route::get('booking-rules', [RoleBookingRuleController::class, 'edit'])
                    ->middleware('can:tetapan.lihat')
                    ->name('booking-rules');

                Route::put('booking-rules', [RoleBookingRuleController::class, 'update'])
                    ->middleware('can:tetapan.kemaskini')
                    ->name('booking-rules.update');
```

- [ ] **Step 7: Write the view**

Create `resources/views/admin/settings/booking-rules.blade.php`:

```blade
@extends('layouts.app')

@section('title', 'Konfigurasi · Peraturan Tempahan')

@section('content')
    <h2 class="mb-1 text-xl font-bold text-slate-900">Konfigurasi Sistem</h2>
    <p class="mb-4 text-sm text-slate-500">Had tempoh dan tempoh awalan tempahan bagi setiap peranan. Digunakan oleh enjin tempahan (M05).</p>

    @include('admin.settings._tabs')

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.booking-rules.update') }}"
          class="overflow-x-auto rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')

        <table class="min-w-full text-sm">
            <thead class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <tr>
                    <th class="py-2">Peranan</th>
                    <th class="py-2">Tempoh minimum (minit)</th>
                    <th class="py-2">Tempoh maksimum (minit)</th>
                    <th class="py-2">Tempoh awalan maksimum (hari)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($rows as $row)
                    <tr>
                        <td class="py-3 font-medium text-slate-800">{{ $row['name'] }}</td>
                        <td class="py-3">
                            <input type="number" min="5" max="1440" required
                                   name="rules[{{ $row['id'] }}][min_duration_minutes]"
                                   value="{{ old("rules.{$row['id']}.min_duration_minutes", $row['min_duration_minutes']) }}"
                                   @disabled(! auth()->user()->can('tetapan.kemaskini'))
                                   class="w-28 rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </td>
                        <td class="py-3">
                            <input type="number" min="5" max="1440" required
                                   name="rules[{{ $row['id'] }}][max_duration_minutes]"
                                   value="{{ old("rules.{$row['id']}.max_duration_minutes", $row['max_duration_minutes']) }}"
                                   @disabled(! auth()->user()->can('tetapan.kemaskini'))
                                   class="w-28 rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error("rules.{$row['id']}.max_duration_minutes")<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </td>
                        <td class="py-3">
                            <input type="number" min="1" max="730" required
                                   name="rules[{{ $row['id'] }}][max_advance_days]"
                                   value="{{ old("rules.{$row['id']}.max_advance_days", $row['max_advance_days']) }}"
                                   @disabled(! auth()->user()->can('tetapan.kemaskini'))
                                   class="w-28 rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
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

Run: `php artisan test --filter=RoleBookingRulesTest`

Expected: PASS, 5 tests.

- [ ] **Step 9: Checkpoint**

```bash
vendor/bin/pint --format agent
php artisan test
```

Expected: tiada ujian gagal.

---

## Task 9: Tab daftar masuk, FR-ADM-04

**Files:**
- Create: `app/Http/Controllers/Admin/Settings/CheckinSettingsController.php`
- Create: `resources/views/admin/settings/checkin.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Admin/Settings/CheckinSettingsTest.php`

Dua nombor sahaja, jadi ia disimpan sebagai tetapan skalar, bukan jadual.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Admin/Settings/CheckinSettingsTest.php`:

```php
<?php

namespace Tests\Feature\Admin\Settings;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckinSettingsTest extends TestCase
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

    public function test_the_tab_renders_with_defaults(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.settings.checkin'))
            ->assertOk()
            ->assertSee('Ambang daftar masuk');
    }

    public function test_the_administrator_saves_both_thresholds(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.settings.checkin.update'), [
                'checkin_threshold_minutes' => 10,
                'checkin_grace_minutes' => 20,
            ])
            ->assertRedirect(route('admin.settings.checkin'));

        $this->assertSame(10, setting('checkin.threshold_minutes'));
        $this->assertSame(20, setting('checkin.grace_minutes'));
        $this->assertDatabaseHas('audit_logs', ['action' => 'setting.updated']);
    }

    public function test_the_thresholds_must_be_positive_whole_numbers(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.settings.checkin'))
            ->put(route('admin.settings.checkin.update'), [
                'checkin_threshold_minutes' => 0,
                'checkin_grace_minutes' => -5,
            ])
            ->assertSessionHasErrors(['checkin_threshold_minutes', 'checkin_grace_minutes']);
    }

    public function test_a_viewer_may_not_save(): void
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('penyelia-ict');

        $this->actingAs($viewer)
            ->put(route('admin.settings.checkin.update'), [
                'checkin_threshold_minutes' => 10,
                'checkin_grace_minutes' => 20,
            ])
            ->assertForbidden();
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=CheckinSettingsTest`

Expected: FAIL with `Route [admin.settings.checkin] not defined.`

- [ ] **Step 3: Write the controller**

Create `app/Http/Controllers/Admin/Settings/CheckinSettingsController.php`:

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
 * M01 — check-in threshold and grace period (FR-ADM-04). Consumed by M07.
 */
class CheckinSettingsController extends Controller
{
    public function __construct(private readonly SettingsRepository $settings) {}

    public function edit(): View
    {
        return view('admin.settings.checkin', [
            'threshold' => $this->settings->get('checkin.threshold_minutes', 15),
            'grace' => $this->settings->get('checkin.grace_minutes', 15),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'checkin_threshold_minutes' => ['required', 'integer', 'min:1', 'max:240'],
            'checkin_grace_minutes' => ['required', 'integer', 'min:1', 'max:240'],
        ]);

        /** @var User $actor */
        $actor = Auth::user();

        $this->settings->set('checkin.threshold_minutes', $validated['checkin_threshold_minutes'], $actor, 'nombor', 'daftar-masuk');
        $this->settings->set('checkin.grace_minutes', $validated['checkin_grace_minutes'], $actor, 'nombor', 'daftar-masuk');

        return to_route('admin.settings.checkin')->with('status', 'Tetapan daftar masuk telah disimpan.');
    }
}
```

- [ ] **Step 4: Register the routes**

In `routes/web.php`, add the import:

```php
use App\Http\Controllers\Admin\Settings\CheckinSettingsController;
```

Then append inside the settings group:

```php
                Route::get('checkin', [CheckinSettingsController::class, 'edit'])
                    ->middleware('can:tetapan.lihat')
                    ->name('checkin');

                Route::put('checkin', [CheckinSettingsController::class, 'update'])
                    ->middleware('can:tetapan.kemaskini')
                    ->name('checkin.update');
```

- [ ] **Step 5: Write the view**

Create `resources/views/admin/settings/checkin.blade.php`:

```blade
@extends('layouts.app')

@section('title', 'Konfigurasi · Daftar Masuk')

@section('content')
    <h2 class="mb-1 text-xl font-bold text-slate-900">Konfigurasi Sistem</h2>
    <p class="mb-4 text-sm text-slate-500">Ambang pengesahan kehadiran dan tempoh anjal sebelum tempahan dilepaskan. Digunakan oleh modul daftar masuk (M07).</p>

    @include('admin.settings._tabs')

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.checkin.update') }}"
          class="max-w-2xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')

        <div class="space-y-4">
            <div>
                <label for="checkin_threshold_minutes" class="block text-sm font-medium text-slate-700">Ambang daftar masuk (minit)</label>
                <input type="number" id="checkin_threshold_minutes" name="checkin_threshold_minutes" min="1" max="240" required
                       value="{{ old('checkin_threshold_minutes', $threshold) }}"
                       @disabled(! auth()->user()->can('tetapan.kemaskini'))
                       class="mt-1 block w-40 rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <p class="mt-1 text-xs text-slate-500">Berapa lama selepas waktu mula penempah masih boleh mengesahkan kehadiran.</p>
                @error('checkin_threshold_minutes')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="checkin_grace_minutes" class="block text-sm font-medium text-slate-700">Tempoh anjal sebelum pelepasan (minit)</label>
                <input type="number" id="checkin_grace_minutes" name="checkin_grace_minutes" min="1" max="240" required
                       value="{{ old('checkin_grace_minutes', $grace) }}"
                       @disabled(! auth()->user()->can('tetapan.kemaskini'))
                       class="mt-1 block w-40 rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <p class="mt-1 text-xs text-slate-500">Selepas tempoh ini tanpa pengesahan, bilik dilepaskan semula.</p>
                @error('checkin_grace_minutes')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
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

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test --filter=CheckinSettingsTest`

Expected: PASS, 4 tests.

- [ ] **Step 7: Checkpoint**

```bash
vendor/bin/pint --format agent
php artisan test
```

Expected: tiada ujian gagal.

---

## Task 10: Aras keutamaan tiket, FR-ADM-05

**Files:**
- Create: `database/migrations/2026_09_10_000005_create_ticket_priorities_table.php`
- Create: `app/Models/TicketPriority.php`
- Create: `database/factories/TicketPriorityFactory.php`
- Create: `app/Http/Requests/Admin/TicketPriorityRequest.php`
- Create: `app/Http/Controllers/Admin/Settings/TicketPriorityController.php`
- Create: `resources/views/admin/settings/ticket-priorities.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Admin/Settings/TicketPriorityTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Admin/Settings/TicketPriorityTest.php`:

```php
<?php

namespace Tests\Feature\Admin\Settings;

use App\Models\TicketPriority;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketPriorityTest extends TestCase
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

    public function test_the_tab_lists_priorities(): void
    {
        TicketPriority::factory()->create(['code' => 'KRITIKAL', 'label' => 'Kritikal']);

        $this->actingAs($this->admin)
            ->get(route('admin.settings.ticket-priorities'))
            ->assertOk()
            ->assertSee('Kritikal');
    }

    public function test_the_administrator_adds_a_priority(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.settings.ticket-priorities.store'), [
                'code' => 'TINGGI',
                'label' => 'Tinggi',
                'response_target_minutes' => 60,
                'resolution_target_minutes' => 480,
                'sort_order' => 2,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.settings.ticket-priorities'));

        $this->assertDatabaseHas('ticket_priorities', ['code' => 'TINGGI', 'resolution_target_minutes' => 480]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'ticket_priority.created']);
    }

    public function test_the_resolution_target_must_exceed_the_response_target(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.settings.ticket-priorities'))
            ->post(route('admin.settings.ticket-priorities.store'), [
                'code' => 'SALAH',
                'label' => 'Salah',
                'response_target_minutes' => 480,
                'resolution_target_minutes' => 60,
                'sort_order' => 1,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('resolution_target_minutes');
    }

    public function test_the_code_must_be_unique(): void
    {
        TicketPriority::factory()->create(['code' => 'TINGGI']);

        $this->actingAs($this->admin)
            ->from(route('admin.settings.ticket-priorities'))
            ->post(route('admin.settings.ticket-priorities.store'), [
                'code' => 'TINGGI',
                'label' => 'Duplikat',
                'response_target_minutes' => 30,
                'resolution_target_minutes' => 60,
                'sort_order' => 1,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('code');
    }

    public function test_the_administrator_updates_a_priority(): void
    {
        $priority = TicketPriority::factory()->create(['response_target_minutes' => 60]);

        $this->actingAs($this->admin)
            ->put(route('admin.settings.ticket-priorities.update', $priority), [
                'code' => $priority->code,
                'label' => $priority->label,
                'response_target_minutes' => 30,
                'resolution_target_minutes' => 240,
                'sort_order' => 1,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.settings.ticket-priorities'));

        $this->assertSame(30, $priority->fresh()->response_target_minutes);
        $this->assertDatabaseHas('audit_logs', ['action' => 'ticket_priority.updated']);
    }

    public function test_a_viewer_may_not_write(): void
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('penyelia-ict');

        $this->actingAs($viewer)->get(route('admin.settings.ticket-priorities'))->assertOk();

        $this->actingAs($viewer)
            ->post(route('admin.settings.ticket-priorities.store'), [
                'code' => 'BARU',
                'label' => 'Baru',
                'response_target_minutes' => 30,
                'resolution_target_minutes' => 60,
                'sort_order' => 1,
                'is_active' => 1,
            ])
            ->assertForbidden();
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=TicketPriorityTest`

Expected: FAIL with `Class "App\Models\TicketPriority" not found`.

- [ ] **Step 3: Write the migration, model and factory**

Create `database/migrations/2026_09_10_000005_create_ticket_priorities_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FR-ADM-05: priority levels with response and resolution targets.
     * Consumed by the M10 SLA calculator.
     */
    public function up(): void
    {
        Schema::create('ticket_priorities', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('label', 100);
            $table->unsignedInteger('response_target_minutes');
            $table->unsignedInteger('resolution_target_minutes');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_priorities');
    }
};
```

Create `app/Models/TicketPriority.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketPriority extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'label',
        'response_target_minutes',
        'resolution_target_minutes',
        'sort_order',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'response_target_minutes' => 'integer',
            'resolution_target_minutes' => 'integer',
            'sort_order' => 'integer',
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
}
```

Create `database/factories/TicketPriorityFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\TicketPriority;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketPriority>
 */
class TicketPriorityFactory extends Factory
{
    protected $model = TicketPriority::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->bothify('PRI-##')),
            'label' => 'Keutamaan '.$this->faker->unique()->word(),
            'response_target_minutes' => 60,
            'resolution_target_minutes' => 480,
            'sort_order' => 1,
            'is_active' => true,
        ];
    }
}
```

- [ ] **Step 4: Write the form request**

Create `app/Http/Requests/Admin/TicketPriorityRequest.php`:

```php
<?php

namespace App\Http\Requests\Admin;

use App\Models\TicketPriority;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TicketPriorityRequest extends FormRequest
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
        /** @var TicketPriority|null $priority */
        $priority = $this->route('ticket_priority');

        return [
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('ticket_priorities', 'code')->ignore($priority?->id),
            ],
            'label' => ['required', 'string', 'max:100'],
            'response_target_minutes' => ['required', 'integer', 'min:5', 'max:20160'],
            'resolution_target_minutes' => ['required', 'integer', 'min:5', 'max:20160'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * SRS §M01: the resolution target must be larger than the response target.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $response = (int) $this->input('response_target_minutes');
                $resolution = (int) $this->input('resolution_target_minutes');

                if ($resolution <= $response) {
                    $validator->errors()->add(
                        'resolution_target_minutes',
                        'Sasaran masa pemulihan mesti lebih besar daripada sasaran masa tindak balas.'
                    );
                }
            },
        ];
    }
}
```

- [ ] **Step 5: Write the controller**

Create `app/Http/Controllers/Admin/Settings/TicketPriorityController.php`:

```php
<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TicketPriorityRequest;
use App\Models\TicketPriority;
use App\Models\User;
use App\Services\Audit\AuditRecorder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * M01 — ticket priority levels and SLA targets (FR-ADM-05). Consumed by M10.
 */
class TicketPriorityController extends Controller
{
    public function __construct(private readonly AuditRecorder $audit) {}

    public function index(): View
    {
        return view('admin.settings.ticket-priorities', [
            'priorities' => TicketPriority::query()->orderBy('sort_order')->orderBy('code')->get(),
        ]);
    }

    public function store(TicketPriorityRequest $request): RedirectResponse
    {
        $priority = TicketPriority::create($this->payload($request));

        $this->audit->record($this->actor(), 'ticket_priority.created', $priority, after: $priority->only([
            'code', 'label', 'response_target_minutes', 'resolution_target_minutes',
        ]));

        return to_route('admin.settings.ticket-priorities')->with('status', 'Aras keutamaan telah ditambah.');
    }

    public function update(TicketPriorityRequest $request, TicketPriority $ticketPriority): RedirectResponse
    {
        $before = $ticketPriority->only(['code', 'label', 'response_target_minutes', 'resolution_target_minutes', 'is_active']);

        $ticketPriority->update($this->payload($request));

        $this->audit->record(
            $this->actor(),
            'ticket_priority.updated',
            $ticketPriority,
            before: $before,
            after: $ticketPriority->only(['code', 'label', 'response_target_minutes', 'resolution_target_minutes', 'is_active']),
        );

        return to_route('admin.settings.ticket-priorities')->with('status', 'Aras keutamaan telah dikemas kini.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(TicketPriorityRequest $request): array
    {
        return [
            'code' => $request->string('code')->upper()->value(),
            'label' => $request->string('label')->value(),
            'response_target_minutes' => (int) $request->integer('response_target_minutes'),
            'resolution_target_minutes' => (int) $request->integer('resolution_target_minutes'),
            'sort_order' => (int) $request->integer('sort_order'),
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function actor(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
```

- [ ] **Step 6: Register the routes**

In `routes/web.php`, add the import:

```php
use App\Http\Controllers\Admin\Settings\TicketPriorityController;
```

Then append inside the settings group:

```php
                Route::get('ticket-priorities', [TicketPriorityController::class, 'index'])
                    ->middleware('can:tetapan.lihat')
                    ->name('ticket-priorities');

                Route::post('ticket-priorities', [TicketPriorityController::class, 'store'])
                    ->middleware('can:tetapan.kemaskini')
                    ->name('ticket-priorities.store');

                Route::put('ticket-priorities/{ticket_priority}', [TicketPriorityController::class, 'update'])
                    ->middleware('can:tetapan.kemaskini')
                    ->name('ticket-priorities.update');
```

- [ ] **Step 7: Write the view**

Create `resources/views/admin/settings/ticket-priorities.blade.php`:

```blade
@extends('layouts.app')

@section('title', 'Konfigurasi · Keutamaan Tiket')

@section('content')
    <h2 class="mb-1 text-xl font-bold text-slate-900">Konfigurasi Sistem</h2>
    <p class="mb-4 text-sm text-slate-500">Aras keutamaan tiket dan sasaran SLA. Digunakan oleh modul tiket (M10).</p>

    @include('admin.settings._tabs')

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="space-y-4">
        @foreach ($priorities as $priority)
            <form method="POST" action="{{ route('admin.settings.ticket-priorities.update', $priority) }}"
                  class="grid gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-6 sm:items-end">
                @csrf
                @method('PUT')

                <div class="sm:col-span-1">
                    <label class="block text-xs font-medium text-slate-500">Kod</label>
                    <input type="text" name="code" value="{{ $priority->code }}" required maxlength="30"
                           @disabled(! auth()->user()->can('tetapan.kemaskini'))
                           class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-slate-500">Label</label>
                    <input type="text" name="label" value="{{ $priority->label }}" required maxlength="100"
                           @disabled(! auth()->user()->can('tetapan.kemaskini'))
                           class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-500">Tindak balas (minit)</label>
                    <input type="number" name="response_target_minutes" value="{{ $priority->response_target_minutes }}" required min="5"
                           @disabled(! auth()->user()->can('tetapan.kemaskini'))
                           class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-500">Pemulihan (minit)</label>
                    <input type="number" name="resolution_target_minutes" value="{{ $priority->resolution_target_minutes }}" required min="5"
                           @disabled(! auth()->user()->can('tetapan.kemaskini'))
                           class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm">
                </div>

                <div class="flex items-center gap-3">
                    <input type="hidden" name="sort_order" value="{{ $priority->sort_order }}">
                    <label class="flex items-center gap-1 text-xs text-slate-600">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked($priority->is_active)
                               @disabled(! auth()->user()->can('tetapan.kemaskini'))
                               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        Aktif
                    </label>
                    @can('tetapan.kemaskini')
                        <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500">
                            Simpan
                        </button>
                    @endcan
                </div>
            </form>
        @endforeach

        @if ($priorities->isEmpty())
            <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-500 shadow-sm">
                Tiada aras keutamaan ditetapkan lagi.
            </div>
        @endif
    </div>

    @can('tetapan.kemaskini')
        <form method="POST" action="{{ route('admin.settings.ticket-priorities.store') }}"
              class="mt-6 max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            <h3 class="mb-4 text-sm font-semibold text-slate-800">Tambah aras keutamaan</h3>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="code" class="block text-sm font-medium text-slate-700">Kod</label>
                    <input type="text" id="code" name="code" value="{{ old('code') }}" required maxlength="30"
                           class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('code')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="label" class="block text-sm font-medium text-slate-700">Label</label>
                    <input type="text" id="label" name="label" value="{{ old('label') }}" required maxlength="100"
                           class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('label')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="response_target_minutes" class="block text-sm font-medium text-slate-700">Sasaran tindak balas (minit)</label>
                    <input type="number" id="response_target_minutes" name="response_target_minutes"
                           value="{{ old('response_target_minutes', 60) }}" required min="5"
                           class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="resolution_target_minutes" class="block text-sm font-medium text-slate-700">Sasaran pemulihan (minit)</label>
                    <input type="number" id="resolution_target_minutes" name="resolution_target_minutes"
                           value="{{ old('resolution_target_minutes', 480) }}" required min="5"
                           class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('resolution_target_minutes')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="sort_order" class="block text-sm font-medium text-slate-700">Susunan</label>
                    <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', 1) }}" min="0"
                           class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <label class="flex items-center gap-2 self-end text-sm text-slate-700">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))
                           class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    Aktif
                </label>
            </div>

            <button type="submit" class="mt-6 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                Tambah
            </button>
        </form>
    @endcan
@endsection
```

- [ ] **Step 8: Run test to verify it passes**

Run: `php artisan test --filter=TicketPriorityTest`

Expected: PASS, 6 tests.

- [ ] **Step 9: Checkpoint**

```bash
vendor/bin/pint --format agent
php artisan test
```

Expected: tiada ujian gagal.

---

## Task 11: Senarai nilai rujukan, FR-ADM-07

**Files:**
- Create: `app/Enums/ReferenceValueType.php`
- Create: `database/migrations/2026_09_10_000006_create_reference_values_table.php`
- Create: `app/Models/ReferenceValue.php`
- Create: `database/factories/ReferenceValueFactory.php`
- Create: `app/Http/Requests/Admin/ReferenceValueRequest.php`
- Create: `app/Http/Controllers/Admin/Settings/ReferenceValueController.php`
- Create: `resources/views/admin/settings/reference-values.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Admin/Settings/ReferenceValueTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Admin/Settings/ReferenceValueTest.php`:

```php
<?php

namespace Tests\Feature\Admin\Settings;

use App\Models\ReferenceValue;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferenceValueTest extends TestCase
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

    public function test_the_tab_defaults_to_the_first_list(): void
    {
        ReferenceValue::factory()->create(['type' => 'kategori_aset', 'label' => 'Komputer Riba']);

        $this->actingAs($this->admin)
            ->get(route('admin.settings.reference-values'))
            ->assertOk()
            ->assertSee('Komputer Riba');
    }

    public function test_the_tab_can_switch_between_lists(): void
    {
        ReferenceValue::factory()->create(['type' => 'kategori_aset', 'label' => 'Komputer Riba']);
        ReferenceValue::factory()->create(['type' => 'unit_stok', 'label' => 'Kotak']);

        $this->actingAs($this->admin)
            ->get(route('admin.settings.reference-values', ['type' => 'unit_stok']))
            ->assertOk()
            ->assertSee('Kotak')
            ->assertDontSee('Komputer Riba');
    }

    public function test_an_unknown_list_type_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.settings.reference-values', ['type' => 'tiada_senarai']))
            ->assertNotFound();
    }

    public function test_the_administrator_adds_a_value(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.settings.reference-values.store'), [
                'type' => 'jenis_kerosakan',
                'code' => 'SKRIN',
                'label' => 'Skrin rosak',
                'sort_order' => 1,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.settings.reference-values', ['type' => 'jenis_kerosakan']));

        $this->assertDatabaseHas('reference_values', ['type' => 'jenis_kerosakan', 'code' => 'SKRIN']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'reference_value.created']);
    }

    public function test_the_code_must_be_unique_within_its_list(): void
    {
        ReferenceValue::factory()->create(['type' => 'unit_stok', 'code' => 'KOTAK']);

        $this->actingAs($this->admin)
            ->from(route('admin.settings.reference-values', ['type' => 'unit_stok']))
            ->post(route('admin.settings.reference-values.store'), [
                'type' => 'unit_stok',
                'code' => 'KOTAK',
                'label' => 'Duplikat',
                'sort_order' => 1,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('code');
    }

    public function test_the_same_code_is_allowed_in_a_different_list(): void
    {
        ReferenceValue::factory()->create(['type' => 'unit_stok', 'code' => 'UNIT']);

        $this->actingAs($this->admin)
            ->post(route('admin.settings.reference-values.store'), [
                'type' => 'kemudahan_bilik',
                'code' => 'UNIT',
                'label' => 'Unit penyaman udara',
                'sort_order' => 1,
                'is_active' => 1,
            ])
            ->assertSessionHasNoErrors();
    }

    public function test_a_value_is_deactivated_rather_than_deleted(): void
    {
        $value = ReferenceValue::factory()->create(['is_active' => true]);

        $this->actingAs($this->admin)
            ->patch(route('admin.settings.reference-values.toggle', $value))
            ->assertRedirect(route('admin.settings.reference-values', ['type' => $value->type]));

        $this->assertFalse($value->fresh()->is_active);
        $this->assertDatabaseHas('audit_logs', ['action' => 'reference_value.deactivated']);
    }

    public function test_a_viewer_may_not_write(): void
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('pegawai-aset');

        $this->actingAs($viewer)->get(route('admin.settings.reference-values'))->assertOk();

        $this->actingAs($viewer)
            ->post(route('admin.settings.reference-values.store'), [
                'type' => 'unit_stok',
                'code' => 'BARU',
                'label' => 'Baru',
                'sort_order' => 1,
                'is_active' => 1,
            ])
            ->assertForbidden();
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ReferenceValueTest`

Expected: FAIL with `Class "App\Models\ReferenceValue" not found`.

- [ ] **Step 3: Write the enum**

Create `app/Enums/ReferenceValueType.php`:

```php
<?php

namespace App\Enums;

/**
 * The five editable reference lists required by FR-ADM-07.
 */
enum ReferenceValueType: string
{
    case KategoriAset = 'kategori_aset';
    case JenisKerosakan = 'jenis_kerosakan';
    case SusunAturBilik = 'susun_atur_bilik';
    case KemudahanBilik = 'kemudahan_bilik';
    case UnitStok = 'unit_stok';

    public function label(): string
    {
        return match ($this) {
            self::KategoriAset => 'Kategori aset',
            self::JenisKerosakan => 'Jenis kerosakan',
            self::SusunAturBilik => 'Susun atur bilik',
            self::KemudahanBilik => 'Kemudahan bilik',
            self::UnitStok => 'Unit stok',
        };
    }

    /**
     * The module that consumes this list, shown as a hint in the UI.
     */
    public function consumer(): string
    {
        return match ($this) {
            self::KategoriAset => 'M09 Inventari Aset',
            self::JenisKerosakan => 'M10 Tiket Aduan',
            self::SusunAturBilik, self::KemudahanBilik => 'M04 Katalog Bilik',
            self::UnitStok => 'M13 Alat Ganti & Stok',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $type) => $type->value, self::cases());
    }
}
```

- [ ] **Step 4: Write the migration, model and factory**

Create `database/migrations/2026_09_10_000006_create_reference_values_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FR-ADM-07: editable reference lists shared by several modules.
     */
    public function up(): void
    {
        Schema::create('reference_values', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50)->index();
            $table->string('code', 50);
            $table->string('label', 150);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['type', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reference_values');
    }
};
```

Create `app/Models/ReferenceValue.php`:

```php
<?php

namespace App\Models;

use App\Enums\ReferenceValueType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferenceValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'code',
        'label',
        'sort_order',
        'is_active',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeOfType(Builder $query, ReferenceValueType $type): void
    {
        $query->where('type', $type->value);
    }
}
```

Create `database/factories/ReferenceValueFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\ReferenceValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReferenceValue>
 */
class ReferenceValueFactory extends Factory
{
    protected $model = ReferenceValue::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'kategori_aset',
            'code' => strtoupper($this->faker->unique()->bothify('REF-###')),
            'label' => ucfirst($this->faker->unique()->words(2, true)),
            'sort_order' => 1,
            'is_active' => true,
            'metadata' => null,
        ];
    }
}
```

- [ ] **Step 5: Write the form request**

Create `app/Http/Requests/Admin/ReferenceValueRequest.php`:

```php
<?php

namespace App\Http\Requests\Admin;

use App\Enums\ReferenceValueType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReferenceValueRequest extends FormRequest
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
            'type' => ['required', Rule::enum(ReferenceValueType::class)],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('reference_values', 'code')->where('type', $this->input('type')),
            ],
            'label' => ['required', 'string', 'max:150'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.unique' => 'Kod ini sudah wujud dalam senarai yang sama.',
        ];
    }
}
```

- [ ] **Step 6: Write the controller**

Create `app/Http/Controllers/Admin/Settings/ReferenceValueController.php`:

```php
<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Enums\ReferenceValueType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReferenceValueRequest;
use App\Models\ReferenceValue;
use App\Models\User;
use App\Services\Audit\AuditRecorder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * M01 — editable reference lists (FR-ADM-07).
 */
class ReferenceValueController extends Controller
{
    public function __construct(private readonly AuditRecorder $audit) {}

    public function index(Request $request): View
    {
        $type = $this->resolveType($request->string('type')->value());

        return view('admin.settings.reference-values', [
            'types' => ReferenceValueType::cases(),
            'activeType' => $type,
            'values' => ReferenceValue::query()
                ->ofType($type)
                ->orderBy('sort_order')
                ->orderBy('label')
                ->get(),
        ]);
    }

    public function store(ReferenceValueRequest $request): RedirectResponse
    {
        $value = ReferenceValue::create([
            'type' => $request->string('type')->value(),
            'code' => $request->string('code')->upper()->value(),
            'label' => $request->string('label')->value(),
            'sort_order' => (int) $request->integer('sort_order'),
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->audit->record($this->actor(), 'reference_value.created', $value, after: $value->only([
            'type', 'code', 'label',
        ]));

        return to_route('admin.settings.reference-values', ['type' => $value->type])
            ->with('status', 'Nilai rujukan telah ditambah.');
    }

    /**
     * Reference values are never deleted, because historical records point
     * at them. They are switched off so new records cannot pick them.
     */
    public function toggle(ReferenceValue $referenceValue): RedirectResponse
    {
        $activating = ! $referenceValue->is_active;

        $referenceValue->update(['is_active' => $activating]);

        $this->audit->record(
            $this->actor(),
            $activating ? 'reference_value.activated' : 'reference_value.deactivated',
            $referenceValue,
            before: ['is_active' => ! $activating],
            after: ['is_active' => $activating],
        );

        return to_route('admin.settings.reference-values', ['type' => $referenceValue->type])
            ->with('status', $activating ? 'Nilai telah diaktifkan.' : 'Nilai telah dinyahaktifkan.');
    }

    private function resolveType(?string $raw): ReferenceValueType
    {
        if ($raw === null || $raw === '') {
            return ReferenceValueType::cases()[0];
        }

        $type = ReferenceValueType::tryFrom($raw);

        if ($type === null) {
            throw new NotFoundHttpException('Senarai nilai rujukan tidak wujud.');
        }

        return $type;
    }

    private function actor(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
```

- [ ] **Step 7: Register the routes**

In `routes/web.php`, add the import:

```php
use App\Http\Controllers\Admin\Settings\ReferenceValueController;
```

Then append inside the settings group:

```php
                Route::get('reference-values', [ReferenceValueController::class, 'index'])
                    ->middleware('can:tetapan.lihat')
                    ->name('reference-values');

                Route::post('reference-values', [ReferenceValueController::class, 'store'])
                    ->middleware('can:tetapan.kemaskini')
                    ->name('reference-values.store');

                Route::patch('reference-values/{reference_value}/toggle', [ReferenceValueController::class, 'toggle'])
                    ->middleware('can:tetapan.kemaskini')
                    ->name('reference-values.toggle');
```

- [ ] **Step 8: Write the view**

Create `resources/views/admin/settings/reference-values.blade.php`:

```blade
@extends('layouts.app')

@section('title', 'Konfigurasi · Nilai Rujukan')

@section('content')
    <h2 class="mb-1 text-xl font-bold text-slate-900">Konfigurasi Sistem</h2>
    <p class="mb-4 text-sm text-slate-500">Senarai nilai yang boleh disunting tanpa menulis kod.</p>

    @include('admin.settings._tabs')

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-4 flex flex-wrap gap-2">
        @foreach ($types as $type)
            <a href="{{ route('admin.settings.reference-values', ['type' => $type->value]) }}"
               class="rounded-full px-3 py-1.5 text-sm font-medium {{ $activeType === $type ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                {{ $type->label() }}
            </a>
        @endforeach
    </div>

    <p class="mb-4 text-xs text-slate-500">Senarai ini digunakan oleh {{ $activeType->consumer() }}.</p>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Kod</th>
                        <th class="px-4 py-3">Label</th>
                        <th class="px-4 py-3 text-center">Susunan</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($values as $value)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono text-xs text-slate-700">{{ $value->code }}</td>
                            <td class="px-4 py-3 text-slate-800">{{ $value->label }}</td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ $value->sort_order }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $value->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $value->is_active ? 'Aktif' : 'Tidak aktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @can('tetapan.kemaskini')
                                    <form method="POST" action="{{ route('admin.settings.reference-values.toggle', $value) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="font-medium text-indigo-600 hover:underline">
                                            {{ $value->is_active ? 'Nyahaktif' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">Senarai ini masih kosong.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @can('tetapan.kemaskini')
            <form method="POST" action="{{ route('admin.settings.reference-values.store') }}"
                  class="h-fit rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                @csrf
                <input type="hidden" name="type" value="{{ $activeType->value }}">
                <h3 class="mb-4 text-sm font-semibold text-slate-800">Tambah ke {{ $activeType->label() }}</h3>

                <div class="space-y-4">
                    <div>
                        <label for="code" class="block text-sm font-medium text-slate-700">Kod</label>
                        <input type="text" id="code" name="code" value="{{ old('code') }}" required maxlength="50"
                               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('code')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="label" class="block text-sm font-medium text-slate-700">Label</label>
                        <input type="text" id="label" name="label" value="{{ old('label') }}" required maxlength="150"
                               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('label')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-slate-700">Susunan</label>
                        <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', 1) }}" min="0"
                               class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

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

- [ ] **Step 9: Run test to verify it passes**

Run: `php artisan test --filter=ReferenceValueTest`

Expected: PASS, 8 tests.

- [ ] **Step 10: Checkpoint**

```bash
vendor/bin/pint --format agent
php artisan test
```

Expected: tiada ujian gagal.

---

## Task 12: Templat notifikasi, FR-ADM-06

**Files:**
- Create: `database/migrations/2026_09_10_000007_create_notification_templates_table.php`
- Create: `app/Models/NotificationTemplate.php`
- Create: `database/factories/NotificationTemplateFactory.php`
- Create: `app/Http/Requests/Admin/NotificationTemplateRequest.php`
- Create: `app/Http/Controllers/Admin/Settings/NotificationTemplateController.php`
- Create: `resources/views/admin/settings/notification-templates.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Admin/Settings/NotificationTemplateTest.php`

Pemegang tempat ditulis sebagai `{{nama_penempah}}`. Templat menyimpan senarai pemegang tempat yang dibenarkan, dan penyimpanan menolak apa-apa pemegang tempat di luar senarai itu.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Admin/Settings/NotificationTemplateTest.php`:

```php
<?php

namespace Tests\Feature\Admin\Settings;

use App\Models\NotificationTemplate;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTemplateTest extends TestCase
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

    private function template(): NotificationTemplate
    {
        return NotificationTemplate::factory()->create([
            'key' => 'tempahan.disahkan',
            'channel' => 'emel',
            'locale' => 'ms',
            'subject' => 'Tempahan disahkan',
            'body' => 'Salam {{nama_penempah}}, tempahan anda disahkan.',
            'placeholders' => ['nama_penempah', 'nama_bilik'],
        ]);
    }

    public function test_the_tab_lists_templates(): void
    {
        $this->template();

        $this->actingAs($this->admin)
            ->get(route('admin.settings.notification-templates'))
            ->assertOk()
            ->assertSee('tempahan.disahkan');
    }

    public function test_the_edit_page_lists_the_allowed_placeholders(): void
    {
        $template = $this->template();

        $this->actingAs($this->admin)
            ->get(route('admin.settings.notification-templates.edit', $template))
            ->assertOk()
            ->assertSee('nama_penempah')
            ->assertSee('nama_bilik');
    }

    public function test_the_administrator_saves_a_template(): void
    {
        $template = $this->template();

        $this->actingAs($this->admin)
            ->put(route('admin.settings.notification-templates.update', $template), [
                'subject' => 'Tempahan anda telah disahkan',
                'body' => 'Salam {{nama_penempah}}, bilik {{nama_bilik}} telah disahkan.',
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.settings.notification-templates'));

        $this->assertSame('Tempahan anda telah disahkan', $template->fresh()->subject);
        $this->assertDatabaseHas('audit_logs', ['action' => 'notification_template.updated']);
    }

    public function test_an_unknown_placeholder_is_rejected(): void
    {
        $template = $this->template();

        $this->actingAs($this->admin)
            ->from(route('admin.settings.notification-templates.edit', $template))
            ->put(route('admin.settings.notification-templates.update', $template), [
                'subject' => 'Tempahan',
                'body' => 'Salam {{nama_tidak_wujud}}.',
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('body');

        $this->assertStringContainsString('nama_penempah', $template->fresh()->body);
    }

    public function test_an_unknown_placeholder_in_the_subject_is_rejected(): void
    {
        $template = $this->template();

        $this->actingAs($this->admin)
            ->from(route('admin.settings.notification-templates.edit', $template))
            ->put(route('admin.settings.notification-templates.update', $template), [
                'subject' => 'Tempahan {{tiada_ini}}',
                'body' => 'Salam {{nama_penempah}}.',
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('subject');
    }

    public function test_a_viewer_may_not_save(): void
    {
        $template = $this->template();

        $viewer = User::factory()->create();
        $viewer->assignRole('penyelia-ict');

        $this->actingAs($viewer)
            ->put(route('admin.settings.notification-templates.update', $template), [
                'subject' => 'Cubaan',
                'body' => 'Salam {{nama_penempah}}.',
                'is_active' => 1,
            ])
            ->assertForbidden();
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=NotificationTemplateTest`

Expected: FAIL with `Class "App\Models\NotificationTemplate" not found`.

- [ ] **Step 3: Write the migration, model and factory**

Create `database/migrations/2026_09_10_000007_create_notification_templates_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FR-ADM-06 and FR-NOT-07: editable message templates per channel and
     * language, with the placeholders each template is allowed to use.
     */
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100);
            $table->enum('channel', ['emel', 'dalam_aplikasi'])->default('emel');
            $table->string('locale', 5)->default('ms');
            $table->string('subject', 200);
            $table->text('body');
            $table->json('placeholders')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['key', 'channel', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
```

Create `app/Models/NotificationTemplate.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'channel',
        'locale',
        'subject',
        'body',
        'placeholders',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'placeholders' => 'array',
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

    /**
     * Placeholder names this template is allowed to use.
     *
     * @return array<int, string>
     */
    public function allowedPlaceholders(): array
    {
        return $this->placeholders ?? [];
    }

    /**
     * Every placeholder actually written in a piece of text.
     *
     * @return array<int, string>
     */
    public static function placeholdersIn(string $text): array
    {
        preg_match_all('/\{\{\s*([a-z0-9_]+)\s*\}\}/i', $text, $matches);

        return array_values(array_unique($matches[1] ?? []));
    }

    public function channelLabel(): string
    {
        return $this->channel === 'emel' ? 'E-mel' : 'Dalam aplikasi';
    }
}
```

Create `database/factories/NotificationTemplateFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\NotificationTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationTemplate>
 */
class NotificationTemplateFactory extends Factory
{
    protected $model = NotificationTemplate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => 'ujian.'.$this->faker->unique()->word(),
            'channel' => 'emel',
            'locale' => 'ms',
            'subject' => 'Subjek ujian',
            'body' => 'Kandungan ujian.',
            'placeholders' => ['nama_penerima'],
            'is_active' => true,
        ];
    }
}
```

- [ ] **Step 4: Write the form request**

Create `app/Http/Requests/Admin/NotificationTemplateRequest.php`:

```php
<?php

namespace App\Http\Requests\Admin;

use App\Models\NotificationTemplate;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class NotificationTemplateRequest extends FormRequest
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
            'subject' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string', 'max:20000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * FR-ADM-06: only the placeholders declared for this template may be
     * used, so a typo never ships an unresolved token to a real recipient.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var NotificationTemplate $template */
                $template = $this->route('notification_template');

                $allowed = $template->allowedPlaceholders();

                foreach (['subject', 'body'] as $field) {
                    $used = NotificationTemplate::placeholdersIn((string) $this->input($field));
                    $unknown = array_diff($used, $allowed);

                    if ($unknown !== []) {
                        $validator->errors()->add(
                            $field,
                            'Pemegang tempat tidak dikenali: '.implode(', ', $unknown).'.'
                        );
                    }
                }
            },
        ];
    }
}
```

- [ ] **Step 5: Write the controller**

Create `app/Http/Controllers/Admin/Settings/NotificationTemplateController.php`:

```php
<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NotificationTemplateRequest;
use App\Models\NotificationTemplate;
use App\Models\User;
use App\Services\Audit\AuditRecorder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * M01 — notification message templates (FR-ADM-06). Consumed by M14.
 */
class NotificationTemplateController extends Controller
{
    public function __construct(private readonly AuditRecorder $audit) {}

    public function index(): View
    {
        return view('admin.settings.notification-templates', [
            'templates' => NotificationTemplate::query()
                ->orderBy('key')
                ->orderBy('locale')
                ->get(),
            'template' => null,
        ]);
    }

    public function edit(NotificationTemplate $notificationTemplate): View
    {
        return view('admin.settings.notification-templates', [
            'templates' => NotificationTemplate::query()
                ->orderBy('key')
                ->orderBy('locale')
                ->get(),
            'template' => $notificationTemplate,
        ]);
    }

    public function update(NotificationTemplateRequest $request, NotificationTemplate $notificationTemplate): RedirectResponse
    {
        $before = $notificationTemplate->only(['subject', 'body', 'is_active']);

        $notificationTemplate->update([
            'subject' => $request->string('subject')->value(),
            'body' => $request->string('body')->value(),
            'is_active' => $request->boolean('is_active'),
        ]);

        /** @var User $actor */
        $actor = Auth::user();

        $this->audit->record(
            $actor,
            'notification_template.updated',
            $notificationTemplate,
            before: $before,
            after: $notificationTemplate->only(['subject', 'body', 'is_active']),
        );

        return to_route('admin.settings.notification-templates')
            ->with('status', 'Templat notifikasi telah disimpan.');
    }
}
```

- [ ] **Step 6: Register the routes**

In `routes/web.php`, add the import:

```php
use App\Http\Controllers\Admin\Settings\NotificationTemplateController;
```

Then append inside the settings group:

```php
                Route::get('notification-templates', [NotificationTemplateController::class, 'index'])
                    ->middleware('can:tetapan.lihat')
                    ->name('notification-templates');

                Route::get('notification-templates/{notification_template}', [NotificationTemplateController::class, 'edit'])
                    ->middleware('can:tetapan.lihat')
                    ->name('notification-templates.edit');

                Route::put('notification-templates/{notification_template}', [NotificationTemplateController::class, 'update'])
                    ->middleware('can:tetapan.kemaskini')
                    ->name('notification-templates.update');
```

- [ ] **Step 7: Write the view**

Create `resources/views/admin/settings/notification-templates.blade.php`:

```blade
@extends('layouts.app')

@section('title', 'Konfigurasi · Templat Notifikasi')

@section('content')
    <h2 class="mb-1 text-xl font-bold text-slate-900">Konfigurasi Sistem</h2>
    <p class="mb-4 text-sm text-slate-500">Subjek dan kandungan mesej keluar. Dihantar oleh modul notifikasi (M14).</p>

    @include('admin.settings._tabs')

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <h3 class="border-b border-slate-200 px-4 py-3 text-sm font-semibold text-slate-800">Templat</h3>
            <ul class="divide-y divide-slate-100">
                @forelse ($templates as $item)
                    <li>
                        <a href="{{ route('admin.settings.notification-templates.edit', $item) }}"
                           class="block px-4 py-3 text-sm hover:bg-slate-50 {{ $template?->is($item) ? 'bg-indigo-50' : '' }}">
                            <span class="font-mono text-xs text-slate-700">{{ $item->key }}</span>
                            <span class="mt-1 block text-xs text-slate-500">{{ $item->channelLabel() }} · {{ strtoupper($item->locale) }}</span>
                        </a>
                    </li>
                @empty
                    <li class="px-4 py-8 text-center text-sm text-slate-500">Tiada templat.</li>
                @endforelse
            </ul>
        </div>

        <div class="lg:col-span-2">
            @if ($template === null)
                <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-500 shadow-sm">
                    Pilih satu templat untuk disunting.
                </div>
            @else
                <form method="POST" action="{{ route('admin.settings.notification-templates.update', $template) }}"
                      class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    @csrf
                    @method('PUT')

                    <h3 class="mb-1 font-mono text-sm text-slate-800">{{ $template->key }}</h3>
                    <p class="mb-4 text-xs text-slate-500">{{ $template->channelLabel() }} · {{ strtoupper($template->locale) }}</p>

                    <div class="mb-4 rounded-lg bg-slate-50 p-3">
                        <p class="text-xs font-semibold text-slate-600">Pemegang tempat yang dibenarkan</p>
                        <p class="mt-1 flex flex-wrap gap-1">
                            @foreach ($template->allowedPlaceholders() as $placeholder)
                                <span class="rounded bg-white px-2 py-0.5 font-mono text-xs text-indigo-700 ring-1 ring-slate-200">
                                    &#123;&#123;{{ $placeholder }}&#125;&#125;
                                </span>
                            @endforeach
                        </p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label for="subject" class="block text-sm font-medium text-slate-700">Subjek</label>
                            <input type="text" id="subject" name="subject" value="{{ old('subject', $template->subject) }}" required maxlength="200"
                                   @disabled(! auth()->user()->can('tetapan.kemaskini'))
                                   class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('subject')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="body" class="block text-sm font-medium text-slate-700">Kandungan</label>
                            <textarea id="body" name="body" rows="10" required
                                      @disabled(! auth()->user()->can('tetapan.kemaskini'))
                                      class="mt-1 block w-full rounded-lg border-slate-300 font-mono text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('body', $template->body) }}</textarea>
                            @error('body')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $template->is_active))
                                   @disabled(! auth()->user()->can('tetapan.kemaskini'))
                                   class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            Aktif
                        </label>
                    </div>

                    @can('tetapan.kemaskini')
                        <button type="submit" class="mt-6 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                            Simpan
                        </button>
                    @endcan
                </form>
            @endif
        </div>
    </div>
@endsection
```

- [ ] **Step 8: Run test to verify it passes**

Run: `php artisan test --filter=NotificationTemplateTest`

Expected: PASS, 6 tests.

- [ ] **Step 9: Checkpoint**

```bash
vendor/bin/pint --format agent
php artisan test
```

Expected: tiada ujian gagal.

---

## Task 13: Seeder konfigurasi lalai

**Files:**
- Create: `database/seeders/SystemConfigurationSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`
- Test: `tests/Feature/Admin/Settings/SystemConfigurationSeederTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Admin/Settings/SystemConfigurationSeederTest.php`:

```php
<?php

namespace Tests\Feature\Admin\Settings;

use App\Models\Holiday;
use App\Models\NotificationTemplate;
use App\Models\OperatingHour;
use App\Models\ReferenceValue;
use App\Models\TicketPriority;
use Database\Seeders\SystemConfigurationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemConfigurationSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SystemConfigurationSeeder::class);
    }

    public function test_it_seeds_scalar_settings(): void
    {
        $this->assertSame(15, setting('checkin.threshold_minutes'));
        $this->assertSame(15, setting('checkin.grace_minutes'));
        $this->assertSame('Asia/Kuala_Lumpur', setting('umum.zon_masa'));
    }

    public function test_it_seeds_seven_days_of_operating_hours(): void
    {
        $this->assertSame(7, OperatingHour::query()->organisationDefault()->count());
        $this->assertTrue(OperatingHour::query()->where('day_of_week', 0)->value('is_closed'));
    }

    public function test_it_seeds_recurring_malaysian_public_holidays(): void
    {
        $this->assertTrue(Holiday::query()->where('name', 'Hari Kebangsaan')->value('recurs_annually'));
        $this->assertGreaterThanOrEqual(4, Holiday::query()->count());
    }

    public function test_it_seeds_all_five_reference_lists(): void
    {
        foreach (['kategori_aset', 'jenis_kerosakan', 'susun_atur_bilik', 'kemudahan_bilik', 'unit_stok'] as $type) {
            $this->assertGreaterThan(0, ReferenceValue::query()->where('type', $type)->count(), "Senarai {$type} kosong.");
        }
    }

    public function test_it_seeds_ticket_priorities_whose_resolution_exceeds_response(): void
    {
        $this->assertGreaterThanOrEqual(3, TicketPriority::query()->count());

        foreach (TicketPriority::all() as $priority) {
            $this->assertGreaterThan($priority->response_target_minutes, $priority->resolution_target_minutes);
        }
    }

    public function test_it_seeds_notification_templates_that_only_use_declared_placeholders(): void
    {
        $this->assertGreaterThanOrEqual(2, NotificationTemplate::query()->count());

        foreach (NotificationTemplate::all() as $template) {
            $used = NotificationTemplate::placeholdersIn($template->subject.' '.$template->body);

            $this->assertSame([], array_diff($used, $template->allowedPlaceholders()), "Templat {$template->key} guna pemegang tempat tidak diisytihar.");
        }
    }

    public function test_running_the_seeder_twice_does_not_duplicate_rows(): void
    {
        $this->seed(SystemConfigurationSeeder::class);

        $this->assertSame(7, OperatingHour::query()->organisationDefault()->count());
        $this->assertSame(1, NotificationTemplate::query()->where('key', 'tempahan.disahkan')->count());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=SystemConfigurationSeederTest`

Expected: FAIL with `Class "Database\Seeders\SystemConfigurationSeeder" not found`.

- [ ] **Step 3: Write the seeder**

Create `database/seeders/SystemConfigurationSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\Holiday;
use App\Models\NotificationTemplate;
use App\Models\OperatingHour;
use App\Models\ReferenceValue;
use App\Models\SystemSetting;
use App\Models\TicketPriority;
use App\Services\Configuration\SettingsRepository;
use Illuminate\Database\Seeder;

/**
 * Default M01 configuration. Idempotent: existing rows are left alone so
 * running it again never overwrites an administrator's own values.
 */
class SystemConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedScalarSettings();
        $this->seedOperatingHours();
        $this->seedHolidays();
        $this->seedReferenceValues();
        $this->seedTicketPriorities();
        $this->seedNotificationTemplates();

        app(SettingsRepository::class)->flush();
    }

    private function seedScalarSettings(): void
    {
        $settings = [
            ['key' => 'umum.nama_organisasi', 'value' => '"e-Fasiliti"', 'value_type' => 'teks', 'group' => 'umum', 'description' => 'Nama organisasi pada tajuk dan mesej keluar.'],
            ['key' => 'umum.zon_masa', 'value' => '"Asia/Kuala_Lumpur"', 'value_type' => 'teks', 'group' => 'umum', 'description' => 'Zon masa rasmi sistem.'],
            ['key' => 'umum.bahasa_lalai', 'value' => '"ms"', 'value_type' => 'teks', 'group' => 'umum', 'description' => 'Bahasa lalai antara muka dan notifikasi.'],
            ['key' => 'checkin.threshold_minutes', 'value' => '15', 'value_type' => 'nombor', 'group' => 'daftar-masuk', 'description' => 'Ambang pengesahan kehadiran selepas waktu mula.'],
            ['key' => 'checkin.grace_minutes', 'value' => '15', 'value_type' => 'nombor', 'group' => 'daftar-masuk', 'description' => 'Tempoh anjal sebelum tempahan dilepaskan.'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }

    private function seedOperatingHours(): void
    {
        foreach (range(0, 6) as $day) {
            $isWeekend = in_array($day, [0, 6], true);

            OperatingHour::firstOrCreate(
                ['owner_type' => null, 'owner_id' => null, 'day_of_week' => $day],
                [
                    'is_closed' => $isWeekend,
                    'opens_at' => $isWeekend ? null : '08:00',
                    'closes_at' => $isWeekend ? null : '17:00',
                ]
            );
        }
    }

    private function seedHolidays(): void
    {
        $holidays = [
            ['date' => '2026-01-01', 'name' => 'Tahun Baharu'],
            ['date' => '2026-05-01', 'name' => 'Hari Pekerja'],
            ['date' => '2026-08-31', 'name' => 'Hari Kebangsaan'],
            ['date' => '2026-09-16', 'name' => 'Hari Malaysia'],
            ['date' => '2026-12-25', 'name' => 'Hari Krismas'],
        ];

        foreach ($holidays as $holiday) {
            Holiday::firstOrCreate(
                ['date' => $holiday['date'], 'type' => Holiday::TYPE_PUBLIC],
                [
                    'name' => $holiday['name'],
                    'recurs_annually' => true,
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedReferenceValues(): void
    {
        $values = [
            'kategori_aset' => [
                ['KOMPUTER', 'Komputer meja'],
                ['RIBA', 'Komputer riba'],
                ['PENCETAK', 'Pencetak'],
                ['PENGHALA', 'Penghala rangkaian'],
                ['PROJEKTOR', 'Projektor'],
            ],
            'jenis_kerosakan' => [
                ['TIDAK-HIDUP', 'Tidak boleh dihidupkan'],
                ['RANGKAIAN', 'Masalah rangkaian'],
                ['PERISIAN', 'Masalah perisian'],
                ['SKRIN', 'Paparan rosak'],
                ['CETAK', 'Masalah cetakan'],
            ],
            'susun_atur_bilik' => [
                ['TEATER', 'Teater'],
                ['KELAS', 'Kelas'],
                ['BULATAN', 'Meja bulat'],
                ['U', 'Bentuk U'],
            ],
            'kemudahan_bilik' => [
                ['PROJEKTOR', 'Projektor'],
                ['PAPAN-PUTIH', 'Papan putih'],
                ['SIDANG-VIDEO', 'Sidang video'],
                ['PA', 'Sistem pembesar suara'],
            ],
            'unit_stok' => [
                ['UNIT', 'Unit'],
                ['KOTAK', 'Kotak'],
                ['SET', 'Set'],
            ],
        ];

        foreach ($values as $type => $rows) {
            foreach ($rows as $index => [$code, $label]) {
                ReferenceValue::firstOrCreate(
                    ['type' => $type, 'code' => $code],
                    [
                        'label' => $label,
                        'sort_order' => $index + 1,
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    private function seedTicketPriorities(): void
    {
        $priorities = [
            ['KRITIKAL', 'Kritikal', 30, 240, 1],
            ['TINGGI', 'Tinggi', 60, 480, 2],
            ['SEDERHANA', 'Sederhana', 240, 1440, 3],
            ['RENDAH', 'Rendah', 480, 4320, 4],
        ];

        foreach ($priorities as [$code, $label, $response, $resolution, $order]) {
            TicketPriority::firstOrCreate(
                ['code' => $code],
                [
                    'label' => $label,
                    'response_target_minutes' => $response,
                    'resolution_target_minutes' => $resolution,
                    'sort_order' => $order,
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedNotificationTemplates(): void
    {
        $templates = [
            [
                'key' => 'tempahan.disahkan',
                'subject' => 'Tempahan {{nama_bilik}} telah disahkan',
                'body' => "Salam {{nama_penempah}},\n\nTempahan anda bagi {{nama_bilik}} pada {{tarikh_mula}} telah disahkan.\n\nTerima kasih.",
                'placeholders' => ['nama_penempah', 'nama_bilik', 'tarikh_mula', 'tarikh_tamat'],
            ],
            [
                'key' => 'tempahan.menunggu_kelulusan',
                'subject' => 'Permohonan tempahan menunggu kelulusan anda',
                'body' => "Salam {{nama_pelulus}},\n\nSatu permohonan tempahan bagi {{nama_bilik}} daripada {{nama_penempah}} menunggu tindakan anda.",
                'placeholders' => ['nama_pelulus', 'nama_penempah', 'nama_bilik'],
            ],
            [
                'key' => 'tiket.dibuka',
                'subject' => 'Tiket {{nombor_tiket}} telah dibuka',
                'body' => "Salam {{nama_pelapor}},\n\nTiket {{nombor_tiket}} bagi aset {{nama_aset}} telah didaftarkan.",
                'placeholders' => ['nama_pelapor', 'nombor_tiket', 'nama_aset'],
            ],
        ];

        foreach ($templates as $template) {
            NotificationTemplate::firstOrCreate(
                ['key' => $template['key'], 'channel' => 'emel', 'locale' => 'ms'],
                [
                    'subject' => $template['subject'],
                    'body' => $template['body'],
                    'placeholders' => $template['placeholders'],
                    'is_active' => true,
                ]
            );
        }
    }
}
```

- [ ] **Step 4: Wire the seeder into DatabaseSeeder**

Open `database/seeders/DatabaseSeeder.php` and add `SystemConfigurationSeeder::class` to the `$this->call([...])` list, after `RolesAndPermissionsSeeder::class` and `OrganizationStructureSeeder::class`. If the file calls seeders individually rather than as an array, add the matching line in the same style.

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=SystemConfigurationSeederTest`

Expected: PASS, 7 tests.

- [ ] **Step 6: Verify against a real database**

```bash
php artisan migrate:fresh --seed
```

Expected: semua migrasi berjalan dan seeder selesai tanpa ralat.

- [ ] **Step 7: Checkpoint**

```bash
vendor/bin/pint --format agent
php artisan test
```

Expected: tiada ujian gagal.

---

## Task 14: Kemas kini dokumentasi dan pengesahan akhir

**Files:**
- Modify: `memory-bank/progress.md`
- Modify: `memory-bank/activeContext.md`

- [ ] **Step 1: Update progress.md**

In the "What works" section, add:

```markdown
- **M01 — Pentadbiran Sistem & Konfigurasi (siap)** — `system_settings` kunci-nilai dengan `SettingsRepository` bercache dan pembantu `setting()`; tujuh tab pentadbiran: umum, waktu operasi, cuti umum, peraturan tempahan per peranan, daftar masuk, keutamaan tiket dengan SLA, nilai rujukan lima senarai, templat notifikasi dengan pengesahan pemegang tempat. Setiap penulisan diaudit dengan nilai sebelum/selepas (FR-ADM-08).
- **`HolidayCalendar`** — `isHoliday()` dan `isBookable()`, menyokong cuti berulang tahunan; logik tulen tanpa pangkalan data supaya M05 boleh guna semula.
```

In "What's left to build", change the domain module line to:

```markdown
- Modul domain (M04–M15): bilik, tempahan, kelulusan, daftar masuk, sokongan, aset, tiket, penyelenggaraan, vendor, stok, notifikasi, laporan.
```

Add this note under the same section:

```markdown
- Tetapan M01 bagi M05, M07, M10 dan M14 sudah wujud tetapi belum ada penggunanya. Sahkan bentuk datanya semasa modul berkenaan dibina.
```

- [ ] **Step 2: Update activeContext.md**

Replace the "Current Focus" and "Next Steps" sections with:

```markdown
## Current Focus
- **Lapisan asas selesai: M02, M03, M01, dan asas M16.** Sistem kini mempunyai identiti, direktori dan konfigurasi penuh.

## Next Steps
- Fasa 1 baki: M04 Katalog Bilik, kemudian M05 Enjin Tempahan, M06 Kelulusan, M14 e-mel.
```

Add to "Notes / Gotchas":

```markdown
- Semua bacaan tetapan mesti melalui `setting()` atau `SettingsRepository`, bukan pertanyaan terus ke `system_settings`. Cache dibatalkan hanya oleh `SettingsRepository::set()` dan `flush()`.
- `operating_hours` polimorfik: baris tanpa pemilik ialah waktu lalai organisasi. M04 menambah baris milik bilik pada jadual yang sama.
- Templat notifikasi menolak pemegang tempat di luar lajur `placeholders`. Tambah pemegang tempat baharu pada lajur itu dahulu sebelum menggunakannya.
- Pembantu `setting()` dimuatkan melalui kunci `files` dalam `composer.json`. Selepas menariknya keluar, jalankan `composer dump-autoload`.
```

- [ ] **Step 3: Final verification**

```bash
vendor/bin/pint --format agent
php artisan test
```

Expected: pemformat bersih, semua ujian lulus, tiada gagal.

- [ ] **Step 4: Manual smoke test**

Run: `php artisan serve`

Then in a browser, logged in as `admin@e-fasiliti.test`:
1. Open Konfigurasi. Confirm all eight tabs appear.
2. On Waktu Operasi, mark Wednesday closed and save. Reopen the tab and confirm it persisted.
3. On Cuti Umum, add a one-off date and confirm it appears in the year listing.
4. On Peraturan Tempahan, set a minimum longer than the maximum and confirm the refusal message.
5. On Keutamaan Tiket, set a resolution target below the response target and confirm the refusal message.
6. On Nilai Rujukan, switch between all five lists and deactivate one value.
7. On Templat Notifikasi, type an undeclared placeholder and confirm the refusal names it.
8. Log in as a facility administrator and confirm the settings screens render read-only with no save button.

---

## Ringkasan Liputan Spec

| Keperluan | Tugasan |
|---|---|
| FR-ADM-01 waktu operasi | Bahagian 1 Task 5; waktu khusus bilik menyusul dalam M04 melalui lajur pemilik |
| FR-ADM-02 kalendar cuti | Bahagian 1 Task 6 dan 7 |
| FR-ADM-03 peraturan tempahan per peranan | Task 8 |
| FR-ADM-04 ambang daftar masuk | Task 9 |
| FR-ADM-05 keutamaan tiket dan SLA | Task 10 |
| FR-ADM-06 templat notifikasi | Task 12 |
| FR-ADM-07 nilai rujukan | Task 11 |
| FR-ADM-08 audit perubahan konfigurasi | Bahagian 1 Task 2, dan setiap pengawal tab |
| Peraturan pengesahan SRS §M01 | Bahagian 1 Task 5; Task 8 dan 10 |
| Kebenaran `tetapan.lihat` dan `tetapan.kemaskini` | Bahagian 1 Task 4, dikuatkuasakan pada setiap laluan |
| Nilai lalai boleh guna | Task 13 |
