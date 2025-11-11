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
        Schema::create(config('tables.questions'), function (Blueprint $table) {
            $table->id();
            $table->string('title_for_web')->nullable()->comment('Question title for web display');
            $table->string('slug')->nullable()->comment('Slug of title_for_web');
            $table->string('title_for_app')->comment('Question title for app display');
            $table->string('sub_title_for_app')->nullable()->comment('Sub Title of questions');
            $table->tinyInteger('type_for_app')->default(1)->comment('1 => single_choice, 2 => multiple_choice, 3 => text, 4 => slider ,5 => info');
            $table->tinyInteger('type_for_web')->nullable()->comment('1 => single_choice, 2 => multiple_choice, 3 => text, 4 => slider ,5 => info');
            $table->tinyInteger('showing_in')->default(1)->comment('1 => Web, 2 => App, 3 => Both');
            $table->integer('question_order_for_web')->nullable()->comment('Order for displaying question on web');
            $table->integer('question_order_for_app')->default(1)->comment('Order for displaying question on app');
            $table->tinyInteger('section')->default(1)->comment('1 = Tenant Profile, 2 = Property, 3 = Room, 4 = housemate');
            $table->tinyInteger('question_for')->nullable()->constrained(config('tables.question_screens'))->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
