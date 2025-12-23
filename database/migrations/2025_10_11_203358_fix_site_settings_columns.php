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
        Schema::table('site_settings', function (Blueprint $table) {
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('site_settings', 'check_interval_minutes')) {
                $table->integer('check_interval_minutes')->default(10)->after('logo_path');
            }
            if (!Schema::hasColumn('site_settings', 'last_auto_check_at')) {
                $table->timestamp('last_auto_check_at')->nullable()->after('check_interval_minutes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            // Only drop columns if they exist
            if (Schema::hasColumn('site_settings', 'check_interval_minutes')) {
                $table->dropColumn('check_interval_minutes');
            }
            if (Schema::hasColumn('site_settings', 'last_auto_check_at')) {
                $table->dropColumn('last_auto_check_at');
            }
        });
    }
};
