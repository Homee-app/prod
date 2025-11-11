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
        Schema::dropIfExists(config('tables.owner_likes','owner_likes'));
        Schema::create(config('tables.owner_likes','owner_likes'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_id')->constrained(config('tables.property_owners','property_owners'))->onDelete('cascade');
            $table->unsignedBigInteger('tenant_id')->constrained(config('tables.users','users'))->onDelete('cascade');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            $table->softDeletes();
            $table->unique(['owner_id','tenant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.owner_likes'));
    }
};
