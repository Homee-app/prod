<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;
use App\Models\TenantProfile;


class TenantProfilesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    
    public function run(): void
    {
        // Get all users who have the 'tenant' role
        $tenantUsers = User::where('role', 2)->get();
        $dataToInsert = [];

        foreach ($tenantUsers as $user) {
            // Check if a TenantProfile already exists for this user to avoid duplicates
            // Using updateOrCreate is generally better here if you want to ensure it exists
            TenantProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'is_teamup' => false, // Defaulting to false, adjust if you have a specific initial state
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }

        $this->command->info('Tenant Profiles seeded successfully.');
    }

}
