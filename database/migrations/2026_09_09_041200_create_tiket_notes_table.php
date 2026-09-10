<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Progress notes on a ticket (FR-TKT-12). Notes flagged
     * boleh_dilihat_pelapor=false are internal to the ICT team.
     */
    public function up(): void
    {
        Schema::create('tiket_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->text('catatan');
            $table->boolean('boleh_dilihat_pelapor')->default(true);
            $table->json('lampiran')->nullable();
            $table->timestamps();

            $table->index(['ticket_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiket_notes');
    }
};
