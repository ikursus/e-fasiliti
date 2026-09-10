<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Widen audit_logs.record_id so it can hold a non-numeric primary key.
     *
     * SystemSetting is keyed by its string `key`, so auditing a settings save
     * wrote "chatbot.enabled" into an unsignedBigInteger column and MySQL
     * rejected the insert with "Incorrect integer value". Existing numeric
     * ids convert to their string form untouched.
     */
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('record_id', 191)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('record_id')->nullable()->change();
        });
    }
};
