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
        Schema::create(config('tables.property_owners'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained(config('tables.users'))->onDelete('cascade');
            $table->enum('living_situation', ['alone', 'with_partner'])->nullable();
            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_owners');
    }
};
