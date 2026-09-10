<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * M09 — daftar induk aset ICT (FR-AST-01 hingga FR-AST-07, FR-AST-10).
     * Maps the ASET entity in docs-claude/05-DRD-data-requirements.md §4.3
     * onto the application conventions: integer keys, English snake_case
     * columns, Malay enum values.
     */
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number', 50)->unique();
            $table->foreignId('category_id')->constrained('reference_values')->restrictOnDelete();
            $table->string('brand', 100);
            $table->string('model', 100);
            $table->string('serial_number', 100)->nullable()->unique();
            $table->json('specifications')->nullable();
            $table->date('acquisition_date');
            $table->decimal('acquisition_cost', 12, 2)->nullable();
            $table->string('order_number', 50)->nullable();
            $table->date('warranty_start_date')->nullable();
            $table->unsignedSmallInteger('warranty_months')->nullable();
            $table->foreignId('location_id')->constrained('locations')->restrictOnDelete();
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['simpanan', 'digunakan', 'dalam_pembaikan', 'tidak_aktif', 'dilupuskan'])->default('simpanan');
            $table->string('mac_address', 20)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('hostname', 100)->nullable();
            $table->string('qr_code', 100)->unique();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('responsible_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
