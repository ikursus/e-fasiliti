<?php

namespace App\Enums;

enum AssetEventType: string
{
    case Didaftar = 'didaftar';
    case PindahLokasi = 'pindah_lokasi';
    case TukarPemilik = 'tukar_pemilik';
    case TukarStatus = 'tukar_status';
    case NaikTaraf = 'naik_taraf';
    case Dilupuskan = 'dilupuskan';

    /**
     * Display label for the asset history table (FR-AST-06).
     */
    public function label(): string
    {
        return match ($this) {
            self::Didaftar => 'Didaftar',
            self::PindahLokasi => 'Pindah lokasi',
            self::TukarPemilik => 'Tukar pemilik',
            self::TukarStatus => 'Tukar status',
            self::NaikTaraf => 'Naik taraf',
            self::Dilupuskan => 'Dilupuskan',
        };
    }
}
