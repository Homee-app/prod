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
        if(!Schema::hasTable(config('tables.question_answers_user'))){
            Schema::table(config('tables.question_answers_user'), function (Blueprint $table) {
                $table->foreignId('property_id')->nullable()->constrained(config('tables.properties','properties'))->onDelete('cascade');
                $table->foreignId('room_id')->nullable()->constrained(config('tables.rooms','rooms'))->onDelete('cascade');
                $table->foreignId('housemate_id')->nullable()->constrained(config('tables.housemates','housemates'))->onDelete('cascade');
            });
        }else{
            
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('question_answers_user', function (Blueprint $table) {
            //
        });
    }
};
