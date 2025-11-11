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
        Schema::create(config('tables.referrals'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained(config('tables.users'));
            $table->foreignId('referred_user_id')->unique()->constrained(config('tables.users'));
            $table->string('referral_code_used')->nullable();
            $table->enum('status', ['pending', 'qualified', 'rewarded', 'cancelled']);
            $table->decimal('reward_amount', 10, 2)->nullable();
            $table->text('reward_details')->nullable();
            $table->timestamp('reward_granted_at')->nullable();
            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
