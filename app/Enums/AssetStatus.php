<?php

namespace App\Enums;

enum AssetStatus: string
{
    case Simpanan = 'simpanan';
    case Digunakan = 'digunakan';
    case DalamPembaikan = 'dalam_pembaikan';
    case TidakAktif = 'tidak_aktif';
    case Dilupuskan = 'dilupuskan';

    /**
     * Display label shown in the administration screens (FR-AST-07).
     */
    public function label(): string
    {
        return match ($this) {
            self::Simpanan => 'Dalam simpanan',
            self::Digunakan => 'Sedang digunakan',
            self::DalamPembaikan => 'Dalam pembaikan',
            self::TidakAktif => 'Tidak aktif',
            self::Dilupuskan => 'Dilupuskan',
        };
    }

    /**
     * Tailwind classes for the status badge. The label is always rendered
     * next to the colour, so status never relies on colour alone (NFR-C02).
     */
    public function badge(): string
    {
        return match ($this) {
            self::Simpanan => 'bg-slate-100 text-slate-700',
            self::Digunakan => 'bg-emerald-50 text-emerald-700',
            self::DalamPembaikan => 'bg-amber-50 text-amber-700',
            self::TidakAktif => 'bg-slate-200 text-slate-600',
            self::Dilupuskan => 'bg-rose-50 text-rose-700',
        };
    }
}
