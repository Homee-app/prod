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
       $columnsToDrop = [];
            foreach ([
                'title',
                'description',
                'price_per_week',
                'bond_amount',
                'bills_included',
                'availability_date',
                'acceptance_criteria',
                'room_overview',
                'features',
                'home_preferences',
                'owner_lives_here',
                'accommodation_type',
                'location',
            ] as $col) {
                if (Schema::hasColumn('properties', $col)) {
                    $columnsToDrop[] = $col;
                }
            }

            if (!empty($columnsToDrop)) {
                Schema::table('properties', function (Blueprint $table) use ($columnsToDrop) {
                    $table->dropColumn($columnsToDrop);
                });
            }

            // Now safely change column
            if (Schema::hasColumn('properties', 'status')) {
                Schema::table('properties', function (Blueprint $table) {
                    $table->boolean('status')->default(true)->change();
                });
            }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            //
        });
    }
};
