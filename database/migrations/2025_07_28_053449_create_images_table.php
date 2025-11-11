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
        Schema::dropIfExists(config('tables.images'));
        if(!Schema::hasTable(config('tables.images'))){
            Schema::create(config('tables.images'), function (Blueprint $table) {
                $table->id();
                $table->string('path');
                $table->unsignedTinyInteger('type',)->default(0)->comment(" 1 => video , 0 => image");
                $table->string('thumbnail_path',)->nullable();
                $table->morphs('taggable');
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.images'));
    }
};
