<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Allow the "rahsia" value type, used for settings held encrypted at rest
     * such as the Gemini API key. The column is an enum, so MySQL and the
     * SQLite check constraint both reject an unlisted value.
     */
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->enum('value_type', ['teks', 'nombor', 'boolean', 'json', 'rahsia'])
                ->default('teks')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->enum('value_type', ['teks', 'nombor', 'boolean', 'json'])
                ->default('teks')
                ->change();
        });
    }
};
