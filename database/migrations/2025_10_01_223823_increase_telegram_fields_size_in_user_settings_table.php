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
            $table->text('telegram_bot_token')->nullable()->change();
            $table->text('telegram_chat_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->string('telegram_bot_token', 255)->nullable()->change();
            $table->string('telegram_chat_id', 255)->nullable()->change();
        });
    }
};
