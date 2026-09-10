<?php

namespace Database\Factories;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Location;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterMaking(function (Ticket $ticket): void {
            $ticket->no_tiket ??= $this->generateNumber();
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dibuka = $this->faker->dateTimeBetween('-14 days', 'now');

        return [
            'lokasi_id' => Location::factory(),
            'pelapor_id' => User::factory(),
            'kategori_masalah' => $this->faker->randomElement(['TIDAK-HIDUP', 'RANGKAIAN', 'PERISIAN', 'SKRIN', 'CETAK']),
            'keterangan' => $this->faker->sentence(),
            'telefon_hubungan' => $this->faker->numerify('01########'),
            'keutamaan' => TicketPriority::P3,
            'status' => TicketStatus::Baharu,
            'masa_dibuka' => $dibuka,
            'sasaran_tindak_balas' => $dibuka,
            'sasaran_pemulihan' => $dibuka,
            'minit_jeda_sla' => 0,
            'penutupan_automatik' => false,
        ];
    }

    public function diagih(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TicketStatus::Diagih,
            'juruteknik_id' => $attributes['juruteknik_id'] ?? User::factory(),
        ]);
    }

    public function dalamTindakan(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TicketStatus::DalamTindakan,
            'juruteknik_id' => $attributes['juruteknik_id'] ?? User::factory(),
            'masa_tindak_balas_pertama' => $attributes['masa_dibuka'] ?? now(),
        ]);
    }

    public function menungguVendor(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TicketStatus::MenungguVendor,
            'juruteknik_id' => $attributes['juruteknik_id'] ?? User::factory(),
            'no_rujukan_vendor' => 'VEN-'.mb_strtoupper($this->faker->lexify('????')),
            'vendor_nama' => $this->faker->company(),
            'jeda_mula_pada' => now(),
        ]);
    }

    public function menungguPengesahan(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TicketStatus::MenungguPengesahan,
            'juruteknik_id' => $attributes['juruteknik_id'] ?? User::factory(),
            'diagnosis' => $this->faker->sentence(),
            'tindakan' => $this->faker->sentence(),
            'masa_tindak_balas_pertama' => $attributes['masa_dibuka'] ?? now(),
            'masa_kerja_selesai' => now(),
        ]);
    }

    public function ditutup(?bool $slaDipatuhi = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TicketStatus::Ditutup,
            'juruteknik_id' => $attributes['juruteknik_id'] ?? User::factory(),
            'masa_tindak_balas_pertama' => $attributes['masa_dibuka'] ?? now(),
            'masa_kerja_selesai' => $attributes['masa_dibuka'] ?? now(),
            'masa_ditutup' => now(),
            'sla_dipatuhi' => $slaDipatuhi ?? $this->faker->boolean(),
        ]);
    }

    public function dibatalkan(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TicketStatus::Dibatalkan,
            'sebab_batal' => 'Laporan tidak sah.',
        ]);
    }

    public function keutamaan(TicketPriority $priority): static
    {
        return $this->state(fn () => ['keutamaan' => $priority]);
    }

    public function melanggarSla(): static
    {
        return $this->state(fn (array $attributes) => [
            'masa_dibuka' => now()->subDays(5),
            'sasaran_pemulihan' => now()->subDays(2),
        ]);
    }

    private function generateNumber(): string
    {
        static $sequence = 0;

        return 'TKT-'.now()->format('Ym').'-'.str_pad((string) (++$sequence), 5, '0', STR_PAD_LEFT);
    }
}
