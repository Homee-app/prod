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
        Schema::create(config('tables.tenant_profiles'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique();
            $table->boolean('is_teamup')->default(false);
            $table->tinyInteger('morning_vs_night')->nullable();
            $table->tinyInteger('cleanliness_preference')->nullable();
            $table->tinyInteger('introversion_vs_extroversion')->nullable();
            $table->tinyInteger('temperature_sensitivity')->nullable();
            $table->tinyInteger('cooking_vs_eating_out')->nullable();
            $table->tinyInteger('homebody_vs_outgoing')->nullable();
            $table->tinyInteger('sharing_preference')->nullable();
            $table->tinyInteger('social_hosting_preference')->nullable();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on(config('tables.users'))->onDelete('cascade');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.tenant_profiles'));
    }
};
