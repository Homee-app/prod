<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class OtpTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'code' => 'SIGNUP-VERIFICATION',
                'message' => "Hi, your verification code for accessing your " . env('APP_NAME') . " account is: ##otp##. This verification code will expire in 10 minutes"
            ],
        ];

        DB::table(config('tables.otp_templates'))->insert($data);
    }
}
