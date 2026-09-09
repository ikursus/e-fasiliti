<?php

namespace App\Enums;

enum LocationLevel: string
{
    case Kampus = 'kampus';
    case Bangunan = 'bangunan';
    case Tingkat = 'tingkat';
    case Ruang = 'ruang';

    /**
     * Whether this level sits at the top of the hierarchy (FR-ORG-01).
     */
    public function isRoot(): bool
    {
        return $this === self::Kampus;
    }

    /**
     * The level a parent must have for a location at this level.
     */
    public function parentLevel(): ?self
    {
        return match ($this) {
            self::Kampus => null,
            self::Bangunan => self::Kampus,
            self::Tingkat => self::Bangunan,
            self::Ruang => self::Tingkat,
        };
    }

    /**
     * Display label shown in the administration screens.
     */
    public function label(): string
    {
        return match ($this) {
            self::Kampus => 'Kampus',
            self::Bangunan => 'Bangunan',
            self::Tingkat => 'Tingkat',
            self::Ruang => 'Ruang',
        };
    }

    /**
     * Backing values in hierarchy order, top to bottom.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $level) => $level->value, self::cases());
    }
}
