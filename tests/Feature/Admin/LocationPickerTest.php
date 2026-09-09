<?php

namespace Tests\Feature\Admin;

use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class LocationPickerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_active_locations_as_a_json_payload(): void
    {
        $campus = Location::factory()->create(['name' => 'Kampus Induk']);
        Location::factory()->bangunan()->childOf($campus)->create(['name' => 'Blok A']);

        $html = Blade::render('<x-ui.location-picker name="primary_location_id" />');

        $this->assertStringContainsString('Kampus Induk', $html);
        $this->assertStringContainsString('Blok A', $html);
        $this->assertStringContainsString('name="primary_location_id"', $html);
    }

    public function test_it_omits_deactivated_locations(): void
    {
        Location::factory()->create(['name' => 'Kampus Aktif']);
        Location::factory()->inactive()->create(['name' => 'Kampus Ditutup']);

        $html = Blade::render('<x-ui.location-picker name="primary_location_id" />');

        $this->assertStringContainsString('Kampus Aktif', $html);
        $this->assertStringNotContainsString('Kampus Ditutup', $html);
    }

    public function test_it_marks_the_selected_location(): void
    {
        $campus = Location::factory()->create(['name' => 'Kampus Induk']);

        $html = Blade::render(
            '<x-ui.location-picker name="primary_location_id" :selected="$id" />',
            ['id' => $campus->id]
        );

        $this->assertStringContainsString('value="'.$campus->id.'"', $html);
    }
}
