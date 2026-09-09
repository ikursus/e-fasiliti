<?php

namespace Tests\Unit\Enums;

use App\Enums\BookingStatus;
use PHPUnit\Framework\TestCase;

class BookingStatusTest extends TestCase
{
    public function test_it_defines_the_eight_statuses_from_the_drd(): void
    {
        $this->assertCount(8, BookingStatus::cases());

        $this->assertEqualsCanonicalizing(
            [
                'draf',
                'menunggu_kelulusan',
                'disahkan',
                'ditolak',
                'daftar_masuk',
                'selesai',
                'dibatalkan',
                'dilepaskan',
            ],
            array_map(fn (BookingStatus $status) => $status->value, BookingStatus::cases()),
        );
    }

    public function test_every_status_has_a_malay_label(): void
    {
        foreach (BookingStatus::cases() as $status) {
            $this->assertNotSame($status->value, $status->label());
            $this->assertNotSame('', $status->label());
        }

        $this->assertSame('Menunggu kelulusan', BookingStatus::MenungguKelulusan->label());
        $this->assertSame('Dibatalkan', BookingStatus::Dibatalkan->label());
    }

    public function test_exactly_three_statuses_hold_a_slot(): void
    {
        $this->assertEqualsCanonicalizing(
            ['menunggu_kelulusan', 'disahkan', 'daftar_masuk'],
            BookingStatus::slotHoldingValues(),
        );

        foreach ([BookingStatus::Draf, BookingStatus::Ditolak, BookingStatus::Dibatalkan] as $status) {
            $this->assertNotContains($status, BookingStatus::slotHolding());
        }
    }
}
