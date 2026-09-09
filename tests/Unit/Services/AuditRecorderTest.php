<?php

namespace Tests\Unit\Services;

use App\Models\AuditLog;
use App\Models\Location;
use App\Models\User;
use App\Services\Audit\AuditRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class AuditRecorderTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_the_actor_action_and_target(): void
    {
        $actor = User::factory()->create();
        $location = Location::factory()->create(['name' => 'Kampus Induk']);

        app(AuditRecorder::class)->record($actor, 'location.created', $location);

        $log = AuditLog::query()->latest('id')->first();

        $this->assertSame($actor->id, $log->user_id);
        $this->assertSame('location.created', $log->action);
        $this->assertSame('success', $log->status);
        $this->assertSame('location', $log->record_type);
        $this->assertSame($location->id, $log->record_id);
    }

    public function test_it_records_only_the_fields_that_changed(): void
    {
        $actor = User::factory()->create();
        $location = Location::factory()->create(['name' => 'Nama Lama', 'code' => 'KOD-1']);

        app(AuditRecorder::class)->record(
            $actor,
            'location.updated',
            $location,
            before: ['name' => 'Nama Lama', 'code' => 'KOD-1'],
            after: ['name' => 'Nama Baharu', 'code' => 'KOD-1'],
        );

        $log = AuditLog::query()->latest('id')->first();

        $this->assertSame(['name' => 'Nama Lama'], $log->metadata['before']);
        $this->assertSame(['name' => 'Nama Baharu'], $log->metadata['after']);
    }

    public function test_it_omits_the_change_payload_when_nothing_changed(): void
    {
        $actor = User::factory()->create();
        $location = Location::factory()->create();

        app(AuditRecorder::class)->record(
            $actor,
            'location.updated',
            $location,
            before: ['name' => 'Sama'],
            after: ['name' => 'Sama'],
        );

        $log = AuditLog::query()->latest('id')->first();

        $this->assertNull($log->metadata);
    }

    public function test_it_keeps_the_whole_snapshot_when_a_record_is_deleted(): void
    {
        $actor = User::factory()->create();
        $location = Location::factory()->create();

        app(AuditRecorder::class)->record(
            $actor,
            'location.deleted',
            $location,
            before: ['code' => 'KOD-1', 'name' => 'Bilik Mesyuarat', 'parent_id' => null],
        );

        $log = AuditLog::query()->latest('id')->first();

        $this->assertSame(
            ['code' => 'KOD-1', 'name' => 'Bilik Mesyuarat'],
            $log->metadata['before'],
        );
        $this->assertSame(['code' => null, 'name' => null], $log->metadata['after']);
    }

    public function test_it_pads_a_field_missing_from_one_side_with_null(): void
    {
        $actor = User::factory()->create();
        $location = Location::factory()->create();

        app(AuditRecorder::class)->record(
            $actor,
            'location.updated',
            $location,
            before: ['name' => 'Sama'],
            after: ['name' => 'Sama', 'note' => 'Baharu'],
        );

        $log = AuditLog::query()->latest('id')->first();

        $this->assertSame(['note' => null], $log->metadata['before']);
        $this->assertSame(['note' => 'Baharu'], $log->metadata['after']);
    }

    public function test_it_stores_context_alongside_the_change_payload(): void
    {
        $actor = User::factory()->create();
        $location = Location::factory()->create();

        app(AuditRecorder::class)->record(
            $actor,
            'location.deactivated',
            $location,
            before: ['is_active' => true],
            after: ['is_active' => false],
            context: ['cascaded_ids' => [7, 9]],
        );

        $log = AuditLog::query()->latest('id')->first();

        $this->assertSame([7, 9], $log->metadata['cascaded_ids']);
        $this->assertSame(['is_active' => false], $log->metadata['after']);
    }

    public function test_it_stores_context_even_when_no_field_changed(): void
    {
        $actor = User::factory()->create();
        $location = Location::factory()->create();

        app(AuditRecorder::class)->record(
            $actor,
            'location.viewed',
            $location,
            context: ['reason' => 'export'],
        );

        $log = AuditLog::query()->latest('id')->first();

        $this->assertSame(['reason' => 'export'], $log->metadata);
    }

    public function test_it_truncates_a_user_agent_that_exceeds_the_column_width(): void
    {
        $this->app->instance('request', Request::create(
            '/',
            'GET',
            server: ['HTTP_USER_AGENT' => str_repeat('a', 400)],
        ));

        $actor = User::factory()->create();
        $location = Location::factory()->create();

        app(AuditRecorder::class)->record($actor, 'location.created', $location);

        $log = AuditLog::query()->latest('id')->first();

        $this->assertSame(255, strlen($log->user_agent));
    }
}
