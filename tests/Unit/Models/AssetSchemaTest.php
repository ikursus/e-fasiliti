<?php

namespace Tests\Unit\Models;

use App\Models\Asset;
use App\Models\AssetHistory;
use App\Models\Location;
use App\Models\ReferenceValue;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_registration_number_is_unique_across_every_status_including_dilupuskan(): void
    {
        Asset::factory()->dilupuskan()->create(['registration_number' => 'AST-DUP']);

        $this->expectException(QueryException::class);

        Asset::factory()->create(['registration_number' => 'AST-DUP']);
    }

    public function test_the_serial_number_is_optional_and_unique_only_when_present(): void
    {
        Asset::factory()->create(['serial_number' => null]);
        Asset::factory()->create(['serial_number' => null]);

        $this->assertSame(2, Asset::query()->whereNull('serial_number')->count());

        Asset::factory()->create(['serial_number' => 'SN-DUP']);

        $this->expectException(QueryException::class);

        Asset::factory()->create(['serial_number' => 'SN-DUP']);
    }

    public function test_a_referenced_category_cannot_be_deleted(): void
    {
        $category = ReferenceValue::factory()->create();
        Asset::factory()->withCategory($category)->create();

        $this->expectException(QueryException::class);

        $category->delete();
    }

    public function test_a_location_with_assets_cannot_be_deleted(): void
    {
        $location = Location::factory()->create();
        Asset::factory()->atLocation($location)->create();

        $this->expectException(QueryException::class);

        $location->delete();
    }

    public function test_deleting_an_asset_cascades_its_history(): void
    {
        $asset = Asset::factory()->create();
        AssetHistory::factory()->count(2)->create(['asset_id' => $asset->id]);

        $asset->delete();

        $this->assertDatabaseCount('asset_histories', 0);
    }
}
