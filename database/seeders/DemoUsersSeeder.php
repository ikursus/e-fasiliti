<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\OrganizationUnit;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUsersSeeder extends Seeder
{
    /**
     * Demo accounts (one per role) for local development and testing.
     *
     * @var array<int, array{name: string, email: string, password: string, role: string}>
     */
    public const DEMO_USERS = [
        ['name' => 'Ahmad Zaki (Pentadbir Sistem)', 'email' => 'admin@e-fasiliti.test', 'password' => 'password', 'role' => 'pentadbir-sistem'],
        ['name' => 'Puan Lina (Pentadbir Fasiliti)', 'email' => 'fasiliti@e-fasiliti.test', 'password' => 'password', 'role' => 'pentadbir-fasiliti'],
        ['name' => 'Encik Zul (Penyelia ICT)', 'email' => 'penyelia@e-fasiliti.test', 'password' => 'password', 'role' => 'penyelia-ict'],
        ['name' => 'Puan Nor (Pegawai Aset)', 'email' => 'aset@e-fasiliti.test', 'password' => 'password', 'role' => 'pegawai-aset'],
        ['name' => 'Encik Rahman (Pelulus)', 'email' => 'pelulus@e-fasiliti.test', 'password' => 'password', 'role' => 'pelulus'],
        ['name' => 'Siti (Setiausaha)', 'email' => 'setiausaha@e-fasiliti.test', 'password' => 'password', 'role' => 'setiausaha'],
        ['name' => 'Faiz (Juruteknik ICT)', 'email' => 'juruteknik@e-fasiliti.test', 'password' => 'password', 'role' => 'juruteknik'],
        ['name' => 'Khadijah (Kakitangan)', 'email' => 'kakitangan@e-fasiliti.test', 'password' => 'password', 'role' => 'kakitangan'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $unitIct = OrganizationUnit::query()->where('code', 'PENTADBIRAN-UNIT-ICT')->first();
        $bangunanA = Location::query()->where('code', 'BANG-A')->first();

        foreach (self::DEMO_USERS as $demoUser) {
            $user = User::query()->firstOrCreate(
                ['email' => $demoUser['email']],
                [
                    'name' => $demoUser['name'],
                    'password' => $demoUser['password'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'organization_unit_id' => $unitIct?->id,
                    'primary_location_id' => $bangunanA?->id,
                ]
            );

            $user->syncRoles($demoUser['role']);
        }
    }
}
