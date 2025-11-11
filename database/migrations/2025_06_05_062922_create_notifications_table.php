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
        Schema::dropIfExists(config('tables.notifications'));
        Schema::create(config('tables.notifications'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->constrained(config('tables.users','users'))->onDelete('cascade');
            $table->string('type');
            $table->string('title');
            $table->string('thumbnail')->nullable();
            $table->text('message');
            $table->json('meta')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.notifications'));
    }
};
