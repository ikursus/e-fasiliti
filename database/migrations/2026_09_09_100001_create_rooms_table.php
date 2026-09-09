<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FR-BLK-01, FR-BLK-02, FR-BLK-06: the meeting room catalogue with its
     * per-room booking rules. Room-specific operating hours live in the
     * polymorphic operating_hours table, not on this table.
     */
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 150);
            $table->foreignId('location_id')->constrained('locations')->restrictOnDelete();
            $table->unsignedInteger('base_capacity');
            $table->boolean('requires_approval')->default(false);
            $table->json('allowed_roles')->nullable();
            $table->unsignedInteger('min_duration_minutes')->default(30);
            $table->unsignedInteger('max_duration_minutes')->default(480);
            $table->unsignedInteger('buffer_before_minutes')->default(0);
            $table->unsignedInteger('buffer_after_minutes')->default(0);
            $table->string('qr_code', 100)->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
