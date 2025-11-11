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
        Schema::create(config('tables.user_question_privacies'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(config('tables.users'))->onDelete('cascade');
            $table->foreignId('question_id')->constrained(config('tables.questions'))->onDelete('cascade');
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.user_question_privacies'));
    }
};
