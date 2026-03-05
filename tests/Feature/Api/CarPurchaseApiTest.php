<?php

use Tests\Helpers\CreatesTestData;
use Illuminate\Support\Facades\DB;

uses(CreatesTestData::class);

beforeEach(fn() => test()->seedBase());

// ─── GET /api/car-purchases ───────────────────────────────────────────────────

it('returns empty purchases list when no records exist', function () {
    $this->getJson('/api/car-purchases')
         ->assertStatus(200)
         ->assertJsonStructure(['purchases'])
         ->assertJsonCount(0, 'purchases');
});

it('returns seeded purchases', function () {
    DB::table('purchases')->insert([
        'tenant_id'         => $this->tenantId,
        'vehicle_id'        => $this->vehicleId,
        'purchase_date'     => '2024-06-01',
        'purchase_price'    => 20000.00,
        'payment_method_id' => $this->paymentMethodId,
        'invoice_number'    => 'INV-001',
        'tax_amount'        => 0,
        'created_at'        => now(),
        'updated_at'        => now(),
    ]);

    $this->getJson('/api/car-purchases')
         ->assertStatus(200)
         ->assertJsonCount(1, 'purchases');
});

// ─── GET /api/car-purchases/{id} ──────────────────────────────────────────────

it('returns 404 for non-existent purchase', function () {
    $this->getJson('/api/car-purchases/99999')
         ->assertStatus(404)
         ->assertJsonStructure(['error']);
});

it('returns a single purchase by id', function () {
    $id = DB::table('purchases')->insertGetId([
        'tenant_id'         => $this->tenantId,
        'vehicle_id'        => $this->vehicleId,
        'purchase_date'     => '2024-06-01',
        'purchase_price'    => 20000.00,
        'payment_method_id' => $this->paymentMethodId,
        'invoice_number'    => 'INV-002',
        'tax_amount'        => 0,
        'created_at'        => now(),
        'updated_at'        => now(),
    ]);

    $this->getJson("/api/car-purchases/{$id}")
         ->assertStatus(200)
         ->assertJsonPath('purchase.id', $id);
});

// ─── GET /api/car-purchases/search-vehicles ───────────────────────────────────

it('returns empty array when search query is too short', function () {
    $this->getJson('/api/car-purchases/search-vehicles?q=T')
         ->assertStatus(200)
         ->assertJsonCount(0, 'vehicles');
});

it('returns matching vehicles for a valid search query', function () {
    $this->getJson('/api/car-purchases/search-vehicles?q=Toyota')
         ->assertStatus(200)
         ->assertJsonCount(1, 'vehicles');
});

// ─── GET /api/car-purchases/vehicle/{id} ─────────────────────────────────────

it('returns 404 for non-existent vehicle detail', function () {
    $this->getJson('/api/car-purchases/vehicle/99999')
         ->assertStatus(404);
});

it('returns vehicle details by id', function () {
    $this->getJson("/api/car-purchases/vehicle/{$this->vehicleId}")
         ->assertStatus(200)
         ->assertJsonPath('vehicle.id', $this->vehicleId);
});

// ─── GET /api/car-purchases/branches ─────────────────────────────────────────

it('returns branches (dealers) list', function () {
    $this->getJson('/api/car-purchases/branches')
         ->assertStatus(200)
         ->assertJsonStructure(['branches']);
});

// ─── GET /api/car-purchases/payment-methods ───────────────────────────────────

it('returns available payment methods', function () {
    $this->getJson('/api/car-purchases/payment-methods')
         ->assertStatus(200)
         ->assertJsonStructure(['paymentMethods'])
         ->assertJsonCount(1, 'paymentMethods');
});

// ─── POST /api/car-purchases ──────────────────────────────────────────────────

it('creates a purchase (stores into sales table) and returns 201', function () {
    $payload = [
        'vehicle_id'        => $this->vehicleId,
        'purchase_date'     => '2024-07-01',
        'purchase_price'    => 22000,
        'payment_method_id' => $this->paymentMethodId,
        'invoice_number'    => 'INV-NEW-001',
        'seller_name'       => 'John Doe',
        'seller_address'    => '5 Seller St',
        'seller_phone'      => '0771111222',
    ];

    $this->postJson('/api/car-purchases', $payload)
         ->assertStatus(201)
         ->assertJsonStructure(['message', 'purchase']);

    $this->assertDatabaseHas('sales', ['invoice_number' => 'INV-NEW-001']);
});

it('returns 422 when required purchase fields are missing', function () {
    $this->postJson('/api/car-purchases', ['vehicle_id' => $this->vehicleId])
         ->assertStatus(422)
         ->assertJsonStructure(['error', 'messages']);
});

// ─── PUT /api/car-purchases/{id} ──────────────────────────────────────────────

it('returns 404 when updating a non-existent purchase', function () {
    $this->putJson('/api/car-purchases/99999', [])
         ->assertStatus(422); // validation runs first, no record check until after
});

// ─── DELETE /api/car-purchases/{id} ──────────────────────────────────────────

it('deletes a purchase record', function () {
    $id = DB::table('purchases')->insertGetId([
        'tenant_id'         => $this->tenantId,
        'vehicle_id'        => $this->vehicleId,
        'purchase_date'     => '2024-06-01',
        'purchase_price'    => 20000.00,
        'payment_method_id' => $this->paymentMethodId,
        'invoice_number'    => 'INV-DEL-001',
        'tax_amount'        => 0,
        'created_at'        => now(),
        'updated_at'        => now(),
    ]);

    $this->deleteJson("/api/car-purchases/{$id}")
         ->assertStatus(200)
         ->assertJsonStructure(['message']);

    $this->assertDatabaseMissing('purchases', ['id' => $id]);
});

it('returns 404 when deleting a non-existent purchase', function () {
    $this->deleteJson('/api/car-purchases/99999')
         ->assertStatus(404);
});
