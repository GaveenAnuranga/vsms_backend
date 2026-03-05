<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Services\SaleTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalesController extends Controller
{
    public function __construct(private SaleTransformer $transformer) {}
    /**
     * Get all sales with optional filters
     */
    public function index(Request $request)
    {
        $query = Sale::with(['vehicle', 'paymentMethod']);

        // Filter by tenant if authenticated
        if (Auth::check() && Auth::user()->tenant_id) {
            $query->where('tenant_id', Auth::user()->tenant_id);
        }

        // Filter by vehicle
        if ($request->has('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('sale_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('sale_date', '<=', $request->end_date);
        }

        // Filter by payment method
        if ($request->has('payment_method_id')) {
            $query->where('payment_method_id', $request->payment_method_id);
        }

        // Search by stock number, make, or model
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('vehicle', function ($q) use ($search) {
                $q->where('stock_number', 'like', "%{$search}%")
                  ->orWhere('make', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%");
            });
        }

        $sales = $query->orderBy('sale_date', 'desc')->get();

        return response()->json([
            'sales' => $sales->map(fn($sale) => $this->transformer->transform($sale)),
        ]);
    }

    /**
     * Get single sale by ID
     */
    public function show($id)
    {
        $sale = Sale::with(['vehicle', 'paymentMethod', 'buyer'])->find($id);

        if (!$sale) {
            return response()->json(['error' => 'Sale not found'], 404);
        }

        return response()->json([
            'sale' => $this->transformer->transform($sale, true),
        ]);
    }

    /**
     * Get sales statistics
     */
    public function statistics(Request $request)
    {
        $query = Sale::query();

        // Filter by tenant if authenticated
        if (Auth::check() && Auth::user()->tenant_id) {
            $query->where('tenant_id', Auth::user()->tenant_id);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('sale_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('sale_date', '<=', $request->end_date);
        }

        $totalSales = $query->count();
        $totalRevenue = $query->sum('final_amount');
        $totalDiscount = $query->sum('discount');
        $totalCommission = $query->sum('commission');

        return response()->json([
            'statistics' => [
                'totalSales' => $totalSales,
                'totalRevenue' => $totalRevenue,
                'totalDiscount' => $totalDiscount,
                'totalCommission' => $totalCommission,
            ],
        ]);
    }
}
