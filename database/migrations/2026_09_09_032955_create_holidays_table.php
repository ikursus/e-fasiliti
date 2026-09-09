<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FR-ADM-02: public holidays and days on which nothing may be booked.
     */
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('name', 150);
            $table->enum('type', ['cuti_umum', 'hari_tanpa_tempahan'])->default('cuti_umum');
            $table->boolean('recurs_annually')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['date', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
