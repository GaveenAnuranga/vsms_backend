<?php

use Tests\Helpers\CreatesTestData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

uses(CreatesTestData::class);

beforeEach(fn() => test()->seedBase());

// ─── Basic Connectivity ──────────────────────────────────────────────────────

it('returns 200 for the test endpoint', function () {
    $res = $this->getJson('/api/test');

    $res->assertStatus(200)
        ->assertJsonStructure(['message', 'timestamp', 'version']);
});

// ─── Register (disabled) ─────────────────────────────────────────────────────

it('blocks registration because it is admin-only', function () {
    $res = $this->postJson('/api/account/register', [
        'name'     => 'New User',
        'email'    => 'new@test.com',
        'password' => 'secret123',
    ]);

    $res->assertStatus(403)
        ->assertJsonPath('success', false);
});

// ─── Login ───────────────────────────────────────────────────────────────────

it('returns 422 when login fields are missing', function () {
    $res = $this->postJson('/api/account/login', []);

    $res->assertStatus(422)
        ->assertJsonPath('success', false);
});

it('returns 401 for invalid credentials', function () {
    $res = $this->postJson('/api/account/login', [
        'email'    => 'admin@test.com',
        'password' => 'wrongpassword',
    ]);

    $res->assertStatus(401)
        ->assertJsonPath('success', false);
});

it('returns 403 for inactive user login', function () {
    DB::table('users')->where('email', 'admin@test.com')->update(['status' => 'inactive']);

    $res = $this->postJson('/api/account/login', [
        'email'    => 'admin@test.com',
        'password' => 'password123',
    ]);

    $res->assertStatus(403)
        ->assertJsonPath('success', false);
});

it('logs in successfully and returns a service token', function () {
    $res = $this->postJson('/api/account/login', [
        'email'    => 'admin@test.com',
        'password' => 'password123',
    ]);

    $res->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['serviceToken', 'user' => ['id', 'name', 'email', 'role']])
        ->assertJsonPath('user.role', 'company_admin');
});

// ─── Logout ──────────────────────────────────────────────────────────────────

it('logs out successfully', function () {
    $this->postJson('/api/account/logout')
         ->assertStatus(200)
         ->assertJsonPath('success', true);
});

// ─── Current user ─────────────────────────────────────────────────────────────

it('returns user when authenticated', function () {
    $user = \App\Models\User::where('email', 'admin@test.com')->first();

    $this->actingAs($user)
         ->getJson('/api/user')
         ->assertStatus(200)
         ->assertJsonPath('email', 'admin@test.com');
});

it('returns 401 when not authenticated on /api/user', function () {
    $this->getJson('/api/user')
         ->assertStatus(401);
});
