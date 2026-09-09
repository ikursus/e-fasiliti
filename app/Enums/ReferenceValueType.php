<?php

namespace App\Enums;

/**
 * The five editable reference lists required by FR-ADM-07.
 */
enum ReferenceValueType: string
{
    case KategoriAset = 'kategori_aset';
    case JenisKerosakan = 'jenis_kerosakan';
    case SusunAturBilik = 'susun_atur_bilik';
    case KemudahanBilik = 'kemudahan_bilik';
    case UnitStok = 'unit_stok';

    public function label(): string
    {
        return match ($this) {
            self::KategoriAset => 'Kategori aset',
            self::JenisKerosakan => 'Jenis kerosakan',
            self::SusunAturBilik => 'Susun atur bilik',
            self::KemudahanBilik => 'Kemudahan bilik',
            self::UnitStok => 'Unit stok',
        };
    }

    /**
     * The module that consumes this list, shown as a hint in the UI.
     */
    public function consumer(): string
    {
        return match ($this) {
            self::KategoriAset => 'M09 Inventari Aset',
            self::JenisKerosakan => 'M10 Tiket Aduan',
            self::SusunAturBilik, self::KemudahanBilik => 'M04 Katalog Bilik',
            self::UnitStok => 'M13 Alat Ganti & Stok',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $type) => $type->value, self::cases());
    }
}
