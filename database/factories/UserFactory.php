<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $email = fake()->unique()->safeEmail();

        $jaipurLat = 26.9124;
        $jaipurLng = 75.7873;

        // Define a small range around Jaipur
        // For example, +/- 0.15 degrees will give you a good spread within and slightly around Jaipur.
        // A degree of latitude is roughly 111 km. 0.15 degrees is about 16.65 km.
        // A degree of longitude varies, but 0.15 is a reasonable spread for a city.
        $latMin = $jaipurLat - 0.15;
        $latMax = $jaipurLat + 0.15;
        $lngMin = $jaipurLng - 0.15;
        $lngMax = $jaipurLng + 0.15;

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => static::$password ??= Hash::make('Test@123'), // Use a default password
            'dob' => fake()->dateTimeBetween('-40 years', '-18 years')->format('Y-m-d'),
            'login_type' => 1, // email
            'device_type' => fake()->randomElement([1, 2]), // Android or iOS
            'device_id' => Str::random(20),
            'profile_photo' => null,
            'partner_profile_photo' => null,
            'forgot_token' => null,
            'role' => 2, // Default to tenant
            'status' => 1, // Active
            'is_blocked' => false,
            'email_verified_at' => now(),
            'referral_code' => Str::upper(Str::random(8)),
            'referred_by_user_id' => null,
            'profile_completed' => fake()->boolean(80), // 80% chance of being completed
            'is_identity_verified' => fake()->boolean(50), // 50% chance of being verified
            'identity_verified_at' => fake()->boolean(50) ? now() : null,
            'otp_code' => null,
            'otp_expires_at' => null,
            'latitude' => fake()->latitude(min: $latMin, max: $latMax), // Adjusted for Jaipur
            'longitude' => fake()->longitude(min: $lngMin, max: $lngMax), // Adjusted for Jaipur
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 1, // Admin role
        ]);
    }
}