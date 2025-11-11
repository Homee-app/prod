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
        Schema::create(config('tables.message_requests'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained(config('tables.users'));
            $table->foreignId('receiver_id')->constrained(config('tables.users'));
            $table->enum('status', ['pending', 'accepted', 'declined']);
            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_requests');
    }
};
