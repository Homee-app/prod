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
        Schema::create(config('tables.referral_requests'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(config('tables.users'))->comment('User who sent the referral');
            $table->string('email')->comment('Email address the referral was sent to');
            $table->string('referral_code')->nullable()->comment('Optional referral code shared');
            $table->timestamp('sent_at')->nullable()->comment('Datetime when referral was sent');
            $table->timestamps();

            $table->softDeletes();
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.referral_requests'));
    }
};
