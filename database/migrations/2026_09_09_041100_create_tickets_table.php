<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * M10 — Tiket Aduan Kerosakan (FR-TKT-01 to FR-TKT-20).
     *
     * aset_id is reserved for the M09 inventory module: the ticket flow runs
     * without an asset today (FR-TKT-04) and gains QR lookup when M09 lands.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('no_tiket', 20)->unique();
            $table->foreignId('lokasi_id')->constrained('locations')->restrictOnDelete();
            $table->foreignId('aset_id')->nullable()->index();
            $table->foreignId('pelapor_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('juruteknik_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('kategori_masalah', 50);
            $table->text('keterangan');
            $table->string('telefon_hubungan', 30)->nullable();
            $table->string('keutamaan', 2);
            $table->string('status', 30)->index();
            $table->string('sebab_keutamaan', 500)->nullable();
            $table->string('sebab_batal', 500)->nullable();

            $table->string('no_rujukan_vendor', 50)->nullable();
            $table->string('vendor_nama', 200)->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('tindakan')->nullable();
            $table->decimal('kos_pembaikan', 10, 2)->nullable();
            $table->unsignedSmallInteger('masa_kerja_minit')->nullable();

            $table->timestamp('masa_dibuka');
            $table->timestamp('masa_tindak_balas_pertama')->nullable();
            $table->timestamp('masa_kerja_selesai')->nullable();
            $table->timestamp('masa_ditutup')->nullable();
            $table->timestamp('sasaran_tindak_balas');
            $table->timestamp('sasaran_pemulihan');
            $table->timestamp('jeda_mula_pada')->nullable();
            $table->unsignedInteger('minit_jeda_sla')->default(0);
            $table->boolean('sla_dipatuhi')->nullable();
            $table->timestamp('amaran_80_dihantar_pada')->nullable();
            $table->timestamp('amaran_100_dihantar_pada')->nullable();
            $table->boolean('penutupan_automatik')->default(false);

            $table->json('lampiran')->nullable();
            $table->timestamps();

            $table->index(['status', 'sasaran_pemulihan']);
            $table->index(['juruteknik_id', 'status']);
            $table->index(['pelapor_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
