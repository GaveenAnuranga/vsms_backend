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
            PaymentMethodSeeder::class,
            TenantSeeder::class,
            UserSeeder::class,
            DealerSeeder::class,
        ]);
        
        $this->command->info('✅ All seeders completed successfully!');
    }
}
