<?php

namespace Tests\Feature\Ticket;

use App\Models\Location;
use App\Models\OperatingHour;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Shared fixtures for the M10 ticket suite: a 09:00-17:00 Monday-Friday
 * calendar, one user per key role, and one active location. Every test runs
 * with notifications and mail faked, because the notifier is best-effort by
 * design and must never be what breaks an assertion.
 */
abstract class TicketTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $penyelia;

    protected User $juruteknik;

    protected User $kakitangan;

    protected Location $lokasi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seedOperatingHours();

        Notification::fake();
        Mail::fake();

        $this->penyelia = User::factory()->create();
        $this->penyelia->assignRole('penyelia-ict');

        $this->juruteknik = User::factory()->create();
        $this->juruteknik->assignRole('juruteknik');

        $this->kakitangan = User::factory()->create();
        $this->kakitangan->assignRole('kakitangan');

        $this->lokasi = Location::factory()->create();
    }

    /**
     * A fixed 09:00-17:00 Mon-Fri week: eight working hours per day, which
     * is also the default the service assumes for "three working days".
     */
    protected function seedOperatingHours(): void
    {
        foreach (range(0, 6) as $day) {
            $weekend = in_array($day, [0, 6], true);

            OperatingHour::create([
                'owner_type' => null,
                'owner_id' => null,
                'day_of_week' => $day,
                'is_closed' => $weekend,
                'opens_at' => $weekend ? null : '09:00',
                'closes_at' => $weekend ? null : '17:00',
            ]);
        }
    }

    /**
     * Valid payload for the fault report form.
     *
     * @return array<string, mixed>
     */
    protected function dataTiketSah(array $overrides = []): array
    {
        return array_merge([
            'lokasi_id' => $this->lokasi->id,
            'kategori_masalah' => 'TIDAK-HIDUP',
            'keutamaan' => 'P2',
            'keterangan' => 'Komputer tidak menyala selepas gangguan elektrik semalam.',
            'telefon_hubungan' => '0123456789',
        ], $overrides);
    }
}
