<?php

use Tests\Helpers\CreatesTestData;
use Illuminate\Support\Facades\DB;

uses(CreatesTestData::class);

beforeEach(fn() => test()->seedBase());

/** Creates a buyer and a sale record; returns the sale id. */
function seedSale(int $tenantId, int $vehicleId, int $pmId): int
{
    $buyerId = DB::table('buyers')->insertGetId([
        'tenant_id'  => $tenantId,
        'name'       => 'Jane Buyer',
        'nic_or_reg' => 'NIC001',
        'address'    => '1 Buyer Lane',
        'phone'      => '0771234567',
        'email'      => 'jane@test.com',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return DB::table('sales')->insertGetId([
        'tenant_id'        => $tenantId,
        'vehicle_id'       => $vehicleId,
        'buyer_id'         => $buyerId,
        'sale_date'        => '2024-07-15',
        'sale_price'       => 25000.00,
        'discount'         => 500.00,
        'final_amount'     => 24500.00,
        'payment_method_id'=> $pmId,
        'invoice_number'   => 'SALE-001',
        'commission'       => 300.00,
        'salesperson_name' => 'Bob Sales',
        'created_at'       => now(),
        'updated_at'       => now(),
    ]);
}

// ─── GET /api/sales ───────────────────────────────────────────────────────────

it('returns empty sales list when no records exist', function () {
    $this->getJson('/api/sales')
         ->assertStatus(200)
         ->assertJsonStructure(['sales'])
         ->assertJsonCount(0, 'sales');
});

it('returns all seeded sales', function () {
    seedSale($this->tenantId, $this->vehicleId, $this->paymentMethodId);

    $this->getJson('/api/sales')
         ->assertStatus(200)
         ->assertJsonCount(1, 'sales');
});

it('filters sales by vehicle_id', function () {
    seedSale($this->tenantId, $this->vehicleId, $this->paymentMethodId);

    $this->getJson("/api/sales?vehicle_id={$this->vehicleId}")
         ->assertStatus(200)
         ->assertJsonCount(1, 'sales');

    $this->getJson('/api/sales?vehicle_id=99999')
         ->assertStatus(200)
         ->assertJsonCount(0, 'sales');
});

it('filters sales by date range', function () {
    seedSale($this->tenantId, $this->vehicleId, $this->paymentMethodId);

    $this->getJson('/api/sales?start_date=2024-07-01&end_date=2024-07-31')
         ->assertStatus(200)
         ->assertJsonCount(1, 'sales');

    $this->getJson('/api/sales?start_date=2025-01-01&end_date=2025-12-31')
         ->assertStatus(200)
         ->assertJsonCount(0, 'sales');
});

// ─── GET /api/sales/{id} ─────────────────────────────────────────────────────

it('returns a single sale by id', function () {
    $id = seedSale($this->tenantId, $this->vehicleId, $this->paymentMethodId);

    $this->getJson("/api/sales/{$id}")
         ->assertStatus(200)
         ->assertJsonPath('sale.id', $id)
         ->assertJsonStructure(['sale' => ['vehicle', 'paymentMethod', 'seller']]);
});

it('returns 404 for non-existent sale', function () {
    $this->getJson('/api/sales/99999')
         ->assertStatus(404)
         ->assertJsonStructure(['error']);
});

// ─── GET /api/sales/statistics ────────────────────────────────────────────────

it('returns sales statistics', function () {
    seedSale($this->tenantId, $this->vehicleId, $this->paymentMethodId);

    $this->getJson('/api/sales/statistics')
         ->assertStatus(200)
         ->assertJsonStructure(['statistics' => [
             'totalSales', 'totalRevenue', 'totalDiscount', 'totalCommission',
         ]]);
});

it('returns zero statistics when no sales exist', function () {
    $this->getJson('/api/sales/statistics')
         ->assertStatus(200)
         ->assertJsonPath('statistics.totalSales', 0);
});
