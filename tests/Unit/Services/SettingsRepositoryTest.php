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

    public function test_writing_an_existing_key_keeps_its_declared_type_and_group(): void
    {
        SystemSetting::create([
            'key' => 'a.satu',
            'value' => '1',
            'value_type' => 'nombor',
            'group' => 'daftar-masuk',
            'description' => 'Keterangan asal.',
        ]);

        // The caller passes the wrong type and group on purpose: an existing
        // row owns its own metadata and must not be reclassified by a write.
        $this->repository()->set('a.satu', 7, null, 'teks', 'umum');

        $this->assertDatabaseHas('system_settings', [
            'key' => 'a.satu',
            'value_type' => 'nombor',
            'group' => 'daftar-masuk',
            'description' => 'Keterangan asal.',
        ]);
        $this->assertSame(7, $this->repository()->get('a.satu'));
    }
}
