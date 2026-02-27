<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DealerController;
use App\Http\Controllers\Api\CarPurchaseController;
use App\Http\Controllers\Api\SalesController;
use App\Http\Controllers\Api\TransferController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Test endpoint
Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working!',
        'timestamp' => now()->toDateTimeString(),
        'version' => '1.0.0'
    ]);
});

// Authentication endpoints
Route::prefix('account')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

// Vehicle API endpoints
Route::prefix('vehicles')->group(function () {
    // Get all vehicles
    Route::get('/', [VehicleController::class, 'index']);
    
    // Get single vehicle for public display (limited data)
    Route::get('/public/{id}', [VehicleController::class, 'showPublic']);
    
    // Get single vehicle (full data for admin)
    Route::get('/{id}', [VehicleController::class, 'show']);
    
    // Create new vehicle
    Route::post('/', [VehicleController::class, 'store']);
    
    // Update vehicle
    Route::put('/{id}', [VehicleController::class, 'update']);
    
    // Delete vehicle
    Route::delete('/{id}', [VehicleController::class, 'destroy']);
    
    // Upload vehicle images
    Route::post('/{id}/images', [VehicleController::class, 'uploadImages']);
});

// Dealer API endpoints
Route::prefix('dealers')->group(function () {
    // Get all active dealers
    Route::get('/', [DealerController::class, 'index']);
    
    // Get all dealers (including inactive)
    Route::get('/all', [DealerController::class, 'all']);
});

// Car Purchase API endpoints
Route::prefix('car-purchases')->group(function () {
    // Get all purchases
    Route::get('/', [CarPurchaseController::class, 'index']);
    
    // Search vehicles by code (for autocomplete)
    Route::get('/search-vehicles', [CarPurchaseController::class, 'searchVehicles']);

    // Get vehicle details by ID
    Route::get('/vehicle/{id}', [CarPurchaseController::class, 'getVehicleDetails']);

    // Get branches (dealers)
    Route::get('/branches', [CarPurchaseController::class, 'getBranches']);

    // Get payment methods
    Route::get('/payment-methods', [CarPurchaseController::class, 'getPaymentMethods']);

    // Get single purchase
    Route::get('/{id}', [CarPurchaseController::class, 'show']);

    // Create new purchase
    Route::post('/', [CarPurchaseController::class, 'store']);

    // Update purchase
    Route::put('/{id}', [CarPurchaseController::class, 'update']);

    // Delete purchase
    Route::delete('/{id}', [CarPurchaseController::class, 'destroy']);
});

// Sales API endpoints
Route::prefix('sales')->group(function () {
    // Get all sales
    Route::get('/', [SalesController::class, 'index']);
    
    // Get sales statistics
    Route::get('/statistics', [SalesController::class, 'statistics']);

    // Get single sale
    Route::get('/{id}', [SalesController::class, 'show']);
});

// Transfer API endpoints
Route::prefix('transfers')->group(function () {
    // Get all transfers
    Route::get('/', [TransferController::class, 'index']);
    
    // Get dealers for dropdown
    Route::get('/dealers', [TransferController::class, 'getDealers']);

    // Get single transfer
    Route::get('/{id}', [TransferController::class, 'show']);

    // Create new transfer
    Route::post('/', [TransferController::class, 'store']);

    // Update transfer
    Route::put('/{id}', [TransferController::class, 'update']);

    // Delete transfer
    Route::delete('/{id}', [TransferController::class, 'destroy']);
});

// Get authenticated user info
Route::get('/user', function (Request $request) {
    if (Auth::check()) {
        return Auth::user();
    }
    return response()->json(['message' => 'Unauthenticated'], 401);
});
