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
        Schema::create(config('tables.roles'), function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();

            $table->softDeletes();
        });

        Schema::create(config('tables.users'), function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email');
            $table->string('password');
            $table->unsignedTinyInteger('login_type')->default(1)->comment('1 = email, 2 = google, 3 = Apple Id');
            $table->unsignedTinyInteger('device_type')->default(1)->comment('1 = Android, 2 = IOS')->nullable();
            $table->string('device_id')->nullable();
            $table->date('dob')->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('partner_profile_photo')->nullable();
            $table->string('forgot_token')->nullable();
            $table->unsignedBigInteger('role')->default(2)->comment('1 = Admin, 2 = tenant, 3 = owner');
            $table->tinyInteger('status')->default(1)->comment('0 = Inactive, 1 = Active');
            $table->boolean('is_blocked')->default(false)->comment('0 = Not Blocked, 1 = Blocked');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('referral_code')->nullable();
            $table->foreignId('referred_by_user_id')->nullable()->constrained(config('tables.users'))->nullOnDelete();
            $table->boolean('profile_completed')->default(false);
            $table->boolean('is_identity_verified')->default(false);
            $table->timestamp('identity_verified_at')->nullable();
            $table->string('otp_code')->nullable();
            $table->timestamp('otp_expires_at')->nullable();

            $table->decimal('latitude', 10, 7)->nullable()->comment('User current latitude');
            $table->decimal('longitude', 10, 7)->nullable()->comment('User current longitude');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('role')
                ->references('id')
                ->on(config('tables.roles'))
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->index(['role']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();

            $table->softDeletes();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('tables.roles'));
        Schema::dropIfExists(config('tables.users'));
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
