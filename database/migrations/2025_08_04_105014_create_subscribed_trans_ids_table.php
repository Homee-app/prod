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
        if(!Schema::hasTable(config('tables.subscribed_trans_ids'))){
            Schema::create(config('tables.subscribed_trans_ids'), function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->text('transactionId')->nullable();
                $table->string('deviceType')->nullable();
                $table->longText('request_payload')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }else{
            
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.subscribed_trans_ids'));
    }
};
