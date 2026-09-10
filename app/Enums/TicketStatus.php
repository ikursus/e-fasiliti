<?php

namespace App\Enums;

/**
 * The ticket life cycle from SRS §M10. Transitions are enforced in
 * TicketService, not here — this enum only describes the states.
 */
enum TicketStatus: string
{
    case Baharu = 'baharu';
    case Diagih = 'diagih';
    case DalamTindakan = 'dalam_tindakan';
    case MenungguVendor = 'menunggu_vendor';
    case MenungguPengesahan = 'menunggu_pengesahan';
    case Ditutup = 'ditutup';
    case Dibatalkan = 'dibatalkan';

    /**
     * Malay label shown next to every status (NFR-C02: never colour alone).
     */
    public function label(): string
    {
        return match ($this) {
            self::Baharu => 'Baharu',
            self::Diagih => 'Diagih',
            self::DalamTindakan => 'Dalam Tindakan',
            self::MenungguVendor => 'Menunggu Vendor',
            self::MenungguPengesahan => 'Menunggu Pengesahan',
            self::Ditutup => 'Ditutup',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    /**
     * Tailwind classes following the status colour system in the UI spec §5.1.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Baharu => 'bg-amber-100 text-amber-800 ring-amber-600/20',
            self::Diagih => 'bg-blue-100 text-blue-800 ring-blue-600/20',
            self::DalamTindakan => 'bg-blue-100 text-blue-800 ring-blue-600/20',
            self::MenungguVendor => 'bg-orange-100 text-orange-800 ring-orange-600/20',
            self::MenungguPengesahan => 'bg-violet-100 text-violet-800 ring-violet-600/20',
            self::Ditutup => 'bg-emerald-100 text-emerald-800 ring-emerald-600/20',
            self::Dibatalkan => 'bg-slate-100 text-slate-600 ring-slate-500/20',
        };
    }

    /**
     * A ticket that still consumes SLA time or demands someone's attention.
     */
    public function isOpen(): bool
    {
        return ! in_array($this, [self::Ditutup, self::Dibatalkan], true);
    }
}
