<?php

namespace Tests\Feature\Admin\Settings;

use App\Models\RoleBookingRule;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleBookingRulesTest extends TestCase
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
     * @param  array<int, array<string, mixed>>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        $rules = [];

        foreach (Role::all() as $role) {
            $rules[$role->id] = [
                'min_duration_minutes' => 30,
                'max_duration_minutes' => 240,
                'max_advance_days' => 90,
            ];
        }

        foreach ($overrides as $roleId => $values) {
            $rules[$roleId] = array_merge($rules[$roleId], $values);
        }

        return ['rules' => $rules];
    }

    public function test_the_tab_lists_every_role(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.settings.booking-rules'))
            ->assertOk()
            ->assertSee('kakitangan')
            ->assertSee('pentadbir-fasiliti');
    }

    public function test_the_administrator_saves_a_rule_for_every_role(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.settings.booking-rules.update'), $this->payload())
            ->assertRedirect(route('admin.settings.booking-rules'));

        $this->assertSame(8, RoleBookingRule::query()->count());
        $this->assertDatabaseHas('role_booking_rules', ['min_duration_minutes' => 30, 'max_duration_minutes' => 240]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'booking_rules.updated']);
    }

    public function test_the_minimum_duration_must_be_shorter_than_the_maximum(): void
    {
        $roleId = Role::findByName('kakitangan')->id;

        $this->actingAs($this->admin)
            ->from(route('admin.settings.booking-rules'))
            ->put(
                route('admin.settings.booking-rules.update'),
                $this->payload([$roleId => ['min_duration_minutes' => 300, 'max_duration_minutes' => 120]])
            )
            ->assertSessionHasErrors("rules.{$roleId}.max_duration_minutes");
    }

    public function test_equal_minimum_and_maximum_durations_are_rejected(): void
    {
        $roleId = Role::findByName('kakitangan')->id;

        $this->actingAs($this->admin)
            ->from(route('admin.settings.booking-rules'))
            ->put(
                route('admin.settings.booking-rules.update'),
                $this->payload([$roleId => ['min_duration_minutes' => 120, 'max_duration_minutes' => 120]])
            )
            ->assertSessionHasErrors("rules.{$roleId}.max_duration_minutes");
    }

    public function test_saving_twice_updates_rather_than_duplicates(): void
    {
        $roleId = Role::findByName('kakitangan')->id;

        $this->actingAs($this->admin)->put(route('admin.settings.booking-rules.update'), $this->payload());
        $this->actingAs($this->admin)->put(
            route('admin.settings.booking-rules.update'),
            $this->payload([$roleId => ['max_advance_days' => 30]])
        );

        $this->assertSame(8, RoleBookingRule::query()->count());
        $this->assertSame(30, RoleBookingRule::query()->where('role_id', $roleId)->value('max_advance_days'));
    }

    public function test_a_viewer_may_not_save(): void
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('pegawai-aset');

        $this->actingAs($viewer)
            ->put(route('admin.settings.booking-rules.update'), $this->payload())
            ->assertForbidden();
    }
}
