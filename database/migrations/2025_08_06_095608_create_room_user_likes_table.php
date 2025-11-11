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
        Schema::dropIfExists(config('tables.tenant_likes','room_tenant_likes'));
        Schema::create(config('tables.tenant_likes','room_tenant_likes'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->constrained(config('tables.users','users'))->onDelete('cascade')->comment('Foreign ID of Users Table');
            $table->unsignedBigInteger('room_id')->constrained(config('tables.rooms','rooms'))->onDelete('cascade');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            $table->softDeletes();
            $table->unique(['tenant_id', 'room_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.tenant_likes'));
    }
};
