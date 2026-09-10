<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Core permissions for the base platform modules: M01 configuration,
     * M02 users and roles, M03 organisation and location directory,
     * M15 reporting and M16 audit. Permissions for the remaining domain
     * modules (M04–M14) are added when those modules are developed,
     * according to the module/role matrix in docs-claude/01-modules.md §3.
     *
     * @var array<int, string>
     */
    public const PERMISSIONS = [
        // M02 — Pengguna, Peranan & Akses
        'pengguna.cipta',
        'pengguna.lihat',
        'pengguna.kemaskini',
        'pengguna.nyahaktif',
        'peranan.cipta',
        'peranan.lihat',
        'peranan.kemaskini',
        'peranan.padam',

        // M16 — Jejak Audit & Keselamatan
        'audit.lihat',

        // M03 — Direktori Organisasi & Lokasi
        'lokasi.lihat',
        'lokasi.cipta',
        'lokasi.kemaskini',
        'lokasi.padam',
        'unit-organisasi.lihat',
        'unit-organisasi.cipta',
        'unit-organisasi.kemaskini',
        'unit-organisasi.padam',

        // M01 — Pentadbiran & Konfigurasi
        'tetapan.lihat',
        'tetapan.kemaskini',

        // M10 — Tiket Aduan Kerosakan
        'tiket.buka',
        'tiket.lihat-sendiri',
        'tiket.lihat-semua',
        'tiket.kemas-kini',
        'tiket.agih',
        'tiket.keutamaan',
        'tiket.tutup',

        // M15 — Laporan
        'laporan.lihat',
    ];

    /**
     * The eight roles from the SRS (§1.4).
     *
     * @var array<string, string>
     */
    public const ROLES = [
        'kakitangan' => 'Kakitangan',
        'setiausaha' => 'Setiausaha',
        'pelulus' => 'Pelulus',
        'pentadbir-fasiliti' => 'Pentadbir Fasiliti',
        'juruteknik' => 'Juruteknik ICT',
        'penyelia-ict' => 'Penyelia ICT',
        'pegawai-aset' => 'Pegawai Aset',
        'pentadbir-sistem' => 'Pentadbir Sistem',
    ];

    /**
     * Permission grants per role, following the module/role matrix in
     * docs-claude/01-modules.md §3.
     *
     * Note: the matrix gives Pentadbir Fasiliti and Pegawai Aset CRU across
     * the whole of M03. Write access to the organisation hierarchy is
     * deliberately narrower than that — only Pentadbir Sistem may restructure
     * bahagian and unit, because that is structural data rather than physical
     * facilities. Every role may still read it.
     *
     * Note: the M02 row also gives Pentadbir Fasiliti, Penyelia ICT and
     * Pegawai Aset a plain read of the user directory. That grant is deferred,
     * not omitted: the directory is still gated by role rather than by
     * permission (routes/web.php), so granting pengguna.lihat today would
     * change nothing. Add it when those routes move to can: gating.
     *
     * Note: the M10 row gives Pelulus "R unit", and Pentadbir Fasiliti and
     * Pegawai Aset a plain read. Unit-scoped reads are not implemented yet,
     * so those three roles receive tiket.lihat-semua instead. This is a
     * deliberate widening, not an omission; revisit when scoping by unit
     * arrives (UR-21 needs only the reporter's own list, which every role
     * gets through tiket.lihat-sendiri regardless).
     *
     * @var array<string, array<int, string>>
     */
    public const ROLE_PERMISSIONS = [
        'kakitangan' => [
            'laporan.lihat',
            'lokasi.lihat',
            'tiket.buka',
            'tiket.lihat-sendiri',
            'unit-organisasi.lihat',
        ],
        'setiausaha' => [
            'laporan.lihat',
            'lokasi.lihat',
            'tiket.buka',
            'tiket.lihat-sendiri',
            'unit-organisasi.lihat',
        ],
        'pelulus' => [
            'laporan.lihat',
            'lokasi.lihat',
            'tiket.lihat-sendiri',
            'tiket.lihat-semua',
            'unit-organisasi.lihat',
        ],
        'pentadbir-fasiliti' => [
            'laporan.lihat',
            'tetapan.lihat',
            'lokasi.lihat',
            'lokasi.cipta',
            'lokasi.kemaskini',
            'tiket.lihat-sendiri',
            'tiket.lihat-semua',
            'unit-organisasi.lihat',
        ],
        'juruteknik' => [
            'laporan.lihat',
            'lokasi.lihat',
            'tiket.kemas-kini',
            'tiket.lihat-sendiri',
            'unit-organisasi.lihat',
        ],
        'penyelia-ict' => [
            'laporan.lihat',
            'audit.lihat',
            'tetapan.lihat',
            'lokasi.lihat',
            'tiket.agih',
            'tiket.buka',
            'tiket.keutamaan',
            'tiket.kemas-kini',
            'tiket.lihat-sendiri',
            'tiket.lihat-semua',
            'tiket.tutup',
            'unit-organisasi.lihat',
        ],
        'pegawai-aset' => [
            'laporan.lihat',
            'audit.lihat',
            'tetapan.lihat',
            'lokasi.lihat',
            'lokasi.cipta',
            'lokasi.kemaskini',
            'tiket.lihat-sendiri',
            'tiket.lihat-semua',
            'unit-organisasi.lihat',
        ],
        'pentadbir-sistem' => self::PERMISSIONS,
    ];

    /**
     * Run the database seeds.
     *
     * syncPermissions is authoritative: re-running this seeder discards any
     * grant an administrator changed through the role management screen.
     */
    public function run(): void
    {
        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (self::ROLES as $roleSlug => $roleName) {
            $role = Role::findOrCreate($roleSlug, 'web');

            $role->syncPermissions(...self::ROLE_PERMISSIONS[$roleSlug]);
        }
    }
}
