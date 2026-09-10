<?php

namespace Tests\Feature\Admin;

use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetHistoryTest extends TestCase
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
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function updatePayload(Asset $asset, array $overrides = []): array
    {
        return array_merge([
            'registration_number' => $asset->registration_number,
            'category_id' => $asset->category_id,
            'brand' => $asset->brand,
            'model' => $asset->model,
            'serial_number' => $asset->serial_number,
            'acquisition_date' => $asset->acquisition_date->toDateString(),
            'location_id' => $asset->location_id,
            'responsible_user_id' => $asset->responsible_user_id,
            'status' => $asset->status->value,
            'reason' => null,
        ], $overrides);
    }

    public function test_moving_an_asset_without_a_reason_is_rejected(): void
    {
        $asset = Asset::factory()->create();
        $newLocation = Location::factory()->create();

        $this->actingAs($this->admin)
            ->put(route('admin.assets.update', $asset), $this->updatePayload($asset, ['location_id' => $newLocation->id]))
            ->assertSessionHasErrors('reason');

        $this->assertSame(0, $asset->histories()->count());
    }

    public function test_moving_an_asset_records_a_pindah_lokasi_history(): void
    {
        $asset = Asset::factory()->create();
        $newLocation = Location::factory()->create();

        $this->actingAs($this->admin)
            ->put(route('admin.assets.update', $asset), $this->updatePayload($asset, [
                'location_id' => $newLocation->id,
                'reason' => 'Pindah ke bangunan baharu',
            ]))
            ->assertRedirect(route('admin.assets.show', $asset));

        $this->assertDatabaseHas('asset_histories', [
            'asset_id' => $asset->id,
            'event_type' => 'pindah_lokasi',
            'reason' => 'Pindah ke bangunan baharu',
            'recorded_by' => $this->admin->id,
        ]);

        $history = $asset->histories()->where('event_type', 'pindah_lokasi')->first();

        $this->assertSame($asset->location_id, $history->before['location_id']);
        $this->assertSame($newLocation->id, $history->after['location_id']);
    }

    public function test_changing_the_owner_records_a_tukar_pemilik_history(): void
    {
        $asset = Asset::factory()->create();
        $newOwner = User::factory()->create();

        $this->actingAs($this->admin)
            ->put(route('admin.assets.update', $asset), $this->updatePayload($asset, [
                'responsible_user_id' => $newOwner->id,
                'reason' => 'Pegawai lama berpindah bahagian',
            ]))
            ->assertRedirect(route('admin.assets.show', $asset));

        $this->assertDatabaseHas('asset_histories', [
            'asset_id' => $asset->id,
            'event_type' => 'tukar_pemilik',
        ]);
    }

    public function test_changing_the_status_records_a_tukar_status_history(): void
    {
        $asset = Asset::factory()->simpanan()->create();

        $this->actingAs($this->admin)
            ->put(route('admin.assets.update', $asset), $this->updatePayload($asset, [
                'status' => 'digunakan',
                'responsible_user_id' => User::factory()->create()->id,
                'reason' => 'Dikeluarkan kepada pengguna',
            ]))
            ->assertRedirect(route('admin.assets.show', $asset));

        $this->assertDatabaseHas('asset_histories', [
            'asset_id' => $asset->id,
            'event_type' => 'tukar_status',
        ]);
    }

    public function test_editing_notes_only_writes_no_new_movement_history(): void
    {
        $asset = Asset::factory()->create();

        $this->actingAs($this->admin)
            ->put(route('admin.assets.update', $asset), $this->updatePayload($asset, [
                'notes' => 'Kabel kuasa diganti pada 2026.',
            ]))
            ->assertRedirect(route('admin.assets.show', $asset));

        $this->assertSame(0, $asset->histories()->count());
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'asset.updated',
            'record_type' => 'asset',
            'record_id' => $asset->id,
        ]);
    }
}
