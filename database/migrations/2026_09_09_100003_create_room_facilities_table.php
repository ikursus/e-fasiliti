<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FR-BLK-04: a room's fixed facilities, recorded from the editable
     * reference list (reference_values type kemudahan_bilik) rather than a
     * free-text field.
     */
    public function up(): void
    {
        Schema::create('room_facilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->string('facility_code', 50);
            $table->timestamps();

            $table->unique(['room_id', 'facility_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_facilities');
    }
};
