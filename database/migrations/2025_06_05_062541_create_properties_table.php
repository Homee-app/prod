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
        Schema::create(config('tables.properties'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained(config('tables.users'))->onDelete('cascade');
            $table->string('accommodation_type');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price_per_week', 10, 2);
            $table->decimal('bond_amount', 10, 2)->nullable();
            $table->boolean('bills_included')->default(false);
            $table->date('availability_date')->nullable();
            $table->string('location');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('acceptance_criteria')->nullable();
            $table->text('room_overview')->nullable();
            $table->json('features')->nullable();
            $table->json('home_preferences')->nullable();
            $table->enum('status', ['draft', 'published', 'rented', 'removed']);
            $table->boolean('owner_lives_here')->default(false);
            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
