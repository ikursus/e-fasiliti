<?php

namespace App\Enums;

/**
 * Ticket priorities from FR-ADM-05 / BRS §6. The minute targets here are the
 * conservative starting values; configured `sla.*` settings override them
 * without a code change.
 */
enum TicketPriority: string
{
    case P1 = 'P1';
    case P2 = 'P2';
    case P3 = 'P3';
    case P4 = 'P4';

    public function label(): string
    {
        return match ($this) {
            self::P1 => 'P1 Kritikal',
            self::P2 => 'P2 Tinggi',
            self::P3 => 'P3 Sederhana',
            self::P4 => 'P4 Rendah',
        };
    }

    /**
     * The user-facing disturbance level from the report form (UI spec §4.4).
     * Users pick in their own words; the system maps to the internal priority.
     */
    public function tahapGangguan(): string
    {
        return match ($this) {
            self::P1 => 'Perkhidmatan terhenti untuk ramai pengguna',
            self::P2 => 'Tidak boleh bekerja',
            self::P3 => 'Ada jalan sementara',
            self::P4 => 'Gangguan kecil',
        };
    }

    public function tindakBalasMinitLalai(): int
    {
        return match ($this) {
            self::P1 => 30,
            self::P2 => 120,
            self::P3 => 240,
            self::P4 => 480,
        };
    }

    public function pemulihanMinitLalai(): int
    {
        return match ($this) {
            self::P1 => 240,
            self::P2 => 480,
            self::P3 => 1440,
            self::P4 => 2400,
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::P1 => 'bg-rose-100 text-rose-800 ring-rose-600/20',
            self::P2 => 'bg-orange-100 text-orange-800 ring-orange-600/20',
            self::P3 => 'bg-yellow-100 text-yellow-800 ring-yellow-600/20',
            self::P4 => 'bg-slate-100 text-slate-600 ring-slate-500/20',
        };
    }

    /**
     * @return array<int, array{value: string, label: string, gangguan: string}>
     */
    public static function pilihanGangguan(): array
    {
        return array_map(
            fn (self $priority) => [
                'value' => $priority->value,
                'label' => $priority->label(),
                'gangguan' => $priority->tahapGangguan(),
            ],
            self::cases(),
        );
    }
}
