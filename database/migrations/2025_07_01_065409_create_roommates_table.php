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
        Schema::create(config('tables.roommates'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained(config('tables.properties'))->onDelete('cascade');
            
            $table->string('name')->nullable();
            $table->tinyInteger('age')->nullable();
            $table->string('gender')->nullable(); 
            $table->string('ethnicity')->nullable();
            $table->string('photo')->nullable(); 
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.roommates'));
    }
};
