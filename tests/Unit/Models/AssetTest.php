<?php

namespace Tests\Unit\Models;

use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\Location;
use App\Models\ReferenceValue;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AssetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_it_casts_dates_enum_json_and_decimal(): void
    {
        $asset = Asset::factory()->inWarranty()->create([
            'acquisition_cost' => '1234.5',
            'specifications' => ['cpu' => 'i5'],
        ]);

        $this->assertInstanceOf(AssetStatus::class, $asset->status);
        $this->assertInstanceOf(Carbon::class, $asset->acquisition_date);
        $this->assertSame(['cpu' => 'i5'], $asset->specifications);
        $this->assertSame('1234.50', (string) $asset->acquisition_cost);
        $this->assertSame(36, $asset->warranty_months);
    }

    public function test_it_belongs_to_category_location_and_responsible_user(): void
    {
        $category = ReferenceValue::factory()->create(['label' => 'Komputer meja']);
        $location = Location::factory()->create();
        $user = User::factory()->create();

        $asset = Asset::factory()->withCategory($category)->atLocation($location)->ownedBy($user)->create();

        $this->assertSame($category->id, $asset->category->id);
        $this->assertSame($location->id, $asset->location->id);
        $this->assertSame($user->id, $asset->responsibleUser->id);
    }

    public function test_warranty_status_is_null_without_warranty_data(): void
    {
        $asset = Asset::factory()->create();

        $this->assertNull($asset->warrantyStatus()['active']);
    }

    public function test_warranty_status_detects_active_and_expired(): void
    {
        $active = Asset::factory()->inWarranty()->create();
        $expired = Asset::factory()->outOfWarranty()->create();

        $this->assertTrue($active->warrantyStatus()['active']);
        $this->assertGreaterThan(0, $active->warrantyStatus()['days_remaining']);
        $this->assertFalse($expired->warrantyStatus()['active']);
    }

    public function test_search_matches_identifiers_and_descriptive_fields(): void
    {
        $bySerial = Asset::factory()->create(['serial_number' => 'SN-FINDME']);
        $byHost = Asset::factory()->create(['hostname' => 'PRN-FINDME']);
        $other = Asset::factory()->create();

        $found = Asset::query()->search('FINDME')->pluck('id')->all();

        $this->assertEqualsCanonicalizing([$bySerial->id, $byHost->id], $found);
        $this->assertNotContains($other->id, $found);
    }

    public function test_visible_to_scopes_staff_to_their_own_assets_only(): void
    {
        $staff = User::factory()->create();
        $staff->assignRole('kakitangan');

        $officer = User::factory()->create();
        $officer->assignRole('pegawai-aset');

        $mine = Asset::factory()->ownedBy($staff)->create();
        $theirs = Asset::factory()->create();

        $this->assertSame([$mine->id], Asset::query()->visibleTo($staff)->pluck('id')->all());

        $officerIds = Asset::query()->visibleTo($officer)->pluck('id')->all();
        $this->assertContains($mine->id, $officerIds);
        $this->assertContains($theirs->id, $officerIds);
    }

    public function test_movement_snapshot_maps_status_to_its_scalar_value(): void
    {
        $asset = Asset::factory()->create();

        $snapshot = $asset->movementSnapshot();

        $this->assertSame('digunakan', $snapshot['status']);
        $this->assertSame($asset->location_id, $snapshot['location_id']);
        $this->assertSame($asset->responsible_user_id, $snapshot['responsible_user_id']);
    }
}
