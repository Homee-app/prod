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
        Schema::create(config('tables.referral_successes'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained(config('tables.users'))->comment('User who referred the friend');
            $table->foreignId('referred_user_id')->constrained(config('tables.users'))->comment('User who signed up using the referral');
            $table->string('referral_code')->nullable()->comment('Referral code used during sign-up');
            $table->timestamp('registered_at')->nullable()->comment('Datetime when referred user registered');
            $table->timestamps();

            $table->softDeletes();
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.referral_successes'));
    }
};
