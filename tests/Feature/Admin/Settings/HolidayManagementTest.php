<?php

namespace Tests\Feature\Admin\Settings;

use App\Models\AuditLog;
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

    public function test_the_same_date_is_allowed_for_a_different_type(): void
    {
        Holiday::factory()->create(['date' => '2026-05-01', 'type' => 'cuti_umum']);

        $this->actingAs($this->admin)
            ->post(route('admin.settings.holidays.store'), [
                'date' => '2026-05-01',
                'name' => 'Penyelenggaraan',
                'type' => 'hari_tanpa_tempahan',
                'is_active' => 1,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.settings.holidays'));
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

    public function test_the_deletion_audit_keeps_the_snapshot(): void
    {
        $holiday = Holiday::factory()->create(['date' => '2026-05-01', 'name' => 'Hari Pekerja']);

        $this->actingAs($this->admin)->delete(route('admin.settings.holidays.destroy', $holiday));

        $log = AuditLog::query()->where('action', 'holiday.deleted')->latest('id')->first();

        $this->assertSame('Hari Pekerja', $log->metadata['before']['name']);
        $this->assertSame('2026-05-01', $log->metadata['before']['date']);
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
