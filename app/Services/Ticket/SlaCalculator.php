<?php

namespace App\Services\Ticket;

use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Pure SLA arithmetic (SDD §5.2). It receives a WorkingCalendar and never
 * touches the database, so every rule — working hours, public holidays and
 * paused time — is unit testable without fixtures.
 */
class SlaCalculator
{
    public function __construct(private readonly WorkingCalendar $calendar) {}

    /**
     * Response and recovery targets for a ticket opened at $dibuka.
     *
     * @return array{sasaran_tindak_balas: Carbon, sasaran_pemulihan: Carbon}
     */
    public function kiraSasaran(CarbonInterface $dibuka, int $tindakBalasMinit, int $pemulihanMinit): array
    {
        return [
            'sasaran_tindak_balas' => $this->calendar->addWorkingMinutes($dibuka, max(0, $tindakBalasMinit)),
            'sasaran_pemulihan' => $this->calendar->addWorkingMinutes($dibuka, max(0, $pemulihanMinit)),
        ];
    }

    /**
     * Working minutes consumed, excluding paused time (FR-TKT-15).
     */
    public function minitBerlalu(CarbonInterface $dibuka, ?CarbonInterface $hingga = null, int $minitJeda = 0): int
    {
        $hingga ??= Carbon::now();

        return max(0, $this->calendar->workingMinutesBetween($dibuka, $hingga) - $minitJeda);
    }

    /**
     * Percentage of the recovery window consumed (FR-TKT-20). Values above
     * 100 mean the target is breached. Takes plain values, not a model, so
     * the arithmetic stays testable without a database connection.
     */
    public function peratusanTerpakai(
        CarbonInterface $dibuka,
        CarbonInterface $sasaranPemulihan,
        int $minitJeda = 0,
        ?CarbonInterface $sekarang = null,
    ): int {
        $jumlah = $this->calendar->workingMinutesBetween($dibuka, $sasaranPemulihan);

        if ($jumlah <= 0) {
            return 0;
        }

        $berlalu = $this->minitBerlalu($dibuka, $sekarang, $minitJeda);

        return (int) round(($berlalu / $jumlah) * 100);
    }

    /**
     * Whether the recovery target is breached at the given moment.
     */
    public function melanggar(
        CarbonInterface $dibuka,
        CarbonInterface $sasaranPemulihan,
        int $minitJeda = 0,
        ?CarbonInterface $sekarang = null,
    ): bool {
        return $this->peratusanTerpakai($dibuka, $sasaranPemulihan, $minitJeda, $sekarang) >= 100;
    }
}
