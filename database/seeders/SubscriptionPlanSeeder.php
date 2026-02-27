<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic Plan',
                'max_dealers' => 5,
                'max_vehicles' => 50,
                'max_transactions' => 100,
                'price' => 99.99,
            ],
            [
                'name' => 'Standard Plan',
                'max_dealers' => 15,
                'max_vehicles' => 150,
                'max_transactions' => 500,
                'price' => 249.99,
            ],
            [
                'name' => 'Premium Plan',
                'max_dealers' => 50,
                'max_vehicles' => 500,
                'max_transactions' => 2000,
                'price' => 499.99,
            ],
            [
                'name' => 'Enterprise Plan',
                'max_dealers' => 999,
                'max_vehicles' => 9999,
                'max_transactions' => 99999,
                'price' => 999.99,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::firstOrCreate(
                ['name' => $plan['name']],
                $plan
            );
        }

        $this->command->info('Subscription plans seeded successfully!');
    }
}
