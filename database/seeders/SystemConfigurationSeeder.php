<?php

namespace Database\Seeders;

use App\Models\Holiday;
use App\Models\NotificationTemplate;
use App\Models\OperatingHour;
use App\Models\ReferenceValue;
use App\Models\SystemSetting;
use App\Services\Configuration\SettingsRepository;
use Illuminate\Database\Seeder;

/**
 * Default M01 configuration. Idempotent: existing rows are left alone so
 * running it again never overwrites an administrator's own values.
 *
 * Ticket priorities are deliberately not seeded. They are FR-ADM-05, a
 * phase 2 requirement belonging to the support ticket module, and the
 * TicketPriority model does not exist yet. The check-in settings below are
 * kept even though their tab is phase 3 work, because they are plain
 * scalar rows that cost nothing and save work when M07 arrives.
 */
class SystemConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedScalarSettings();
        $this->seedOperatingHours();
        $this->seedHolidays();
        $this->seedReferenceValues();
        $this->seedNotificationTemplates();

        app(SettingsRepository::class)->flush();
    }

    private function seedScalarSettings(): void
    {
        $settings = [
            ['key' => 'umum.nama_organisasi', 'value' => '"e-Fasiliti"', 'value_type' => 'teks', 'group' => 'umum', 'description' => 'Nama organisasi pada tajuk dan mesej keluar.'],
            ['key' => 'umum.zon_masa', 'value' => '"Asia/Kuala_Lumpur"', 'value_type' => 'teks', 'group' => 'umum', 'description' => 'Zon masa rasmi sistem.'],
            ['key' => 'umum.bahasa_lalai', 'value' => '"ms"', 'value_type' => 'teks', 'group' => 'umum', 'description' => 'Bahasa lalai antara muka dan notifikasi.'],
            ['key' => 'checkin.threshold_minutes', 'value' => '15', 'value_type' => 'nombor', 'group' => 'daftar-masuk', 'description' => 'Ambang pengesahan kehadiran selepas waktu mula.'],
            ['key' => 'checkin.grace_minutes', 'value' => '15', 'value_type' => 'nombor', 'group' => 'daftar-masuk', 'description' => 'Tempoh anjal sebelum tempahan dilepaskan.'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }

    private function seedOperatingHours(): void
    {
        foreach (range(0, 6) as $day) {
            $isWeekend = in_array($day, [0, 6], true);

            OperatingHour::firstOrCreate(
                ['owner_type' => null, 'owner_id' => null, 'day_of_week' => $day],
                [
                    'is_closed' => $isWeekend,
                    'opens_at' => $isWeekend ? null : '08:00',
                    'closes_at' => $isWeekend ? null : '17:00',
                ]
            );
        }
    }

    private function seedHolidays(): void
    {
        $holidays = [
            ['date' => '2026-01-01', 'name' => 'Tahun Baharu'],
            ['date' => '2026-05-01', 'name' => 'Hari Pekerja'],
            ['date' => '2026-08-31', 'name' => 'Hari Kebangsaan'],
            ['date' => '2026-09-16', 'name' => 'Hari Malaysia'],
            ['date' => '2026-12-25', 'name' => 'Hari Krismas'],
        ];

        foreach ($holidays as $holiday) {
            // firstOrCreate cannot be used here. The date cast stores a full
            // datetime, so an equality lookup misses the existing row on
            // SQLite and the second run collides with the (date, type)
            // unique index. whereDate compares the date part on both drivers.
            $exists = Holiday::query()
                ->whereDate('date', $holiday['date'])
                ->where('type', Holiday::TYPE_PUBLIC)
                ->exists();

            if ($exists) {
                continue;
            }

            Holiday::create([
                'date' => $holiday['date'],
                'type' => Holiday::TYPE_PUBLIC,
                'name' => $holiday['name'],
                'recurs_annually' => true,
                'is_active' => true,
            ]);
        }
    }

    private function seedReferenceValues(): void
    {
        $values = [
            'kategori_aset' => [
                ['KOMPUTER', 'Komputer meja'],
                ['RIBA', 'Komputer riba'],
                ['PENCETAK', 'Pencetak'],
                ['PENGHALA', 'Penghala rangkaian'],
                ['PROJEKTOR', 'Projektor'],
            ],
            'jenis_kerosakan' => [
                ['TIDAK-HIDUP', 'Tidak boleh dihidupkan'],
                ['RANGKAIAN', 'Masalah rangkaian'],
                ['PERISIAN', 'Masalah perisian'],
                ['SKRIN', 'Paparan rosak'],
                ['CETAK', 'Masalah cetakan'],
            ],
            'susun_atur_bilik' => [
                ['TEATER', 'Teater'],
                ['KELAS', 'Kelas'],
                ['BULATAN', 'Meja bulat'],
                ['U', 'Bentuk U'],
            ],
            'kemudahan_bilik' => [
                ['PROJEKTOR', 'Projektor'],
                ['PAPAN-PUTIH', 'Papan putih'],
                ['SIDANG-VIDEO', 'Sidang video'],
                ['PA', 'Sistem pembesar suara'],
            ],
            'unit_stok' => [
                ['UNIT', 'Unit'],
                ['KOTAK', 'Kotak'],
                ['SET', 'Set'],
            ],
        ];

        foreach ($values as $type => $rows) {
            foreach ($rows as $index => [$code, $label]) {
                ReferenceValue::firstOrCreate(
                    ['type' => $type, 'code' => $code],
                    [
                        'label' => $label,
                        'sort_order' => $index + 1,
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    private function seedNotificationTemplates(): void
    {
        $templates = [
            [
                'key' => 'tempahan.disahkan',
                'subject' => 'Tempahan {{nama_bilik}} telah disahkan',
                'body' => "Salam {{nama_penempah}},\n\nTempahan anda bagi {{nama_bilik}} pada {{tarikh_mula}} telah disahkan.\n\nTerima kasih.",
                'placeholders' => ['nama_penempah', 'nama_bilik', 'tarikh_mula', 'tarikh_tamat'],
            ],
            [
                'key' => 'tempahan.menunggu_kelulusan',
                'subject' => 'Permohonan tempahan menunggu kelulusan anda',
                'body' => "Salam {{nama_pelulus}},\n\nSatu permohonan tempahan bagi {{nama_bilik}} daripada {{nama_penempah}} menunggu tindakan anda.",
                'placeholders' => ['nama_pelulus', 'nama_penempah', 'nama_bilik'],
            ],
            [
                'key' => 'tiket.dibuka',
                'subject' => 'Tiket {{nombor_tiket}} telah dibuka',
                'body' => "Salam {{nama_pelapor}},\n\nTiket {{nombor_tiket}} bagi aset {{nama_aset}} telah didaftarkan.",
                'placeholders' => ['nama_pelapor', 'nombor_tiket', 'nama_aset'],
            ],
        ];

        foreach ($templates as $template) {
            NotificationTemplate::firstOrCreate(
                ['key' => $template['key'], 'channel' => 'emel', 'locale' => 'ms'],
                [
                    'subject' => $template['subject'],
                    'body' => $template['body'],
                    'placeholders' => $template['placeholders'],
                    'is_active' => true,
                ]
            );
        }
    }
}
