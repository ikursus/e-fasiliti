<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FR-ADM-07: editable reference lists shared by several modules.
     */
    public function up(): void
    {
        Schema::create('reference_values', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50)->index();
            $table->string('code', 50);
            $table->string('label', 150);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['type', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reference_values');
    }
};
