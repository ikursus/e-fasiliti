<?php

namespace Tests\Feature\Admin\Settings;

use App\Models\OperatingHour;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperatingHoursTest extends TestCase
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
        $days = [];

        foreach (range(0, 6) as $day) {
            $days[$day] = [
                'is_closed' => in_array($day, [0, 6], true) ? 1 : 0,
                'opens_at' => '08:00',
                'closes_at' => '17:30',
            ];
        }

        foreach ($overrides as $day => $values) {
            $days[$day] = array_merge($days[$day], $values);
        }

        return ['days' => $days];
    }

    public function test_the_tab_renders_seven_days(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.settings.operating-hours'))
            ->assertOk()
            ->assertSee('Isnin')
            ->assertSee('Ahad');
    }

    public function test_the_administrator_saves_default_organisation_hours(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.settings.operating-hours.update'), $this->payload())
            ->assertRedirect(route('admin.settings.operating-hours'));

        $this->assertSame(7, OperatingHour::query()->whereNull('owner_type')->count());
        $this->assertDatabaseHas('operating_hours', ['day_of_week' => 1, 'is_closed' => false]);
        $this->assertDatabaseHas('operating_hours', ['day_of_week' => 0, 'is_closed' => true]);
    }

    public function test_saving_twice_updates_rather_than_duplicates(): void
    {
        $this->actingAs($this->admin)->put(route('admin.settings.operating-hours.update'), $this->payload());
        $this->actingAs($this->admin)->put(
            route('admin.settings.operating-hours.update'),
            $this->payload([1 => ['closes_at' => '18:00']])
        );

        $this->assertSame(7, OperatingHour::query()->count());
        $this->assertStringStartsWith('18:00', OperatingHour::query()->where('day_of_week', 1)->value('closes_at'));
    }

    public function test_the_closing_time_must_be_after_the_opening_time(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.settings.operating-hours'))
            ->put(
                route('admin.settings.operating-hours.update'),
                $this->payload([2 => ['opens_at' => '17:00', 'closes_at' => '09:00']])
            )
            ->assertSessionHasErrors('days.2.closes_at');
    }

    public function test_a_closed_day_does_not_need_times(): void
    {
        $this->actingAs($this->admin)
            ->put(
                route('admin.settings.operating-hours.update'),
                $this->payload([3 => ['is_closed' => 1, 'opens_at' => '', 'closes_at' => '']])
            )
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('operating_hours', ['day_of_week' => 3, 'is_closed' => true]);
    }

    public function test_an_operating_day_must_carry_both_times(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.settings.operating-hours'))
            ->put(
                route('admin.settings.operating-hours.update'),
                $this->payload([4 => ['is_closed' => 0, 'opens_at' => '', 'closes_at' => '']])
            )
            ->assertSessionHasErrors('days.4.opens_at');
    }

    public function test_a_viewer_may_not_save(): void
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('penyelia-ict');

        $this->actingAs($viewer)
            ->put(route('admin.settings.operating-hours.update'), $this->payload())
            ->assertForbidden();
    }

    public function test_saving_writes_an_audit_entry(): void
    {
        $this->actingAs($this->admin)->put(route('admin.settings.operating-hours.update'), $this->payload());

        $this->assertDatabaseHas('audit_logs', ['action' => 'operating_hours.updated']);
    }
}
