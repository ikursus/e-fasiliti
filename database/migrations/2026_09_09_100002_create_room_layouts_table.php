<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FR-BLK-03: several layouts per room, each with its own capacity
     * (classroom 30, U-shape 18, ...). layout_code points at an active
     * reference_values row of type susun_atur_bilik.
     */
    public function up(): void
    {
        Schema::create('room_layouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->string('layout_code', 50);
            $table->unsignedInteger('capacity');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['room_id', 'layout_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_layouts');
    }
};
