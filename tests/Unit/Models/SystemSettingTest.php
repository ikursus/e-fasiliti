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
