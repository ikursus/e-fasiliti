<?php

namespace App\Enums;

/**
 * The eight booking statuses from the DRD §4.2 TEMPAHAN entity, following the
 * SRS lifecycle: draf -> menunggu_kelulusan -> disahkan -> daftar_masuk ->
 * selesai, with ditolak / dibatalkan / dilepaskan as terminal branches.
 */
enum BookingStatus: string
{
    case Draf = 'draf';
    case MenungguKelulusan = 'menunggu_kelulusan';
    case Disahkan = 'disahkan';
    case Ditolak = 'ditolak';
    case DaftarMasuk = 'daftar_masuk';
    case Selesai = 'selesai';
    case Dibatalkan = 'dibatalkan';
    case Dilepaskan = 'dilepaskan';

    /**
     * Display label shown in calendars, lists and screens.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draf => 'Draf',
            self::MenungguKelulusan => 'Menunggu kelulusan',
            self::Disahkan => 'Disahkan',
            self::Ditolak => 'Ditolak',
            self::DaftarMasuk => 'Daftar masuk',
            self::Selesai => 'Selesai',
            self::Dibatalkan => 'Dibatalkan',
            self::Dilepaskan => 'Dilepaskan',
        };
    }

    /**
     * Statuses that hold a real slot in the diary. FR-BLK-08 counts future
     * bookings in any of these states as affected when a room is
     * deactivated; the M05 conflict check will use the same list.
     *
     * @return array<int, self>
     */
    public static function slotHolding(): array
    {
        return [
            self::MenungguKelulusan,
            self::Disahkan,
            self::DaftarMasuk,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function slotHoldingValues(): array
    {
        return array_map(fn (self $status) => $status->value, self::slotHolding());
    }
}
