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
        // Call seeders in order (respecting foreign key dependencies)
        $this->call([
            SubscriptionPlanSeeder::class,
            TenantSeeder::class,
            UserSeeder::class,
            DealerSeeder::class,
            VehicleSeeder::class,
        ]);
        
        $this->command->info('✅ All seeders completed successfully!');
    }
}
