<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The TEMPAHAN entity from DRD §4.2. Created during M04 because
     * FR-BLK-08 must list upcoming bookings before deactivating a room;
     * the booking engine that writes to it arrives with M05. The series
     * column (tempahan berulang, FR-TMP-10) is added by M05's plan.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no', 20)->unique();
            $table->foreignId('room_id')->constrained('rooms')->restrictOnDelete();
            $table->foreignId('booked_by_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->unsignedInteger('participant_count');
            $table->foreignId('room_layout_id')->nullable()->constrained('room_layouts')->nullOnDelete();
            $table->enum('status', [
                'draf',
                'menunggu_kelulusan',
                'disahkan',
                'ditolak',
                'daftar_masuk',
                'selesai',
                'dibatalkan',
                'dilepaskan',
            ])->default('draf');
            $table->string('cancellation_reason', 500)->nullable();
            $table->boolean('cancelled_late')->default(false);
            $table->timestamps();

            $table->index(['room_id', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
