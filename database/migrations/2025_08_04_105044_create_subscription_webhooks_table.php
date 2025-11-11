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
        if(!Schema::hasTable(config('tables.subscription_webhooks'))){
            Schema::create(config('tables.subscription_webhooks'), function (Blueprint $table) {
                $table->id();
                $table->text('originalTransactionId')->nullable();
                $table->string('notification_type')->nullable();
                $table->longText('data')->nullable();
                $table->string('type')->nullable();
                $table->tinyInteger('status')->default(1)->comment('1=>Pending, 2=>Proceed');
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
        Schema::dropIfExists(config('tables.subscription_webhooks'));
    }
};
