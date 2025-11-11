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
        Schema::create(config('tables.user_identities'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
            $table->string('id_type'); 
            $table->string('front_of_id_path'); 
            $table->string('back_of_id_path')->nullable(); 
            $table->string('verification_status')->default('pending'); 
            $table->text('rejection_reason')->nullable(); 

            $table->timestamp('verified_at')->nullable(); 
            $table->timestamps();

            $table->unique(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.user_identities'));
    }
};
