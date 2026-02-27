<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Dealer;
use App\Models\Tenant;

class DealerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant = Tenant::first();
        
        if (!$tenant) {
            $this->command->error('No tenants found! Run TenantSeeder first.');
            return;
        }

        // Use updateOrCreate so that re-running the seeder never drops existing
        // dealers (which would orphan vehicle.dealer_id foreign keys).
        $branches = [
            [
                'name' => 'Main Branch',
                'email' => 'main.branch@rbsvsms.local',
                'phone' => '077 765 0650',
                'address' => 'Boralasgamuwa, Colombo',
                'status' => 'active',
            ],
            [
                'name' => 'Kalubowila Branch',
                'email' => 'kalubowila.branch@rbsvsms.local',
                'phone' => '077 765 0650',
                'address' => 'Kalubovila, Colombo',
                'status' => 'active',
            ],
            [
                'name' => 'Kohuwala Branch',
                'email' => 'kohuwala.branch@rbsvsms.local',
                'phone' => '077 765 0650',
                'address' => 'Kohuwala, Colombo',
                'status' => 'active',
            ],
        ];

        // Use updateOrCreate so existing dealer IDs are preserved.
        // This prevents vehicle.dealer_id from becoming orphaned across re-seeds.
        foreach ($branches as $branch) {
            Dealer::updateOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $branch['name']],
                array_merge($branch, ['tenant_id' => $tenant->id])
            );
        }

        $this->command->info('Dealers seeded successfully!');
    }
}
