<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class ReportsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'title' => 'Harassment',
                'slug' => 'harassment',
                'description' => 'User is harassing or being abusive.',
            ],
            [
                'title' => 'Suicide or self-injury',
                'slug' => 'suicide-or-self-injury',
                'description' => 'Concern about self-harm or suicidal behavior.',
            ],
            [
                'title' => 'Pretending to be someone else',
                'slug' => 'impersonation',
                'description' => 'User is pretending to be another person or entity.',
            ],
            [
                'title' => 'Violence or dangerous organisations',
                'slug' => 'violence-or-dangerous-orgs',
                'description' => 'Promotion or threat of violence or association with dangerous groups.',
            ],
            [
                'title' => 'Nudity or sexual activity',
                'slug' => 'nudity-or-sexual-activity',
                'description' => 'Contains nudity, pornography, or sexual content.',
            ],
            [
                'title' => 'Scam or fraud',
                'slug' => 'scam-or-fraud',
                'description' => 'Suspected scam or deceptive behavior.',
            ],
            [
                'title' => 'Other',
                'slug' => 'other',
                'description' => 'Does not fit any specific category above.',
            ],
        ];

        DB::table(config('tables.reports'))->insert($data);
    }
}
