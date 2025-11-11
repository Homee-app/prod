<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            ['name' => 'Australia', 'country_code' => 'AU', 'phone_code' => '+61', 'status' => 1],
            ['name' => 'India', 'country_code' => 'IN', 'phone_code' => '+91', 'status' => 1],
            ['name' => 'Afghanistan', 'country_code' => 'AF', 'phone_code' => '+93', 'status' => 0],
            ['name' => 'Albania', 'country_code' => 'AL', 'phone_code' => '+355', 'status' => 0],
            ['name' => 'Algeria', 'country_code' => 'DZ', 'phone_code' => '+213', 'status' => 0],
            ['name' => 'Andorra', 'country_code' => 'AD', 'phone_code' => '+376', 'status' => 0],
            ['name' => 'Angola', 'country_code' => 'AO', 'phone_code' => '+244', 'status' => 0],
            ['name' => 'Argentina', 'country_code' => 'AR', 'phone_code' => '+54', 'status' => 0],
            ['name' => 'Armenia', 'country_code' => 'AM', 'phone_code' => '+374', 'status' => 0],
            ['name' => 'Austria', 'country_code' => 'AT', 'phone_code' => '+43', 'status' => 0],
            ['name' => 'Azerbaijan', 'country_code' => 'AZ', 'phone_code' => '+994', 'status' => 0],
            ['name' => 'Bahamas', 'country_code' => 'BS', 'phone_code' => '+1', 'status' => 0],
            ['name' => 'Bahrain', 'country_code' => 'BH', 'phone_code' => '+973', 'status' => 0],
            ['name' => 'Bangladesh', 'country_code' => 'BD', 'phone_code' => '+880', 'status' => 0],
            ['name' => 'Belarus', 'country_code' => 'BY', 'phone_code' => '+375', 'status' => 0],
            ['name' => 'Belgium', 'country_code' => 'BE', 'phone_code' => '+32', 'status' => 0],
            ['name' => 'Belize', 'country_code' => 'BZ', 'phone_code' => '+501', 'status' => 0],
            ['name' => 'Benin', 'country_code' => 'BJ', 'phone_code' => '+229', 'status' => 0],
            ['name' => 'Bhutan', 'country_code' => 'BT', 'phone_code' => '+975', 'status' => 0],
            ['name' => 'Bolivia', 'country_code' => 'BO', 'phone_code' => '+591', 'status' => 0],
            ['name' => 'Bosnia and Herzegovina', 'country_code' => 'BA', 'phone_code' => '+387', 'status' => 0],
            ['name' => 'Botswana', 'country_code' => 'BW', 'phone_code' => '+267', 'status' => 0],
            ['name' => 'Brazil', 'country_code' => 'BR', 'phone_code' => '+55', 'status' => 0],
            ['name' => 'Bulgaria', 'country_code' => 'BG', 'phone_code' => '+359', 'status' => 0],
            ['name' => 'Burkina Faso', 'country_code' => 'BF', 'phone_code' => '+226', 'status' => 0],
            ['name' => 'Burundi', 'country_code' => 'BI', 'phone_code' => '+257', 'status' => 0],
            ['name' => 'Cambodia', 'country_code' => 'KH', 'phone_code' => '+855', 'status' => 0],
            ['name' => 'Cameroon', 'country_code' => 'CM', 'phone_code' => '+237', 'status' => 0],
            ['name' => 'Canada', 'country_code' => 'CA', 'phone_code' => '+1', 'status' => 0],
            ['name' => 'Chile', 'country_code' => 'CL', 'phone_code' => '+56', 'status' => 0],
            ['name' => 'China', 'country_code' => 'CN', 'phone_code' => '+86', 'status' => 0],
            ['name' => 'Colombia', 'country_code' => 'CO', 'phone_code' => '+57', 'status' => 0],
            ['name' => 'Costa Rica', 'country_code' => 'CR', 'phone_code' => '+506', 'status' => 0],
            ['name' => 'Croatia', 'country_code' => 'HR', 'phone_code' => '+385', 'status' => 0],
            ['name' => 'Cuba', 'country_code' => 'CU', 'phone_code' => '+53', 'status' => 0],
            ['name' => 'Cyprus', 'country_code' => 'CY', 'phone_code' => '+357', 'status' => 0],
            ['name' => 'Czech Republic', 'country_code' => 'CZ', 'phone_code' => '+420', 'status' => 0],
            ['name' => 'Denmark', 'country_code' => 'DK', 'phone_code' => '+45', 'status' => 0],
            ['name' => 'Ecuador', 'country_code' => 'EC', 'phone_code' => '+593', 'status' => 0],
            ['name' => 'Egypt', 'country_code' => 'EG', 'phone_code' => '+20', 'status' => 0],
            ['name' => 'Estonia', 'country_code' => 'EE', 'phone_code' => '+372', 'status' => 0],
            ['name' => 'Finland', 'country_code' => 'FI', 'phone_code' => '+358', 'status' => 0],
            ['name' => 'France', 'country_code' => 'FR', 'phone_code' => '+33', 'status' => 0],
            ['name' => 'Germany', 'country_code' => 'DE', 'phone_code' => '+49', 'status' => 0],
            ['name' => 'Greece', 'country_code' => 'GR', 'phone_code' => '+30', 'status' => 0],
            ['name' => 'Hong Kong', 'country_code' => 'HK', 'phone_code' => '+852', 'status' => 0],
            ['name' => 'Hungary', 'country_code' => 'HU', 'phone_code' => '+36', 'status' => 0],
            ['name' => 'Indonesia', 'country_code' => 'ID', 'phone_code' => '+62', 'status' => 0],
            ['name' => 'Iran', 'country_code' => 'IR', 'phone_code' => '+98', 'status' => 0],
            ['name' => 'Iraq', 'country_code' => 'IQ', 'phone_code' => '+964', 'status' => 0],
            ['name' => 'Ireland', 'country_code' => 'IE', 'phone_code' => '+353', 'status' => 0],
            ['name' => 'Israel', 'country_code' => 'IL', 'phone_code' => '+972', 'status' => 0],
            ['name' => 'Italy', 'country_code' => 'IT', 'phone_code' => '+39', 'status' => 0],
            ['name' => 'Japan', 'country_code' => 'JP', 'phone_code' => '+81', 'status' => 0],
            ['name' => 'Mexico', 'country_code' => 'MX', 'phone_code' => '+52', 'status' => 0],
            ['name' => 'Netherlands', 'country_code' => 'NL', 'phone_code' => '+31', 'status' => 0],
            ['name' => 'New Zealand', 'country_code' => 'NZ', 'phone_code' => '+64', 'status' => 0],
            ['name' => 'Pakistan', 'country_code' => 'PK', 'phone_code' => '+92', 'status' => 0],
            ['name' => 'Russia', 'country_code' => 'RU', 'phone_code' => '+7', 'status' => 0],
            ['name' => 'South Africa', 'country_code' => 'ZA', 'phone_code' => '+27', 'status' => 0],
            ['name' => 'South Korea', 'country_code' => 'KR', 'phone_code' => '+82', 'status' => 0],
            ['name' => 'Spain', 'country_code' => 'ES', 'phone_code' => '+34', 'status' => 0],
            ['name' => 'United Kingdom', 'country_code' => 'GB', 'phone_code' => '+44', 'status' => 0],
            ['name' => 'United States', 'country_code' => 'US', 'phone_code' => '+1', 'status' => 0],
        ];

        DB::table(config('tables.countries'))->insert($countries);
    }
}
