<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Location;
use App\Models\Room;
use App\Models\RoomLayout;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SystemConfigurationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SystemConfigurationSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('pentadbir-sistem');
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    /**
     * A valid payload using the seeded reference codes (susun_atur_bilik:
     * TEATER/KELAS/U, kemudahan_bilik: PROJEKTOR/PAPAN-PUTIH).
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function roomPayload(array $overrides = []): array
    {
        $ruang = Location::factory()->ruang()->create();

        return array_merge([
            'code' => 'BM-UJI-01',
            'name' => 'Bilik Ujian',
            'location_id' => $ruang->id,
            'base_capacity' => 20,
            'requires_approval' => 0,
            'allowed_roles' => null,
            'min_duration_minutes' => 30,
            'max_duration_minutes' => 480,
            'is_active' => 1,
            'layouts' => [
                ['layout_code' => 'TEATER', 'capacity' => 30, 'is_default' => 1],
                ['layout_code' => 'U', 'capacity' => 18, 'is_default' => 0],
            ],
            'facilities' => ['PROJEKTOR', 'PAPAN-PUTIH'],
        ], $overrides);
    }

    /**
     * A valid seven-day grid for the edit form (RoomUpdateRequest makes
     * it required, exactly like the browser does).
     *
     * @return array<int, array<string, mixed>>
     */
    private function daysPayload(): array
    {
        $days = [];

        foreach (range(0, 6) as $day) {
            $days[$day] = ['is_closed' => 0, 'opens_at' => '08:00', 'closes_at' => '17:00'];
        }

        return $days;
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('admin.rooms.index'))->assertRedirect(route('login'));
    }

    public function test_exactly_the_matrix_roles_may_view_the_catalogue(): void
    {
        $mayRead = ['kakitangan', 'setiausaha', 'pelulus', 'pentadbir-fasiliti', 'pentadbir-sistem'];

        foreach (array_keys(RolesAndPermissionsSeeder::ROLES) as $role) {
            $response = $this->actingAs($this->userWithRole($role))
                ->get(route('admin.rooms.index'));

            in_array($role, $mayRead, true)
                ? $response->assertOk()
                : $response->assertForbidden();
        }
    }

    public function test_only_the_two_administrator_roles_may_create(): void
    {
        foreach (['kakitangan', 'setiausaha', 'pelulus', 'juruteknik', 'penyelia-ict', 'pegawai-aset'] as $role) {
            $this->actingAs($this->userWithRole($role))
                ->get(route('admin.rooms.create'))
                ->assertForbidden();
        }

        $this->actingAs($this->userWithRole('pentadbir-fasiliti'))
            ->get(route('admin.rooms.create'))
            ->assertOk();

        $this->actingAs($this->admin)
            ->get(route('admin.rooms.create'))
            ->assertOk();
    }

    public function test_a_facility_administrator_may_create_a_room_with_layouts_and_facilities(): void
    {
        $this->actingAs($this->userWithRole('pentadbir-fasiliti'))
            ->post(route('admin.rooms.store'), $this->roomPayload())
            ->assertRedirect(route('admin.rooms.index'));

        $room = Room::query()->where('code', 'BM-UJI-01')->sole();

        $this->assertSame('Bilik Ujian', $room->name);
        $this->assertSame(2, $room->layouts()->count());
        $this->assertSame(2, $room->facilities()->count());
        $this->assertTrue($room->layouts()->where('layout_code', 'TEATER')->value('is_default'));
    }

    public function test_creating_a_room_writes_an_audit_entry(): void
    {
        $this->actingAs($this->admin)->post(route('admin.rooms.store'), $this->roomPayload());

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'room.created',
            'record_type' => 'room',
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_updating_a_room_records_before_and_after_names(): void
    {
        $room = Room::factory()->has(RoomLayout::factory()->default(), 'layouts')->create([
            'name' => 'Nama Lama',
            'code' => 'BM-KOD-A',
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.rooms.update', $room), $this->roomPayload([
                'code' => 'BM-KOD-A',
                'name' => 'Nama Baharu',
                'location_id' => $room->location_id,
            ]) + ['days' => $this->daysPayload()])
            ->assertRedirect(route('admin.rooms.index'));

        $this->assertDatabaseHas('rooms', ['id' => $room->id, 'name' => 'Nama Baharu']);

        $log = AuditLog::query()->where('action', 'room.updated')->latest('id')->first();
        $this->assertSame('Nama Lama', $log->metadata['before']['name']);
        $this->assertSame('Nama Baharu', $log->metadata['after']['name']);
    }

    public function test_updating_a_room_replaces_its_layout_rows(): void
    {
        $room = Room::factory()->create();
        $room->layouts()->createMany([
            ['layout_code' => 'KELAS', 'capacity' => 24, 'is_default' => true],
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.rooms.update', $room), $this->roomPayload([
                'location_id' => $room->location_id,
                'layouts' => [
                    ['layout_code' => 'TEATER', 'capacity' => 30, 'is_default' => 1],
                ],
            ]) + ['days' => $this->daysPayload()])
            ->assertRedirect(route('admin.rooms.index'));

        $room->refresh();

        $this->assertSame(['TEATER'], $room->layouts()->pluck('layout_code')->all());
        $this->assertTrue($room->layouts()->where('layout_code', 'TEATER')->value('is_default'));
    }

    public function test_the_code_is_stored_in_upper_case(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.rooms.store'), $this->roomPayload(['code' => 'bm-kecil']))
            ->assertRedirect(route('admin.rooms.index'));

        $this->assertDatabaseHas('rooms', ['code' => 'BM-KECIL']);
    }

    public function test_deletion_is_blocked_when_the_room_has_bookings(): void
    {
        $room = Room::factory()->create();
        Booking::factory()->upcoming()->confirmed()->create(['room_id' => $room->id]);

        $this->actingAs($this->admin)
            ->from(route('admin.rooms.index'))
            ->delete(route('admin.rooms.destroy', $room))
            ->assertRedirect(route('admin.rooms.index'))
            ->assertSessionHasErrors('id');

        $this->assertDatabaseHas('rooms', ['id' => $room->id]);
    }

    public function test_an_unreferenced_room_is_deleted(): void
    {
        $room = Room::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.rooms.destroy', $room))
            ->assertRedirect(route('admin.rooms.index'));

        $this->assertDatabaseMissing('rooms', ['id' => $room->id]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'room.deleted',
            'record_type' => 'room',
            'record_id' => $room->id,
        ]);
    }

    public function test_search_filters_by_name_or_code(): void
    {
        Room::factory()->create(['name' => 'Bilik Utama', 'code' => 'BM-A-301']);
        Room::factory()->create(['name' => 'Bilik Kecil', 'code' => 'BM-B-101']);

        $this->actingAs($this->admin)
            ->get(route('admin.rooms.index', ['search' => 'Kecil']))
            ->assertOk()
            ->assertSee('Bilik Kecil')
            ->assertDontSee('Bilik Utama');
    }
}
