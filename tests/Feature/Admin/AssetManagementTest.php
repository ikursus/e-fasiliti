<?php

namespace Tests\Feature\Admin;

use App\Models\Asset;
use App\Models\Location;
use App\Models\ReferenceValue;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private ReferenceValue $category;

    private Location $location;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('pentadbir-sistem');

        $this->category = ReferenceValue::factory()->create();
        $this->location = Location::factory()->create();
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'registration_number' => 'AST-0001',
            'category_id' => $this->category->id,
            'brand' => 'Dell',
            'model' => 'OptiPlex 7010',
            'serial_number' => 'SN-0001',
            'acquisition_date' => '2025-03-15',
            'acquisition_cost' => '3500.00',
            'location_id' => $this->location->id,
            'responsible_user_id' => null,
            'status' => 'simpanan',
        ], $overrides);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('admin.assets.index'))->assertRedirect(route('login'));
    }

    public function test_every_role_except_pelulus_may_view_the_register(): void
    {
        foreach (array_keys(RolesAndPermissionsSeeder::ROLES) as $role) {
            $response = $this->actingAs($this->userWithRole($role))
                ->get(route('admin.assets.index'));

            $role === 'pelulus'
                ? $response->assertForbidden()
                : $response->assertOk();
        }
    }

    public function test_only_the_asset_officer_and_system_administrator_may_open_the_create_form(): void
    {
        foreach (array_keys(RolesAndPermissionsSeeder::ROLES) as $role) {
            $expected = in_array($role, ['pentadbir-sistem', 'pegawai-aset'], true);

            $this->actingAs($this->userWithRole($role))
                ->get(route('admin.assets.create'))
                ->assertStatus($expected ? 200 : 403);
        }
    }

    public function test_only_the_asset_officer_and_system_administrator_may_store_assets(): void
    {
        foreach (array_keys(RolesAndPermissionsSeeder::ROLES) as $role) {
            $expected = in_array($role, ['pentadbir-sistem', 'pegawai-aset'], true);
            $suffix = strtoupper(str_replace('-', '', $role));

            $this->actingAs($this->userWithRole($role))
                ->post(route('admin.assets.store'), $this->payload([
                    'registration_number' => 'AST-'.$suffix,
                    'serial_number' => 'SN-'.$suffix,
                ]))
                ->assertStatus($expected ? 302 : 403);
        }
    }

    public function test_only_the_asset_officer_and_system_administrator_may_edit_update_or_delete(): void
    {
        $writers = ['pentadbir-sistem', 'pegawai-aset'];

        foreach (array_keys(RolesAndPermissionsSeeder::ROLES) as $role) {
            $expected = in_array($role, $writers, true);
            $asset = Asset::factory()->create();
            $user = $this->userWithRole($role);

            $this->actingAs($user)
                ->get(route('admin.assets.edit', $asset))
                ->assertStatus($expected ? 200 : 403);

            $this->actingAs($user)
                ->put(route('admin.assets.update', $asset), $this->updatePayload($asset))
                ->assertStatus($expected ? 302 : 403);

            $this->actingAs($user)
                ->delete(route('admin.assets.destroy', $asset))
                ->assertStatus($expected ? 302 : 403);
        }
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
        ], $overrides);
    }

    public function test_registering_an_asset_records_history_and_audit(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.assets.store'), $this->payload())
            ->assertRedirect(route('admin.assets.index'));

        $asset = Asset::query()->where('registration_number', 'AST-0001')->firstOrFail();

        $this->assertDatabaseHas('asset_histories', [
            'asset_id' => $asset->id,
            'event_type' => 'didaftar',
            'recorded_by' => $this->admin->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'asset.created',
            'record_type' => 'asset',
            'record_id' => $asset->id,
        ]);
    }

    public function test_the_serial_number_is_optional(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.assets.store'), $this->payload(['serial_number' => null]))
            ->assertRedirect(route('admin.assets.index'));

        $this->assertDatabaseHas('assets', ['registration_number' => 'AST-0001', 'serial_number' => null]);
    }

    public function test_a_duplicate_registration_number_is_rejected_in_malay(): void
    {
        Asset::factory()->create(['registration_number' => 'AST-0001']);

        $this->actingAs($this->admin)
            ->post(route('admin.assets.store'), $this->payload())
            ->assertSessionHasErrors([
                'registration_number' => 'Nombor pendaftaran ini telah digunakan oleh aset lain.',
            ]);

        $this->assertSame(1, Asset::count());
    }

    public function test_a_duplicate_serial_number_is_rejected(): void
    {
        Asset::factory()->create(['serial_number' => 'SN-DUP']);

        $this->actingAs($this->admin)
            ->post(route('admin.assets.store'), $this->payload(['serial_number' => 'SN-DUP']))
            ->assertSessionHasErrors('serial_number');
    }

    public function test_the_category_must_be_an_active_asset_category(): void
    {
        $wrongType = ReferenceValue::factory()->create(['type' => 'unit_stok']);
        $inactive = ReferenceValue::factory()->create(['is_active' => false]);

        $this->actingAs($this->admin)
            ->post(route('admin.assets.store'), $this->payload(['category_id' => $wrongType->id]))
            ->assertSessionHasErrors('category_id');

        $this->actingAs($this->admin)
            ->post(route('admin.assets.store'), $this->payload(['category_id' => $inactive->id]))
            ->assertSessionHasErrors('category_id');
    }

    public function test_an_inactive_location_is_rejected(): void
    {
        $inactive = Location::factory()->inactive()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.assets.store'), $this->payload(['location_id' => $inactive->id]))
            ->assertSessionHasErrors('location_id');
    }

    public function test_staff_sees_only_assets_registered_under_their_name(): void
    {
        $staff = $this->userWithRole('kakitangan');
        $mine = Asset::factory()->ownedBy($staff)->create();
        $others = Asset::factory()->create();

        $this->actingAs($staff)
            ->get(route('admin.assets.index'))
            ->assertOk()
            ->assertSee($mine->registration_number)
            ->assertDontSee($others->registration_number);

        $this->actingAs($staff)
            ->get(route('admin.assets.show', $others))
            ->assertNotFound();

        $this->actingAs($staff)
            ->get(route('admin.assets.show', $mine))
            ->assertOk();
    }

    public function test_search_and_status_filters_narrow_the_register(): void
    {
        $printer = Asset::factory()->dalamPembaikan()->create(['hostname' => 'PRN-FINDME']);
        $laptop = Asset::factory()->create(['registration_number' => 'AST-LAPTOP']);

        $this->actingAs($this->admin)
            ->get(route('admin.assets.index', ['search' => 'PRN-FINDME']))
            ->assertOk()
            ->assertSee($printer->registration_number)
            ->assertDontSee($laptop->registration_number);

        $this->actingAs($this->admin)
            ->get(route('admin.assets.index', ['status' => 'dalam_pembaikan']))
            ->assertOk()
            ->assertSee($printer->registration_number)
            ->assertDontSee($laptop->registration_number);
    }

    public function test_deleting_removes_the_asset_and_writes_an_audit_entry(): void
    {
        $asset = Asset::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.assets.destroy', $asset))
            ->assertRedirect(route('admin.assets.index'));

        $this->assertDatabaseMissing('assets', ['id' => $asset->id]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'asset.deleted',
            'record_type' => 'asset',
            'record_id' => $asset->id,
        ]);
    }
}
