<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin (Platform Admin)
        User::firstOrCreate(
            ['email' => 'sadmin@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Sadnim@1234'),
                'role' => 'platform_admin',
                'status' => 'active',
                'tenant_id' => null, // Platform admin doesn't belong to any tenant
            ]
        );

        // Create Company Admin (for future use)
        User::firstOrCreate(
            ['email' => 'companyadmin@example.com'],
            [
                'name' => 'Company Admin',
                'password' => Hash::make('CompanyAdmin@1234'),
                'role' => 'company_admin',
                'status' => 'active',
                'tenant_id' => null, // Will be assigned when tenants are created
            ]
        );

        // Create Dealer Admin (for future use)
        User::firstOrCreate(
            ['email' => 'dealeradmin@example.com'],
            [
                'name' => 'Dealer Admin',
                'password' => Hash::make('DealerAdmin@1234'),
                'role' => 'dealer_admin',
                'status' => 'active',
                'tenant_id' => null, // Will be assigned when dealers are created
            ]
        );

        $this->command->info('Users seeded successfully!');
        $this->command->info('Super Admin: sadmin@gmail.com / Sadnim@1234');
    }
}
