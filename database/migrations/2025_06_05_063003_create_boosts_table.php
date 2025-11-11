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
        Schema::create(config('tables.boosts'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(config('tables.users'));
            $table->unsignedTinyInteger('type')->comment('1 = profile, 2 = listing');
            $table->timestamp('start_time');
            $table->timestamp('end_time');
            $table->timestamp('purchased_at');
            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boosts');
    }
};
