<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\OrganizationUnit;
use Illuminate\Database\Seeder;

class OrganizationStructureSeeder extends Seeder
{
    /**
     * Seed the organisation and location reference data (M03).
     * Idempotent: missing rows are created, existing rows are untouched.
     */
    public function run(): void
    {
        $unitData = [
            ['code' => 'PENTADBIRAN', 'name' => 'Bahagian Pentadbiran', 'parent' => null],
            ['code' => 'PENTADBIRAN-UNIT-ICT', 'name' => 'Unit ICT', 'parent' => 'PENTADBIRAN'],
            ['code' => 'PENTADBIRAN-UNIT-AM', 'name' => 'Unit Am', 'parent' => 'PENTADBIRAN'],
            ['code' => 'KEWANGAN', 'name' => 'Bahagian Kewangan', 'parent' => null],
            ['code' => 'KEWANGAN-UNIT-BA', 'name' => 'Unit Bajet', 'parent' => 'KEWANGAN'],
        ];

        $unitIds = [];

        foreach ($unitData as $unit) {
            $parentId = $unit['parent'] !== null ? $unitIds[$unit['parent']] : null;

            $created = OrganizationUnit::firstOrCreate(
                ['code' => $unit['code']],
                [
                    'name' => $unit['name'],
                    'parent_id' => $parentId,
                    'is_active' => true,
                ]
            );

            $unitIds[$unit['code']] = $created->id;
        }

        $locationData = [
            ['code' => 'KAMPUS-PUTRAJAYA', 'name' => 'Kampus Putrajaya', 'level' => 'kampus', 'parent' => null],
            ['code' => 'BANG-A', 'name' => 'Bangunan A', 'level' => 'bangunan', 'parent' => 'KAMPUS-PUTRAJAYA'],
            ['code' => 'BANG-A-T1', 'name' => 'Aras 1 Bangunan A', 'level' => 'tingkat', 'parent' => 'BANG-A'],
            ['code' => 'BANG-A-T2', 'name' => 'Aras 2 Bangunan A', 'level' => 'tingkat', 'parent' => 'BANG-A'],
            ['code' => 'BM-A-1-01', 'name' => 'Bilik Mesyuarat Utama', 'level' => 'ruang', 'parent' => 'BANG-A-T1'],
            ['code' => 'BM-A-2-01', 'name' => 'Bilik Perbincangan 2A', 'level' => 'ruang', 'parent' => 'BANG-A-T2'],
            ['code' => 'PEJ-A-2-02', 'name' => 'Pejabat Unit ICT', 'level' => 'ruang', 'parent' => 'BANG-A-T2'],
        ];

        $locationIds = [];

        foreach ($locationData as $location) {
            $parentId = $location['parent'] !== null ? $locationIds[$location['parent']] : null;

            // Look up by code AND parent, matching the scoped uniqueness the
            // locations table now enforces.
            $created = Location::firstOrCreate(
                ['code' => $location['code'], 'parent_id' => $parentId],
                [
                    'name' => $location['name'],
                    'level' => $location['level'],
                    'is_active' => true,
                ]
            );

            $locationIds[$location['code']] = $created->id;
        }
    }
}
