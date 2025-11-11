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
        Schema::create(config('tables.question_screens'), function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('Screen title');
            $table->string('slug')->unique()->comment('Unique slug for screen');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.question_screens'));
    }
};
