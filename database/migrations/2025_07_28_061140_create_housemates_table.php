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
        if(!Schema::hasTable(config('tables.housemates'))){
            Schema::create(config('tables.housemates'), function (Blueprint $table) {
                $table->id();
                $table->foreignId('property_id')->constrained(config('tables.properties','properties'))->onDelete('cascade');
                $table->boolean('status')->default(true);
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
                $table->softDeletes();
            });
        }else{
            
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.housemates'));
    }
};
