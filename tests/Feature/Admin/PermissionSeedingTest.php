<?php

namespace Tests\Feature\Admin;

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionSeedingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The complete grant grid from docs-claude/01-modules.md §3, written out
     * independently of the seeder so that comparing the two proves something.
     *
     * Organisation-unit writes are deliberately narrower than the matrix row,
     * which gives Pentadbir Fasiliti and Pegawai Aset CRU across all of M03.
     * See the note on RolesAndPermissionsSeeder::ROLE_PERMISSIONS.
     *
     * @var array<string, array<int, string>>
     */
    private const EXPECTED_GRANTS = [
        'kakitangan' => [
            'aset.lihat',
            'chatbot.guna',
            'laporan.lihat',
            'lokasi.lihat',
            'unit-organisasi.lihat',
        ],
        'setiausaha' => [
            'aset.lihat',
            'chatbot.guna',
            'laporan.lihat',
            'lokasi.lihat',
            'unit-organisasi.lihat',
        ],
        'pelulus' => [
            'chatbot.guna',
            'laporan.lihat',
            'lokasi.lihat',
            'unit-organisasi.lihat',
        ],
        'pentadbir-fasiliti' => [
            'aset.lihat',
            'chatbot.guna',
            'laporan.lihat',
            'lokasi.cipta',
            'lokasi.kemaskini',
            'lokasi.lihat',
            'tetapan.lihat',
            'unit-organisasi.lihat',
        ],
        'juruteknik' => [
            'aset.lihat',
            'chatbot.guna',
            'laporan.lihat',
            'lokasi.lihat',
            'unit-organisasi.lihat',
        ],
        'penyelia-ict' => [
            'aset.lihat',
            'audit.lihat',
            'chatbot.guna',
            'laporan.lihat',
            'lokasi.lihat',
            'tetapan.lihat',
            'unit-organisasi.lihat',
        ],
        'pegawai-aset' => [
            'aset.cipta',
            'aset.kemaskini',
            'aset.lihat',
            'aset.padam',
            'audit.lihat',
            'chatbot.guna',
            'laporan.lihat',
            'lokasi.cipta',
            'lokasi.kemaskini',
            'lokasi.lihat',
            'tetapan.lihat',
            'unit-organisasi.lihat',
        ],
        'pentadbir-sistem' => [
            'aset.cipta',
            'aset.kemaskini',
            'aset.lihat',
            'aset.padam',
            'audit.lihat',
            'chatbot.guna',
            'chatbot.tetapan',
            'laporan.lihat',
            'lokasi.cipta',
            'lokasi.kemaskini',
            'lokasi.lihat',
            'lokasi.padam',
            'pengguna.cipta',
            'pengguna.kemaskini',
            'pengguna.lihat',
            'pengguna.nyahaktif',
            'peranan.cipta',
            'peranan.kemaskini',
            'peranan.lihat',
            'peranan.padam',
            'tetapan.kemaskini',
            'tetapan.lihat',
            'unit-organisasi.cipta',
            'unit-organisasi.kemaskini',
            'unit-organisasi.lihat',
            'unit-organisasi.padam',
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_it_seeds_the_eight_roles_and_the_whole_permission_catalogue(): void
    {
        $this->assertCount(8, Role::all());

        $this->assertEqualsCanonicalizing(
            RolesAndPermissionsSeeder::PERMISSIONS,
            Permission::query()->pluck('name')->all(),
        );
    }

    public function test_every_role_holds_exactly_the_permissions_the_matrix_grants(): void
    {
        foreach (self::EXPECTED_GRANTS as $slug => $expected) {
            $actual = Role::findByName($slug)->permissions->pluck('name')->sort()->values()->all();

            $this->assertSame(
                $expected,
                $actual,
                "Kebenaran bagi peranan {$slug} tidak sepadan dengan matriks."
            );
        }
    }

    public function test_the_expected_grid_covers_every_seeded_role(): void
    {
        $this->assertSame(
            array_keys(RolesAndPermissionsSeeder::ROLES),
            array_keys(self::EXPECTED_GRANTS),
        );
    }

    public function test_every_role_may_view_locations(): void
    {
        foreach (array_keys(RolesAndPermissionsSeeder::ROLES) as $slug) {
            $this->assertTrue(
                Role::findByName($slug)->hasPermissionTo('lokasi.lihat'),
                "Peranan {$slug} sepatutnya boleh melihat lokasi."
            );
        }
    }

    public function test_only_three_roles_may_create_or_update_locations(): void
    {
        $allowed = ['pentadbir-sistem', 'pentadbir-fasiliti', 'pegawai-aset'];

        foreach (array_keys(RolesAndPermissionsSeeder::ROLES) as $slug) {
            $role = Role::findByName($slug);

            $this->assertSame(
                in_array($slug, $allowed, true),
                $role->hasPermissionTo('lokasi.kemaskini'),
                "Kebenaran lokasi.kemaskini salah bagi peranan {$slug}."
            );

            $this->assertSame(
                in_array($slug, $allowed, true),
                $role->hasPermissionTo('lokasi.cipta'),
                "Kebenaran lokasi.cipta salah bagi peranan {$slug}."
            );
        }
    }

    public function test_only_the_system_administrator_may_delete_locations(): void
    {
        foreach (array_keys(RolesAndPermissionsSeeder::ROLES) as $slug) {
            $this->assertSame(
                $slug === 'pentadbir-sistem',
                Role::findByName($slug)->hasPermissionTo('lokasi.padam'),
                "Kebenaran lokasi.padam salah bagi peranan {$slug}."
            );
        }
    }

    public function test_only_the_system_administrator_may_manage_organisation_units(): void
    {
        foreach (array_keys(RolesAndPermissionsSeeder::ROLES) as $slug) {
            $role = Role::findByName($slug);

            $this->assertTrue(
                $role->hasPermissionTo('unit-organisasi.lihat'),
                "Peranan {$slug} sepatutnya boleh melihat unit organisasi."
            );

            foreach (['cipta', 'kemaskini', 'padam'] as $verb) {
                $this->assertSame(
                    $slug === 'pentadbir-sistem',
                    $role->hasPermissionTo("unit-organisasi.{$verb}"),
                    "Kebenaran unit-organisasi.{$verb} salah bagi peranan {$slug}."
                );
            }
        }
    }

    public function test_four_roles_may_view_settings_but_only_one_may_change_them(): void
    {
        $mayView = ['pentadbir-sistem', 'pentadbir-fasiliti', 'penyelia-ict', 'pegawai-aset'];

        foreach (array_keys(RolesAndPermissionsSeeder::ROLES) as $slug) {
            $role = Role::findByName($slug);

            $this->assertSame(
                in_array($slug, $mayView, true),
                $role->hasPermissionTo('tetapan.lihat'),
                "Kebenaran tetapan.lihat salah bagi peranan {$slug}."
            );

            $this->assertSame(
                $slug === 'pentadbir-sistem',
                $role->hasPermissionTo('tetapan.kemaskini'),
                "Kebenaran tetapan.kemaskini salah bagi peranan {$slug}."
            );
        }
    }

    public function test_reporting_and_audit_grants_are_unchanged(): void
    {
        foreach (array_keys(RolesAndPermissionsSeeder::ROLES) as $slug) {
            $role = Role::findByName($slug);

            $this->assertTrue(
                $role->hasPermissionTo('laporan.lihat'),
                "Peranan {$slug} sepatutnya boleh melihat laporan."
            );

            $this->assertSame(
                in_array($slug, ['penyelia-ict', 'pegawai-aset', 'pentadbir-sistem'], true),
                $role->hasPermissionTo('audit.lihat'),
                "Kebenaran audit.lihat salah bagi peranan {$slug}."
            );
        }
    }

    public function test_only_asset_officer_and_system_administrator_may_manage_assets(): void
    {
        $allowed = ['pentadbir-sistem', 'pegawai-aset'];

        foreach (array_keys(RolesAndPermissionsSeeder::ROLES) as $slug) {
            $role = Role::findByName($slug);

            foreach (['cipta', 'kemaskini', 'padam'] as $verb) {
                $this->assertSame(
                    in_array($slug, $allowed, true),
                    $role->hasPermissionTo("aset.{$verb}"),
                    "Kebenaran aset.{$verb} salah bagi peranan {$slug}."
                );
            }

            $this->assertSame(
                $slug !== 'pelulus',
                $role->hasPermissionTo('aset.lihat'),
                "Kebenaran aset.lihat salah bagi peranan {$slug}."
            );
        }
    }
}
