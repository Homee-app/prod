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
        Schema::create(config('tables.reports'), function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('Title of the report reason, e.g. Harassment');
            $table->string('slug')->unique()->comment('Slug for report reason, e.g. harassment');
            $table->text('description')->nullable()->comment('Optional description for internal/admin use');
            $table->timestamps();

            $table->softDeletes();
        });
    } 

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.reports'));
    }
};
