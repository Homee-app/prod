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
        Schema::create(config('tables.golden_keys'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(config('tables.users'));
            $table->integer('key_count');
            $table->unsignedTinyInteger('type')->comment('1 = free, 2 = purchased, 3 = priority');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('purchased_at')->nullable();
            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('golden_keys');
    }
};
