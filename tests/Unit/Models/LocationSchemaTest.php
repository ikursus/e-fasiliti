<?php

namespace Tests\Unit\Models;

use App\Models\Location;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_same_code_is_allowed_under_two_different_parents(): void
    {
        $campusA = Location::factory()->create(['code' => 'KAMPUS-A']);
        $campusB = Location::factory()->create(['code' => 'KAMPUS-B']);

        Location::factory()->bangunan()->childOf($campusA)->create(['code' => 'BLOK-A']);
        $second = Location::factory()->bangunan()->childOf($campusB)->create(['code' => 'BLOK-A']);

        $this->assertSame('BLOK-A', $second->code);
        $this->assertSame(2, Location::where('code', 'BLOK-A')->count());
    }

    public function test_the_same_code_is_rejected_under_the_same_parent(): void
    {
        $campus = Location::factory()->create();
        Location::factory()->bangunan()->childOf($campus)->create(['code' => 'BLOK-A']);

        $this->expectException(QueryException::class);

        Location::factory()->bangunan()->childOf($campus)->create(['code' => 'BLOK-A']);
    }
}
