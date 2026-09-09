<?php

namespace Tests\Feature\Admin\Settings;

use App\Models\NotificationTemplate;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTemplateTest extends TestCase
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

    private function template(): NotificationTemplate
    {
        return NotificationTemplate::factory()->create([
            'key' => 'tempahan.disahkan',
            'channel' => 'emel',
            'locale' => 'ms',
            'subject' => 'Tempahan disahkan',
            'body' => 'Salam {{nama_penempah}}, tempahan anda disahkan.',
            'placeholders' => ['nama_penempah', 'nama_bilik'],
        ]);
    }

    public function test_the_tab_lists_templates(): void
    {
        $this->template();

        $this->actingAs($this->admin)
            ->get(route('admin.settings.notification-templates'))
            ->assertOk()
            ->assertSee('tempahan.disahkan');
    }

    public function test_the_edit_page_lists_the_allowed_placeholders(): void
    {
        $template = $this->template();

        $this->actingAs($this->admin)
            ->get(route('admin.settings.notification-templates.edit', $template))
            ->assertOk()
            ->assertSee('nama_penempah')
            ->assertSee('nama_bilik');
    }

    public function test_the_administrator_saves_a_template(): void
    {
        $template = $this->template();

        $this->actingAs($this->admin)
            ->put(route('admin.settings.notification-templates.update', $template), [
                'subject' => 'Tempahan anda telah disahkan',
                'body' => 'Salam {{nama_penempah}}, bilik {{nama_bilik}} telah disahkan.',
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.settings.notification-templates'));

        $this->assertSame('Tempahan anda telah disahkan', $template->fresh()->subject);
        $this->assertDatabaseHas('audit_logs', ['action' => 'notification_template.updated']);
    }

    public function test_an_unknown_placeholder_is_rejected(): void
    {
        $template = $this->template();

        $this->actingAs($this->admin)
            ->from(route('admin.settings.notification-templates.edit', $template))
            ->put(route('admin.settings.notification-templates.update', $template), [
                'subject' => 'Tempahan',
                'body' => 'Salam {{nama_tidak_wujud}}.',
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('body');

        $this->assertStringContainsString('nama_penempah', $template->fresh()->body);
    }

    public function test_an_unknown_placeholder_in_the_subject_is_rejected(): void
    {
        $template = $this->template();

        $this->actingAs($this->admin)
            ->from(route('admin.settings.notification-templates.edit', $template))
            ->put(route('admin.settings.notification-templates.update', $template), [
                'subject' => 'Tempahan {{tiada_ini}}',
                'body' => 'Salam {{nama_penempah}}.',
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('subject');
    }

    public function test_placeholders_with_surrounding_spaces_are_recognised(): void
    {
        $template = $this->template();

        $this->actingAs($this->admin)
            ->put(route('admin.settings.notification-templates.update', $template), [
                'subject' => 'Tempahan disahkan',
                'body' => 'Salam {{ nama_penempah }}, bilik {{  nama_bilik  }}.',
                'is_active' => 1,
            ])
            ->assertSessionHasNoErrors();
    }

    public function test_a_template_with_no_placeholders_at_all_is_accepted(): void
    {
        $template = $this->template();

        $this->actingAs($this->admin)
            ->put(route('admin.settings.notification-templates.update', $template), [
                'subject' => 'Makluman umum',
                'body' => 'Tiada pemegang tempat di sini.',
                'is_active' => 1,
            ])
            ->assertSessionHasNoErrors();
    }

    public function test_a_viewer_may_not_save(): void
    {
        $template = $this->template();

        $viewer = User::factory()->create();
        $viewer->assignRole('penyelia-ict');

        $this->actingAs($viewer)
            ->put(route('admin.settings.notification-templates.update', $template), [
                'subject' => 'Cubaan',
                'body' => 'Salam {{nama_penempah}}.',
                'is_active' => 1,
            ])
            ->assertForbidden();
    }
}
