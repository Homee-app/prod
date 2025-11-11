<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => '4.99 boost profile',
                'description' => 'Test plan',
                'product_id' => 'prod_test_001',
                'price' => 4.99,
                'value' => 5,
                'value_type' => 'boost',
                'type' => 'tenant',
            ],
            [
                'name' => '2.99 boost profile',
                'description' => 'Test plan',
                'product_id' => 'prod_test_002',
                'price' => 2.99,
                'value' => 3,
                'value_type' => 'boost',
                'type' => 'tenant',
            ],
            [
                'name' => '0.99 boost profile',
                'description' => 'Test plan',
                'product_id' => 'prod_test_003',
                'price' => 0.99,
                'value' => 1,
                'value_type' => 'boost',
                'type' => 'tenant',
            ],
            [
                'name' => '4.99 golden key offer',
                'description' => 'Test plan',
                'product_id' => 'prod_test_004',
                'price' => 4.99,
                'value' => 5,
                'value_type' => 'key',
                'type' => 'tenant',
            ],
            [
                'name' => '2.99 golden key offer',
                'description' => 'Test plan',
                'product_id' => 'prod_test_005',
                'price' => 2.99,
                'value' => 3,
                'value_type' => 'key',
                'type' => 'tenant',
            ],
            [
                'name' => '.99 golden key offer',
                'description' => 'Test plan',
                'product_id' => 'prod_test_006',
                'price' => 0.99,
                'value' => 1,
                'value_type' => 'key',
                'type' => 'tenant',
            ],
            [
                'name' => '4.99 golden key offer',
                'description' => 'Test plan',
                'product_id' => 'prod_test_007',
                'price' => 4.99,
                'value' => 5,
                'value_type' => 'key',
                'type' => 'owner',
            ],
            [
                'name' => '2.99 golden key offer',
                'description' => 'Test plan',
                'product_id' => 'prod_test_008',
                'price' => 2.99,
                'value' => 3,
                'value_type' => 'key',
                'type' => 'owner',
            ],
            [
                'name' => '.99 golden key offer',
                'description' => 'Test plan',
                'product_id' => 'prod_test_009',
                'price' => 0.99,
                'value' => 1,
                'value_type' => 'key',
                'type' => 'owner',
            ],
            [
                'name' => '4.99 for subscription',
                'description' => 'Test plan',
                'product_id' => 'prod_test_010',
                'price' => 4.99,
                'value' => 1,
                'value_type' => 'month',
                'type' => 'owner',
            ],

            [
                'name' => '2.99 for subscription',
                'description' => 'Test plan',
                'product_id' => 'prod_test_011',
                'price' => 2.99,
                'value' => 2,
                'value_type' => 'week',
                'type' => 'owner',
            ],
            [
                'name' => '2.99 for subscription',
                'description' => 'Test plan',
                'product_id' => 'prod_test_012',
                'price' => 1.99,
                'value' => 1,
                'value_type' => 'week',
                'type' => 'owner',
            ],
            [
                'name' => '.99 for subscription',
                'description' => 'Test plan',
                'product_id' => 'prod_test_013',
                'price' => 0.00,
                'value' => 1,
                'value_type' => 'day',
                'type' => 'owner',
            ],
            [
                'name' => '4.99 for subscription',
                'description' => 'Test plan',
                'product_id' => 'prod_test_014',
                'price' => 4.99,
                'value' => 1,
                'value_type' => 'month',
                'type' => 'tenant',
            ],

            [
                'name' => '2.99 for subscription',
                'description' => 'Test plan',
                'product_id' => 'prod_test_015',
                'price' => 2.99,
                'value' => 2,
                'value_type' => 'week',
                'type' => 'tenant',
            ],
            [
                'name' => '2.99 for subscription',
                'description' => 'Test plan',
                'product_id' => 'prod_test_016',
                'price' => 1.99,
                'value' => 1,
                'value_type' => 'week',
                'type' => 'tenant',
            ],
            [
                'name' => '.99 for subscription',
                'description' => 'Test plan',
                'product_id' => 'prod_test_017',
                'price' => 0.00,
                'value' => 1,
                'value_type' => 'day',
                'type' => 'tenant',
            ],
        ];

        DB::table(config('tables.subscription_plans'))->insert($data);
    }
}
