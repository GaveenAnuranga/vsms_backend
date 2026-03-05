<?php

use Tests\Helpers\CreatesTestData;
use Illuminate\Support\Facades\DB;

uses(CreatesTestData::class);

beforeEach(fn() => test()->seedBase());

function seedTransfer(int $tenantId, int $vehicleId, int $fromDealerId, int $toDealerId): int
{
    return DB::table('transfers')->insertGetId([
        'tenant_id'     => $tenantId,
        'vehicle_id'    => $vehicleId,
        'from_dealer_id'=> $fromDealerId,
        'to_dealer_id'  => $toDealerId,
        'transfer_date' => '2024-08-01',
        'transfer_price'=> 20000.00,
        'transport_cost'=> 500.00,
        'status'        => 'pending',
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);
}

// ─── GET /api/transfers ───────────────────────────────────────────────────────

it('returns empty transfers list when no records exist', function () {
    $this->getJson('/api/transfers')
         ->assertStatus(200)
         ->assertJsonStructure(['transfers'])
         ->assertJsonCount(0, 'transfers');
});

it('returns seeded transfers', function () {
    seedTransfer($this->tenantId, $this->vehicleId, $this->dealerId, $this->dealerId);

    $this->getJson('/api/transfers')
         ->assertStatus(200)
         ->assertJsonCount(1, 'transfers');
});

it('filters transfers by status', function () {
    seedTransfer($this->tenantId, $this->vehicleId, $this->dealerId, $this->dealerId);

    $this->getJson('/api/transfers?status=pending')
         ->assertStatus(200)
         ->assertJsonCount(1, 'transfers');

    $this->getJson('/api/transfers?status=completed')
         ->assertStatus(200)
         ->assertJsonCount(0, 'transfers');
});

// ─── GET /api/transfers/dealers ───────────────────────────────────────────────

it('returns dealers list for dropdown', function () {
    $this->getJson('/api/transfers/dealers')
         ->assertStatus(200)
         ->assertJsonStructure(['dealers'])
         ->assertJsonCount(1, 'dealers');
});

// ─── GET /api/transfers/{id} ──────────────────────────────────────────────────

it('returns a single transfer by id', function () {
    $id = seedTransfer($this->tenantId, $this->vehicleId, $this->dealerId, $this->dealerId);

    $this->getJson("/api/transfers/{$id}")
         ->assertStatus(200)
         ->assertJsonPath('transfer.id', $id)
         ->assertJsonStructure(['transfer' => ['id', 'vehicleId', 'status', 'transferDate']]);
});

it('returns 404 for non-existent transfer', function () {
    $this->getJson('/api/transfers/99999')
         ->assertStatus(404)
         ->assertJsonStructure(['error']);
});

// ─── POST /api/transfers ──────────────────────────────────────────────────────

it('creates a new transfer and returns 201', function () {
    $payload = [
        'vehicle_id'    => $this->vehicleId,
        'to_dealer_id'  => $this->dealerId,
        'transfer_date' => '2024-09-01',
        'status'        => 'pending',
    ];

    $this->postJson('/api/transfers', $payload)
         ->assertStatus(201)
         ->assertJsonStructure(['message', 'transfer'])
         ->assertJsonPath('transfer.status', 'pending');

    $this->assertDatabaseHas('transfers', [
        'vehicle_id'   => $this->vehicleId,
        'to_dealer_id' => $this->dealerId,
    ]);
});

it('returns 422 when required transfer fields are missing', function () {
    $this->postJson('/api/transfers', ['status' => 'pending'])
         ->assertStatus(422)
         ->assertJsonStructure(['error', 'messages']);
});

it('marks vehicle as Transferred when status is completed', function () {
    $payload = [
        'vehicle_id'    => $this->vehicleId,
        'to_dealer_id'  => $this->dealerId,
        'transfer_date' => '2024-09-01',
        'status'        => 'completed',
    ];

    $this->postJson('/api/transfers', $payload)
         ->assertStatus(201);

    $this->assertDatabaseHas('vehicles', [
        'id'     => $this->vehicleId,
        'status' => 'Transferred',
    ]);
});

// ─── PUT /api/transfers/{id} ──────────────────────────────────────────────────

it('updates an existing transfer', function () {
    $id = seedTransfer($this->tenantId, $this->vehicleId, $this->dealerId, $this->dealerId);

    $payload = [
        'vehicle_id'    => $this->vehicleId,
        'to_dealer_id'  => $this->dealerId,
        'transfer_date' => '2024-09-15',
        'status'        => 'completed',
    ];

    $this->putJson("/api/transfers/{$id}", $payload)
         ->assertStatus(200)
         ->assertJsonPath('transfer.status', 'completed');
});

it('returns 404 when updating a non-existent transfer', function () {
    $payload = [
        'vehicle_id'    => $this->vehicleId,
        'to_dealer_id'  => $this->dealerId,
        'transfer_date' => '2024-09-15',
    ];

    $this->putJson('/api/transfers/99999', $payload)
         ->assertStatus(404);
});

// ─── DELETE /api/transfers/{id} ───────────────────────────────────────────────

it('deletes a transfer and returns 200', function () {
    $id = seedTransfer($this->tenantId, $this->vehicleId, $this->dealerId, $this->dealerId);

    $this->deleteJson("/api/transfers/{$id}")
         ->assertStatus(200)
         ->assertJsonStructure(['message']);

    $this->assertDatabaseMissing('transfers', ['id' => $id]);
});

it('returns 404 when deleting a non-existent transfer', function () {
    $this->deleteJson('/api/transfers/99999')
         ->assertStatus(404);
});
