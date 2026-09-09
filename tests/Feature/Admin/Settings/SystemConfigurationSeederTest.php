<?php

namespace Tests\Feature\Admin\Settings;

use App\Models\Holiday;
use App\Models\NotificationTemplate;
use App\Models\OperatingHour;
use App\Models\ReferenceValue;
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

    public function test_it_seeds_the_room_layouts_and_facilities_the_room_catalogue_needs(): void
    {
        $this->assertGreaterThanOrEqual(4, ReferenceValue::query()->where('type', 'susun_atur_bilik')->count());
        $this->assertGreaterThanOrEqual(4, ReferenceValue::query()->where('type', 'kemudahan_bilik')->count());
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
        $holidaysBefore = Holiday::query()->count();

        $this->seed(SystemConfigurationSeeder::class);

        $this->assertSame(7, OperatingHour::query()->organisationDefault()->count());
        $this->assertSame(1, NotificationTemplate::query()->where('key', 'tempahan.disahkan')->count());
        $this->assertSame($holidaysBefore, Holiday::query()->count(), 'Cuti tidak boleh diduplikasi pada larian kedua.');
        $this->assertSame(1, Holiday::query()->where('name', 'Hari Kebangsaan')->count());
    }
}
