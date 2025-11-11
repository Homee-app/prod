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
        Schema::dropIfExists(config('tables.user_blocks','user_blocks'));
        Schema::create(config('tables.user_blocks','user_blocks'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->constrained(config('tables.users','users'))->onDelete('cascade');
            $table->unsignedBigInteger('blocked_user_id')->constrained(config('tables.users','users'))->onDelete('cascade');
            
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            $table->softDeletes();
            $table->unique(['user_id', 'blocked_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.user_blocks','user_blocks'));
    }
};
