<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FR-ADM-06 and FR-NOT-07: editable message templates per channel and
     * language, with the placeholders each template is allowed to use.
     */
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100);
            $table->enum('channel', ['emel', 'dalam_aplikasi'])->default('emel');
            $table->string('locale', 5)->default('ms');
            $table->string('subject', 200);
            $table->text('body');
            $table->json('placeholders')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['key', 'channel', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
