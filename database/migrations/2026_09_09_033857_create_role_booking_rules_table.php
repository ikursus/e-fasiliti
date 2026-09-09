<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FR-ADM-03: minimum duration, maximum duration and how far ahead each
     * role may book. Consumed by the M05 booking engine.
     */
    public function up(): void
    {
        Schema::create('role_booking_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->unique()->constrained('roles')->cascadeOnDelete();
            $table->unsignedInteger('min_duration_minutes')->default(30);
            $table->unsignedInteger('max_duration_minutes')->default(240);
            $table->unsignedInteger('max_advance_days')->default(90);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_booking_rules');
    }
};
