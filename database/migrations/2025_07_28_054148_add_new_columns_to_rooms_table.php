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
        if(!Schema::hasTable(config('tables.rooms'))){
            Schema::table(config('tables.rooms'), function (Blueprint $table) {
                $table->boolean('status')->default(true)->change();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(config('tables.rooms'), function (Blueprint $table) {
            $table->dropIfExists('status');
        });
    }
};
