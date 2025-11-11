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
        Schema::create(config('tables.identity_verifications'), function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained(config('tables.users'));
            $table->unsignedTinyInteger('id_type')->comment("1 = Driver's License, 2 = Passport, 3 = Identity Card");
            $table->string('doc_front');
            $table->string('doc_back')->nullable();
            $table->string('user_photo')->nullable();
            $table->timestamp('submission_date');
            $table->unsignedTinyInteger('status')->comment("1 = pending, 2 = approved, 3 = rejected, 4 = resubmit_required");
            $table->string('admin_notes')->nullable();
            $table->foreignId('reviewed_by_admin_id')->nullable()->constrained(config('tables.users'));
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('identity_verifications');
    }
};
