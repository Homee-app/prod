<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'adminhomee@yopmail.com'],
            [
                'first_name' => 'admin',
                'last_name' => 'admin',
                'password' => Hash::make('admin1234'),
                'dob' => '2000-01-01',
                'role' => 1, // Admin
                'login_type' => 1,
                'device_type' => 1,
                'device_id' => null,
                'profile_photo' => null,
                'partner_profile_photo' => null,
                'forgot_token' => null,
                'status' => 1,
                'is_blocked' => false,
                'email_verified_at' => now(),
                'referral_code' => 'ADMIN123',
                'referred_by_user_id' => null,
                'profile_completed' => true,
                'is_identity_verified' => true,
                'identity_verified_at' => now(),
                'latitude' => -33.8688, // Example Lat for Sydney
                'longitude' => 151.2093, // Example Long for Sydney
            ]
        );

        // Create Varsha (if she doesn't exist)
        User::firstOrCreate(
            ['email' => 'varsha@yopmail.com'],
            [
                'first_name' => 'Varsha',
                'last_name' => 'Test',
                'password' => Hash::make('Test@123'),
                'dob' => '1998-05-20',
                'role' => 2, // Tenant
                'login_type' => 1,
                'device_type' => 1,
                'device_id' => null,
                'profile_photo' => null,
                'partner_profile_photo' => null,
                'forgot_token' => null,
                'status' => 1,
                'is_blocked' => false,
                'email_verified_at' => now(),
                'referral_code' => 'VARSHA123',
                'referred_by_user_id' => null,
                'profile_completed' => false,
                'is_identity_verified' => false,
                'identity_verified_at' => null,
                'latitude' => -34.0000, // Example Lat
                'longitude' => 151.0000, // Example Long
            ]
        );

        $existingTenantsCount = User::where('role', 2)->count();
        $tenantsToCreate = 40 - $existingTenantsCount;

        if ($tenantsToCreate > 0) {
            User::factory()->count($tenantsToCreate)->create();
            $this->command->info("Created {$tenantsToCreate} additional tenant users.");
        } else {
             $this->command->info("Already have at least 40 tenant users. No new tenants created by factory.");
        }

        $this->command->info('Users seeded successfully.');
    }
}
