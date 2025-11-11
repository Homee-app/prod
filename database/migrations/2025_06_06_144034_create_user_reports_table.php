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
        Schema::create(config('tables.user_reports'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->comment('User who submitted the report')->constrained(config('tables.users'));
            $table->foreignId('reported_user_id')->nullable()->comment('User being reported, if applicable')->constrained(config('tables.users'));
            $table->foreignId('reported_property_id')->nullable()->comment('Property being reported, if applicable')->constrained(config('tables.properties'));
            $table->foreignId('report_id')->comment('Type of report (e.g. Harassment)')->constrained(config('tables.reports'));
            $table->text('comment')->nullable()->comment('Additional details provided by reporter');
            $table->timestamps();

            $table->softDeletes();
        });               
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.user_reports'));
    }
};
