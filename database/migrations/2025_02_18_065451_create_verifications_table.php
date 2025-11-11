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
        Schema::create(config('tables.verifications'), function (Blueprint $table) {
            $table->id();
            $table->string('otp');
            $table->string('value');
            $table->tinyInteger('type')->nullable()->comment('1 = email, 2 = phone');
            $table->tinyInteger('status')->default(0)->comment('0 = unverified, 1 = verified');
            $table->string('device_type')->nullable();
            $table->string('device_id')->nullable();
            $table->timestamp('expires_at')->nullable()->comment('OTP expiry time');
            $table->tinyInteger('otp_type')->nullable()->comment(' 1 = signup, 2 = forgot password');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verifications');
    }
};
