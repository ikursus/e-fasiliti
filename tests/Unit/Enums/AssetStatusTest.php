<?php

namespace Tests\Unit\Enums;

use App\Enums\AssetStatus;
use PHPUnit\Framework\TestCase;

class AssetStatusTest extends TestCase
{
    public function test_it_exposes_the_five_statuses_from_fr_ast_07(): void
    {
        $this->assertSame(
            ['simpanan', 'digunakan', 'dalam_pembaikan', 'tidak_aktif', 'dilupuskan'],
            array_map(fn (AssetStatus $status) => $status->value, AssetStatus::cases()),
        );
    }

    public function test_labels_are_in_malay(): void
    {
        $this->assertSame('Dalam simpanan', AssetStatus::Simpanan->label());
        $this->assertSame('Sedang digunakan', AssetStatus::Digunakan->label());
        $this->assertSame('Dalam pembaikan', AssetStatus::DalamPembaikan->label());
        $this->assertSame('Tidak aktif', AssetStatus::TidakAktif->label());
        $this->assertSame('Dilupuskan', AssetStatus::Dilupuskan->label());
    }

    public function test_every_status_has_a_badge_style_to_pair_with_its_text_label(): void
    {
        foreach (AssetStatus::cases() as $status) {
            $this->assertStringContainsString('text-', $status->badge());
        }
    }
}
