<?php

namespace Tests\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Shared trait that seeds the minimum relational data every API test needs.
 * Call $this->seedBase() inside beforeEach().
 */
trait CreatesTestData
{
    protected int $planId;
    protected int $tenantId;
    protected int $dealerId;
    protected int $paymentMethodId;
    protected int $vehicleId;

    protected function seedBase(): void
    {
        $this->planId = DB::table('subscription_plans')->insertGetId([
            'name'             => 'Basic',
            'max_dealers'      => 10,
            'max_vehicles'     => 100,
            'max_transactions' => 500,
            'price'            => 99.99,
        ]);

        $this->tenantId = DB::table('tenants')->insertGetId([
            'name'                 => 'Test Auto',
            'email'                => 'tenant@test.com',
            'phone'                => '0771234567',
            'address'              => '1 Main St',
            'subscription_plan_id' => $this->planId,
            'subscription_start'   => '2024-01-01',
            'subscription_end'     => '2027-12-31',
            'status'               => 'active',
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        $this->dealerId = DB::table('dealers')->insertGetId([
            'tenant_id'  => $this->tenantId,
            'name'       => 'Head Branch',
            'email'      => 'branch@test.com',
            'phone'      => '0771234567',
            'address'    => 'Head Office',
            'status'     => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->paymentMethodId = DB::table('payment_methods')->insertGetId(['name' => 'Cash']);

        DB::table('users')->insertGetId([
            'name'       => 'Admin User',
            'email'      => 'admin@test.com',
            'password'   => Hash::make('password123'),
            'tenant_id'  => $this->tenantId,
            'role'       => 'company_admin',
            'status'     => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->vehicleId = DB::table('vehicles')->insertGetId([
            'tenant_id'         => $this->tenantId,
            'vehicle_code'      => 'VH20241001',
            'stock_number'      => '10001',
            'make'              => 'Toyota',
            'model'             => 'Camry',
            'vehicle_type'      => 'Sedan',
            'year'              => 2020,
            'color'             => 'White',
            'country_of_origin' => 'Japan',
            'fuel_type'         => 'Gasoline',
            'mileage'           => 50000,
            'transmission_type' => 'Automatic',
            'registration_type' => 'Unregistered',
            'price'             => 25000.00,
            'dealer_id'         => $this->dealerId,
            'status'            => 'Available',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }
}
