<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            UserSeeder::class,
            OtpTemplateSeeder::class,
            TestSeeder::class,
            ReportsSeeder::class,
            QuestionsSeeder::class,
            QuestionOptionsSeeder::class,
            QuestionAnswersUserSeeder::class,
            QuestionScreensSeeder::class,
            SuburbsSeeder::class,
            TenantProfilesSeeder::class,
            UserQuestionsAnswersSeeder::class,
        ]);
    }
}
