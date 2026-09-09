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
