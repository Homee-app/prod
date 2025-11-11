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
            $table->unsignedMediumInteger('chat_count');
            $table->dateTime('week_start_date')->nullable();
            $table->tinyInteger('is_subscribed')->after('otp_expires_at')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('chat_count');
            $table->dropColumn('week_start_date');
            $table->dropColumn('is_subscribed');
        });
    }
};
