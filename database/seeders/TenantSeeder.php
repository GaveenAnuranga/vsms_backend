<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\SubscriptionPlan;
use Carbon\Carbon;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a subscription plan
        $plan = SubscriptionPlan::first();
        
        if (!$plan) {
            $this->command->error('No subscription plans found! Run SubscriptionPlanSeeder first.');
            return;
        }

        $tenants = [
            [
                'name' => 'RBS Auto Trading',
                'email' => 'contact@rbsauto.com',
                'phone' => '+1234567890',
                'address' => '123 Main Street, City, State 12345',
                'subscription_plan_id' => $plan->id,
                'subscription_start' => Carbon::now(),
                'subscription_end' => Carbon::now()->addYear(),
                'status' => 'active',
            ],
            [
                'name' => 'Global Motors Ltd',
                'email' => 'info@globalmotors.com',
                'phone' => '+0987654321',
                'address' => '456 Oak Avenue, Town, State 67890',
                'subscription_plan_id' => $plan->id,
                'subscription_start' => Carbon::now(),
                'subscription_end' => Carbon::now()->addMonths(6),
                'status' => 'active',
            ],
        ];

        foreach ($tenants as $tenant) {
            Tenant::firstOrCreate(
                ['email' => $tenant['email']],
                $tenant
            );
        }

        $this->command->info('Tenants seeded successfully!');
    }
}
