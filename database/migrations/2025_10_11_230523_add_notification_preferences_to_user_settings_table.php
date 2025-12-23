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
        Schema::table('user_settings', function (Blueprint $table) {
            // Individual notification preferences
            $table->boolean('notify_redirect_suspicious')->default(true)->after('telegram_chat_id');
            $table->boolean('notify_redirect_domain_change')->default(true)->after('notify_redirect_suspicious');
            $table->boolean('notify_redirect_unexpected')->default(true)->after('notify_redirect_domain_change');
            $table->boolean('notify_content_suspicious')->default(true)->after('notify_redirect_unexpected');
            $table->boolean('notify_connection')->default(true)->after('notify_content_suspicious');
            $table->boolean('notify_ssl')->default(true)->after('notify_connection');
            $table->boolean('notify_dns')->default(true)->after('notify_ssl');
            $table->boolean('notify_timeout')->default(true)->after('notify_dns');
            $table->boolean('notify_http')->default(true)->after('notify_timeout');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->dropColumn([
                'notify_redirect_suspicious',
                'notify_redirect_domain_change',
                'notify_redirect_unexpected',
                'notify_content_suspicious',
                'notify_connection',
                'notify_ssl',
                'notify_dns',
                'notify_timeout',
                'notify_http',
            ]);
        });
    }
};