<?php

use Tests\Helpers\CreatesTestData;
use Illuminate\Support\Facades\DB;

uses(CreatesTestData::class);

beforeEach(fn() => test()->seedBase());

// ─── GET /api/vehicles ────────────────────────────────────────────────────────

it('lists all vehicles', function () {
    $this->getJson('/api/vehicles')
         ->assertStatus(200)
         ->assertJsonStructure(['vehicles'])
         ->assertJsonCount(1, 'vehicles');
});

it('filters vehicles by status query param', function () {
    $this->getJson('/api/vehicles?status=Available')
         ->assertStatus(200)
         ->assertJsonCount(1, 'vehicles');

    $this->getJson('/api/vehicles?status=Sold')
         ->assertStatus(200)
         ->assertJsonCount(0, 'vehicles');
});

// ─── GET /api/vehicles/{id} ───────────────────────────────────────────────────

it('returns a single vehicle by id', function () {
    $this->getJson("/api/vehicles/{$this->vehicleId}")
         ->assertStatus(200)
         ->assertJsonPath('vehicle.id', $this->vehicleId)
         ->assertJsonPath('vehicle.make', 'Toyota');
});

it('returns 404 for a non-existent vehicle', function () {
    $this->getJson('/api/vehicles/99999')
         ->assertStatus(404)
         ->assertJsonStructure(['error']);
});

// ─── GET /api/vehicles/public/{id} ───────────────────────────────────────────

it('returns public vehicle data', function () {
    $this->getJson("/api/vehicles/public/{$this->vehicleId}")
         ->assertStatus(200)
         ->assertJsonPath('vehicle.id', $this->vehicleId);
});

it('returns 404 for non-existent public vehicle', function () {
    $this->getJson('/api/vehicles/public/99999')
         ->assertStatus(404);
});

// ─── GET /api/vehicles/landing ───────────────────────────────────────────────

it('returns landing page vehicles', function () {
    $this->getJson('/api/vehicles/landing')
         ->assertStatus(200)
         ->assertJsonStructure(['vehicles', 'total', 'page', 'limit']);
});

it('returns empty landing vehicles when none are available', function () {
    DB::table('vehicles')->where('id', $this->vehicleId)->update(['status' => 'Sold']);

    $this->getJson('/api/vehicles/landing')
         ->assertStatus(200)
         ->assertJsonCount(0, 'vehicles');
});

// ─── POST /api/vehicles ───────────────────────────────────────────────────────

it('creates a new vehicle and returns 201', function () {
    $payload = [
        'stockNumber'        => '20002',
        'vehicleType'        => 'SUV',
        'make'               => 'Honda',
        'model'              => 'CR-V',
        'year'               => 2022,
        'color'              => 'Black',
        'countryOfOrigin'    => 'Japan',
        'fuelType'           => 'Gasoline',
        'mileage'            => 15000,
        'transmissionType'   => 'Automatic',
        'registrationType'   => 'Unregistered',
        'price'              => 35000,
        'dealerId'           => $this->dealerId,
        'status'             => 'Available',
        'unregisteredDetails' => [
            'chassisNumber'   => 'CH000111',
            'engineNumber'    => 'EN000222',
            'importerName'    => 'Test Importer',
            'importerContact' => '0779876543',
        ],
    ];

    $this->postJson('/api/vehicles', $payload)
         ->assertStatus(201)
         ->assertJsonPath('vehicle.make', 'Honda')
         ->assertJsonPath('vehicle.model', 'CR-V');

    $this->assertDatabaseHas('vehicles', ['stock_number' => '20002', 'make' => 'Honda']);
});

it('rejects vehicle creation with missing required fields', function () {
    $this->postJson('/api/vehicles', ['make' => 'Honda'])
         ->assertStatus(422)
         ->assertJsonStructure(['error', 'messages']);
});

it('rejects duplicate stock number', function () {
    $payload = [
        'stockNumber'        => '10001', // already used in seedBase
        'vehicleType'        => 'Car',
        'make'               => 'Nissan',
        'model'              => 'Sunny',
        'year'               => 2021,
        'color'              => 'Blue',
        'countryOfOrigin'    => 'Japan',
        'fuelType'           => 'Gasoline',
        'mileage'            => 10000,
        'transmissionType'   => 'Manual',
        'registrationType'   => 'Unregistered',
        'price'              => 18000,
        'dealerId'           => $this->dealerId,
        'status'             => 'Available',
        'unregisteredDetails' => ['chassisNumber' => 'CH999', 'engineNumber' => 'EN999', 'importerName' => 'X', 'importerContact' => '07700'],
    ];

    $this->postJson('/api/vehicles', $payload)
         ->assertStatus(422);
});

// ─── PUT /api/vehicles/{id} ───────────────────────────────────────────────────

it('updates an existing vehicle', function () {
    $payload = [
        'stockNumber'        => '10001',
        'vehicleType'        => 'Sedan',
        'make'               => 'Toyota',
        'model'              => 'Camry',
        'year'               => 2021,
        'color'              => 'Silver',
        'countryOfOrigin'    => 'Japan',
        'fuelType'           => 'Hybrid',
        'mileage'            => 60000,
        'transmissionType'   => 'Automatic',
        'registrationType'   => 'Unregistered',
        'price'              => 27000,
        'dealerId'           => $this->dealerId,
        'status'             => 'Available',
        'unregisteredDetails' => ['chassisNumber' => 'CH001', 'engineNumber' => 'EN001', 'importerName' => 'Imp', 'importerContact' => '07711'],
    ];

    $this->putJson("/api/vehicles/{$this->vehicleId}", $payload)
         ->assertStatus(200)
         ->assertJsonPath('vehicle.color', 'Silver')
         ->assertJsonPath('vehicle.year', 2021);
});

it('returns 404 when updating a non-existent vehicle', function () {
    $this->putJson('/api/vehicles/99999', ['make' => 'Test'])
         ->assertStatus(404);
});

// ─── DELETE /api/vehicles/{id} ────────────────────────────────────────────────

it('deletes a vehicle and returns 200', function () {
    $this->deleteJson("/api/vehicles/{$this->vehicleId}")
         ->assertStatus(200)
         ->assertJsonStructure(['message']);

    $this->assertDatabaseMissing('vehicles', ['id' => $this->vehicleId]);
});

it('returns 404 when deleting a non-existent vehicle', function () {
    $this->deleteJson('/api/vehicles/99999')
         ->assertStatus(404);
});
