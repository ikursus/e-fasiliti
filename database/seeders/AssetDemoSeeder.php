<?php

namespace Database\Seeders;

use App\Enums\AssetEventType;
use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\Location;
use App\Models\ReferenceValue;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * M09 — 10 contoh aset untuk ujian manual. Idempoten: rekod sedia ada
 * (ditapis melalui no. pendaftaran) tidak disentuh, jadi boleh dijalankan
 * semula tanpa menghasilkan pendua atau sejarah tiruan.
 *
 * Jalankan: php artisan db:seed --class=AssetDemoSeeder
 */
class AssetDemoSeeder extends Seeder
{
    /**
     * [no. pendaftaran, kod kategori, jenama, model, no. siri, tarikh
     *  perolehan, harga, status, indek pemilik (null = tiada), tambahan]
     *
     * @var array<int, array<string, mixed>>
     */
    private array $rows = [
        ['registration_number' => 'ICT/KOMP/2023/0012', 'category' => 'KOMPUTER', 'brand' => 'Dell', 'model' => 'OptiPlex 7010', 'serial_number' => 'SN-DL7010-001', 'acquisition_date' => '2023-02-14', 'acquisition_cost' => '3450.00', 'status' => 'digunakan', 'owner' => 0, 'warranty' => ['2023-03-01', 36]],
        ['registration_number' => 'ICT/KOMP/2023/0015', 'category' => 'KOMPUTER', 'brand' => 'HP', 'model' => 'ProDesk 400 G9', 'serial_number' => 'SN-HP400G9-014', 'acquisition_date' => '2023-06-20', 'acquisition_cost' => '2899.00', 'status' => 'digunakan', 'owner' => 1],
        ['registration_number' => 'ICT/RIBA/2024/0021', 'category' => 'RIBA', 'brand' => 'Dell', 'model' => 'Latitude 5440', 'serial_number' => 'SN-DL5440-021', 'acquisition_date' => '2024-01-10', 'acquisition_cost' => '4299.00', 'status' => 'digunakan', 'owner' => 0, 'warranty' => ['2024-02-01', 36]],
        ['registration_number' => 'ICT/RIBA/2024/0022', 'category' => 'RIBA', 'brand' => 'Lenovo', 'model' => 'ThinkPad E14', 'serial_number' => 'SN-TPE14-022', 'acquisition_date' => '2024-03-05', 'acquisition_cost' => '3899.00', 'status' => 'simpanan', 'owner' => null],
        ['registration_number' => 'ICT/PENCETAK/2022/0007', 'category' => 'PENCETAK', 'brand' => 'Canon', 'model' => 'imageCLASS LBP223dw', 'serial_number' => 'SN-CN223-007', 'acquisition_date' => '2022-08-11', 'acquisition_cost' => '1250.00', 'status' => 'digunakan', 'owner' => 2, 'warranty' => ['2022-09-01', 12]],
        ['registration_number' => 'ICT/PENCETAK/2022/0009', 'category' => 'PENCETAK', 'brand' => 'Epson', 'model' => 'L3210', 'serial_number' => 'SN-EP3210-009', 'acquisition_date' => '2022-11-02', 'acquisition_cost' => '799.00', 'status' => 'dalam_pembaikan', 'owner' => 1],
        ['registration_number' => 'ICT/PENGHALA/2023/0003', 'category' => 'PENGHALA', 'brand' => 'TP-Link', 'model' => 'Archer AX55', 'serial_number' => 'SN-TPAX55-003', 'acquisition_date' => '2023-04-18', 'acquisition_cost' => '649.00', 'status' => 'digunakan', 'owner' => null, 'network' => ['4C:B1:8C:2A:11:03', '10.10.20.1', 'GW-LANTAI2']],
        ['registration_number' => 'ICT/PENGHALA/2021/0001', 'category' => 'PENGHALA', 'brand' => 'Cisco', 'model' => 'RV340', 'serial_number' => 'SN-CSRV340-001', 'acquisition_date' => '2021-05-30', 'acquisition_cost' => '1899.00', 'status' => 'tidak_aktif', 'owner' => null, 'network' => ['00:1A:2B:3C:4D:5E', '10.10.20.254', 'GW-LAMA']],
        ['registration_number' => 'ICT/PROJEKTOR/2024/0005', 'category' => 'PROJEKTOR', 'brand' => 'Epson', 'model' => 'EB-X49', 'serial_number' => 'SN-EPX49-005', 'acquisition_date' => '2024-07-22', 'acquisition_cost' => '2199.00', 'status' => 'digunakan', 'owner' => 2],
        ['registration_number' => 'ICT/PROJEKTOR/2020/0002', 'category' => 'PROJEKTOR', 'brand' => 'BenQ', 'model' => 'MX560', 'serial_number' => 'SN-BQMX560-002', 'acquisition_date' => '2020-09-15', 'acquisition_cost' => '1899.00', 'status' => 'dilupuskan', 'owner' => null, 'notes' => 'Dilupuskan — rosak teruk. No. pendaftaran tidak boleh diguna semula (FR-AST-02).'],
    ];

    public function run(): void
    {
        $officer = User::role('pegawai-aset')->where('is_active', true)->orderBy('id')->first()
            ?? User::factory()->create(['name' => 'Pegawai Aset Demo'])->assignRole('pegawai-aset');

        $owners = User::query()->where('is_active', true)->orderBy('id')->limit(3)->get()->all();
        $owners[0] = $officer;

        $ruang = Location::query()->where('level', 'ruang')->orderBy('id')->get();
        $locations = $ruang->isNotEmpty() ? $ruang->take(2) : Location::query()->orderBy('id')->limit(2)->get();

        foreach ($this->rows as $index => $row) {
            $owner = $row['owner'] === null ? null : ($owners[$row['owner']] ?? $officer);
            $location = $locations[$index % $locations->count()];

            $attributes = [
                'category_id' => $this->categoryId($row['category']),
                'brand' => $row['brand'],
                'model' => $row['model'],
                'serial_number' => $row['serial_number'],
                'acquisition_date' => $row['acquisition_date'],
                'acquisition_cost' => $row['acquisition_cost'],
                'location_id' => $location->id,
                'responsible_user_id' => $owner?->id,
                'status' => AssetStatus::from($row['status']),
                'qr_code' => (string) Str::uuid(),
            ];

            if (isset($row['warranty'])) {
                $attributes['warranty_start_date'] = $row['warranty'][0];
                $attributes['warranty_months'] = $row['warranty'][1];
            }

            if (isset($row['network'])) {
                $attributes['mac_address'] = $row['network'][0];
                $attributes['ip_address'] = $row['network'][1];
                $attributes['hostname'] = $row['network'][2];
            }

            if (isset($row['notes'])) {
                $attributes['notes'] = $row['notes'];
            }

            $asset = Asset::firstOrCreate(
                ['registration_number' => $row['registration_number']],
                $attributes,
            );

            if ($asset->wasRecentlyCreated) {
                $asset->histories()->create([
                    'event_type' => AssetEventType::Didaftar,
                    'after' => $asset->refresh()->movementSnapshot(),
                    'recorded_by' => $officer->id,
                ]);

                echo 'DICIPTA  '.$asset->registration_number.'  ['.$asset->status->label().']'.PHP_EOL;
            } else {
                echo 'SEDIA ADA '.$asset->registration_number.PHP_EOL;
            }
        }

        echo 'Jumlah aset dalam daftar: '.Asset::count().PHP_EOL;
    }

    private function categoryId(string $code): int
    {
        return ReferenceValue::query()
            ->where('type', 'kategori_aset')
            ->where('code', $code)
            ->value('id');
    }
}
