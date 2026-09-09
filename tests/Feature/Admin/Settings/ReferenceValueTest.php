<?php

namespace Tests\Feature\Admin\Settings;

use App\Models\ReferenceValue;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferenceValueTest extends TestCase
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

    public function test_the_tab_defaults_to_the_first_list(): void
    {
        ReferenceValue::factory()->create(['type' => 'kategori_aset', 'label' => 'Komputer Riba']);

        $this->actingAs($this->admin)
            ->get(route('admin.settings.reference-values'))
            ->assertOk()
            ->assertSee('Komputer Riba');
    }

    public function test_the_tab_can_switch_between_lists(): void
    {
        ReferenceValue::factory()->create(['type' => 'kategori_aset', 'label' => 'Komputer Riba']);
        ReferenceValue::factory()->create(['type' => 'unit_stok', 'label' => 'Kotak']);

        $this->actingAs($this->admin)
            ->get(route('admin.settings.reference-values', ['type' => 'unit_stok']))
            ->assertOk()
            ->assertSee('Kotak')
            ->assertDontSee('Komputer Riba');
    }

    public function test_an_unknown_list_type_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.settings.reference-values', ['type' => 'tiada_senarai']))
            ->assertNotFound();
    }

    public function test_the_administrator_adds_a_value(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.settings.reference-values.store'), [
                'type' => 'jenis_kerosakan',
                'code' => 'SKRIN',
                'label' => 'Skrin rosak',
                'sort_order' => 1,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.settings.reference-values', ['type' => 'jenis_kerosakan']));

        $this->assertDatabaseHas('reference_values', ['type' => 'jenis_kerosakan', 'code' => 'SKRIN']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'reference_value.created']);
    }

    public function test_the_code_must_be_unique_within_its_list(): void
    {
        ReferenceValue::factory()->create(['type' => 'unit_stok', 'code' => 'KOTAK']);

        $this->actingAs($this->admin)
            ->from(route('admin.settings.reference-values', ['type' => 'unit_stok']))
            ->post(route('admin.settings.reference-values.store'), [
                'type' => 'unit_stok',
                'code' => 'KOTAK',
                'label' => 'Duplikat',
                'sort_order' => 1,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('code');
    }

    public function test_the_same_code_is_allowed_in_a_different_list(): void
    {
        ReferenceValue::factory()->create(['type' => 'unit_stok', 'code' => 'UNIT']);

        $this->actingAs($this->admin)
            ->post(route('admin.settings.reference-values.store'), [
                'type' => 'kemudahan_bilik',
                'code' => 'UNIT',
                'label' => 'Unit penyaman udara',
                'sort_order' => 1,
                'is_active' => 1,
            ])
            ->assertSessionHasNoErrors();
    }

    public function test_the_code_is_stored_in_upper_case(): void
    {
        $this->actingAs($this->admin)->post(route('admin.settings.reference-values.store'), [
            'type' => 'susun_atur_bilik',
            'code' => 'bentuk-u',
            'label' => 'Bentuk U',
            'sort_order' => 1,
            'is_active' => 1,
        ]);

        $this->assertDatabaseHas('reference_values', ['type' => 'susun_atur_bilik', 'code' => 'BENTUK-U']);
    }

    public function test_a_value_is_deactivated_rather_than_deleted(): void
    {
        $value = ReferenceValue::factory()->create(['is_active' => true]);

        $this->actingAs($this->admin)
            ->patch(route('admin.settings.reference-values.toggle', $value))
            ->assertRedirect(route('admin.settings.reference-values', ['type' => $value->type]));

        $this->assertFalse($value->fresh()->is_active);
        $this->assertDatabaseHas('reference_values', ['id' => $value->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'reference_value.deactivated']);
    }

    public function test_a_viewer_may_not_write(): void
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('pegawai-aset');

        $this->actingAs($viewer)->get(route('admin.settings.reference-values'))->assertOk();

        $this->actingAs($viewer)
            ->post(route('admin.settings.reference-values.store'), [
                'type' => 'unit_stok',
                'code' => 'BARU',
                'label' => 'Baru',
                'sort_order' => 1,
                'is_active' => 1,
            ])
            ->assertForbidden();
    }
}
