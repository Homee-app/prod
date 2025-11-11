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
        Schema::dropIfExists(config('tables.room_boost_usages'));
        Schema::create(config('tables.room_boost_usages'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_id');
            $table->dateTime('used_at');
            $table->dateTime('expires_at');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.room_boost_usages'));
    }
};
