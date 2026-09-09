<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('password');
            $table->foreignId('organization_unit_id')->nullable()->after('is_active')
                ->constrained('organization_units')->nullOnDelete();
            $table->foreignId('primary_location_id')->nullable()->after('organization_unit_id')
                ->constrained('locations')->nullOnDelete();
            $table->foreignId('delegate_user_id')->nullable()->after('primary_location_id')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('delegate_start_at')->nullable()->after('delegate_user_id');
            $table->timestamp('delegate_end_at')->nullable()->after('delegate_start_at');
            $table->timestamp('last_login_at')->nullable()->after('delegate_end_at');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');

            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['delegate_user_id']);
            $table->dropForeign(['primary_location_id']);
            $table->dropForeign(['organization_unit_id']);
            $table->dropIndex(['is_active']);
            $table->dropColumn([
                'is_active',
                'organization_unit_id',
                'primary_location_id',
                'delegate_user_id',
                'delegate_start_at',
                'delegate_end_at',
                'last_login_at',
                'last_login_ip',
            ]);
        });
    }
};
