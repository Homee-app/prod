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
        Schema::create(config('tables.question_options'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->comment('Foreign key to ft_questions')->constrained(config('tables.questions'));
            $table->string('label_for_app')->comment('Option label for app');
            $table->string('label_for_web')->comment('Option label for web');
            $table->string('instruction')->nullable()->comment('Instruction or helper text for option');
            $table->string('value')->nullable()->comment('Value to store/submit');
            $table->integer('min_val')->nullable()->comment('Minimum numeric value if applicable');
            $table->integer('max_val')->nullable()->comment('Maximum numeric value if applicable');
            $table->string('image')->nullable()->comment('Optional image for the option');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.question_options'));
    }
};
