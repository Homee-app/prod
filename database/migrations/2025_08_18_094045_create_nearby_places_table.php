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
        Schema::dropIfExists(config('tables.nearby_places','nearby_places'));
        Schema::create(config('tables.nearby_places','nearby_places'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id')->constrained(config('tables.properties','properties'))->onDelete('cascade');
            $table->string('type'); // Bus stop, Train station, etc
            $table->string('distance_text')->nullable();
            $table->string('duration_text')->nullable();
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
        Schema::dropIfExists(config('tables.nearby_places'));
    }
};
