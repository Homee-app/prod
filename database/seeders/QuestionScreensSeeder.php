<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class QuestionScreensSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $questions = [
            'Your identity',
            'Your employment',
            'Your lifestyle and habits',
            'Your Lifestyle',
            'Your ethnicity',
            'Find Your Perfect Home',
            'Show off the person behind the profile',
            'Tell Us More About You',
            'Your Interests',
            'Your religious beliefs',
            "Let's get to know you - My Vibe 🤩",
            "Let's get to know you - Dealbreakers 🎯",
            "Let's get to know you - Living Habits 🧼",
            "Let's get to know you - House Rules",
            "🏡 Describe Your Place",
            "🪴 About The Property",
            "👨‍👧 About The Homees",
            "🛋️ About The Room",
            "🪑 Room Features",
            "💰 Rent, Bond and Bills",
            "⏰ Room Availability",
            "📸 Property and Room Images",
            "🙋🏻‍♀️ Homee Preference",
            "🤩 Tell Us About You and Your Property"
        ];

        $data = [];

        // Add two special entries with same title but different slugs
        $data[] = [
            'title' => '💁‍♀️ Let’s introduce yourself',
            'slug' => 'lets-introduce-yourself-first',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        $data[] = [
            'title' => '💁‍♀️ Let’s introduce yourself',
            'slug' => 'lets-introduce-yourself-second',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Skip the special case from the main loop to avoid duplicates
        foreach ($questions as $title) {
            $data[] = [
                'title' => $title,
                'slug' => Str::slug($title),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table(config('tables.question_screens'))->insert($data);
    }
}
