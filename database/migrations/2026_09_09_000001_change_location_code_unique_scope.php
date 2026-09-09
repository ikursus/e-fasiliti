<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * DRD §4.1: a location code is unique within its parent, not globally.
     * Root-level uniqueness is enforced in the application layer, because
     * both MySQL and SQLite treat NULL parent values as distinct.
     */
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropUnique('locations_code_unique');
            $table->unique(['parent_id', 'code']);
        });
    }

    /**
     * Safe to run only while no two locations under different parents
     * share a code. Once such rows exist, reinstating the global unique
     * index will fail with a duplicate-entry QueryException.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropUnique(['parent_id', 'code']);
            $table->unique('code');
        });
    }
};
