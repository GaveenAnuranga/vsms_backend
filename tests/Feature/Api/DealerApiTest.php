<?php

use Tests\Helpers\CreatesTestData;
use Illuminate\Support\Facades\DB;

uses(CreatesTestData::class);

beforeEach(fn() => test()->seedBase());

// ─── GET /api/dealers ─────────────────────────────────────────────────────────

it('returns active dealers', function () {
    $this->getJson('/api/dealers')
         ->assertStatus(200)
         ->assertJsonPath('success', true)
         ->assertJsonCount(1, 'data');
});

it('excludes inactive dealers from the default list', function () {
    DB::table('dealers')->where('id', $this->dealerId)->update(['status' => 'inactive']);

    $this->getJson('/api/dealers')
         ->assertStatus(200)
         ->assertJsonCount(0, 'data');
});

// ─── GET /api/dealers/all ─────────────────────────────────────────────────────

it('returns all dealers including inactive from /dealers/all', function () {
    DB::table('dealers')->where('id', $this->dealerId)->update(['status' => 'inactive']);

    $this->getJson('/api/dealers/all')
         ->assertStatus(200)
         ->assertJsonCount(1, 'data');
});

it('returns multiple dealers', function () {
    DB::table('dealers')->insert([
        'tenant_id'  => $this->tenantId,
        'name'       => 'Branch B',
        'email'      => 'branchb@test.com',
        'phone'      => '0779999999',
        'address'    => 'Branch B Location',
        'status'     => 'inactive',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->getJson('/api/dealers/all')
         ->assertStatus(200)
         ->assertJsonCount(2, 'data');

    $this->getJson('/api/dealers')
         ->assertStatus(200)
         ->assertJsonCount(1, 'data');
});
