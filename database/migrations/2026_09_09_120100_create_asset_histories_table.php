<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * M09 — sejarah pergerakan aset (FR-AST-06). Maps the SEJARAH_ASET
     * entity in docs-claude/05-DRD-data-requirements.md §4.3. Rows belong
     * to their asset and follow it into deletion; the audit trail keeps the
     * long-lived record.
     */
    public function up(): void
    {
        Schema::create('asset_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->enum('event_type', ['didaftar', 'pindah_lokasi', 'tukar_pemilik', 'tukar_status', 'naik_taraf', 'dilupuskan']);
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->string('reason', 500)->nullable();
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['asset_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_histories');
    }
};
