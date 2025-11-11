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
        Schema::create(config('tables.saved_profiles'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('saver_user_id')->constrained(config('tables.users'));
            $table->foreignId('saved_user_id')->constrained(config('tables.users'));
            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_profiles');
    }
};
