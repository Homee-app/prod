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
        Schema::create(config('tables.question_answers_user'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->comment('User who answered the question')->constrained(config('tables.users'));
            $table->foreignId('question_id')->comment('Question that was answered')->constrained(config('tables.questions'));
            $table->foreignId('option_id')->nullable()->comment('Selected option if applicable')->constrained(config('tables.question_options'));
            $table->text('answer')->nullable()->comment('Direct text answer if applicable');
            $table->boolean('for_partner')->default(false)->comment('Indicates if the answer is for the partner');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_answers_user');
    }
};
