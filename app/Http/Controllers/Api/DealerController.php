<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dealer;
use Illuminate\Http\Request;

class DealerController extends Controller
{
    /**
     * Get all active dealers
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Get dealers with active status
            $dealers = Dealer::active()
                ->select('id', 'name', 'email', 'phone', 'tenant_id', 'status')
                ->orderBy('name', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $dealers
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dealers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all dealers (including inactive)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function all(Request $request)
    {
        try {
            $dealers = Dealer::select('id', 'name', 'email', 'phone', 'tenant_id', 'status')
                ->orderBy('name', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $dealers
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dealers',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
