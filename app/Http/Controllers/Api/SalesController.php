<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Seller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Controller for handling sales operations
 */
class SalesController extends Controller
{
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
            'sales' => $sales->map(function ($sale) {
                return $this->transformSale($sale);
            }),
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
            'sale' => $this->transformSale($sale, true),
        ]);
    }

    /**
     * Transform sale data for response
     */
    private function transformSale($sale, $includeBuyer = false): array
    {
        $data = [
            'id' => $sale->id,
            'vehicleId' => $sale->vehicle_id,
            'saleDate' => $sale->sale_date,
            'salePrice' => $sale->sale_price,
            'discount' => $sale->discount,
            'finalAmount' => $sale->final_amount,
            'paymentMethodId' => $sale->payment_method_id,
            'invoiceNumber' => $sale->invoice_number,
            'commission' => $sale->commission,
            'salespersonName' => $sale->salesperson_name,
            'createdAt' => $sale->created_at,
            'updatedAt' => $sale->updated_at,
        ];

        // Add vehicle info
        if ($sale->vehicle) {
            $data['vehicle'] = [
                'id' => $sale->vehicle->id,
                'stockNumber' => $sale->vehicle->stock_number,
                'make' => $sale->vehicle->make,
                'model' => $sale->vehicle->model,
                'subModel' => $sale->vehicle->sub_model,
                'year' => $sale->vehicle->year,
                'color' => $sale->vehicle->color,
                'fuelType' => $sale->vehicle->fuel_type,
                'transmissionType' => $sale->vehicle->transmission_type,
                'mileage' => $sale->vehicle->mileage,
                'price' => $sale->vehicle->price,
                'status' => $sale->vehicle->status,
            ];
        }

        // Add payment method info
        if ($sale->paymentMethod) {
            $data['paymentMethod'] = [
                'id' => $sale->paymentMethod->id,
                'name' => $sale->paymentMethod->name,
            ];
        }

        // Add buyer info if requested
        if ($includeBuyer && $sale->buyer) {
            $data['buyer'] = [
                'id' => $sale->buyer->id,
                'name' => $sale->buyer->name,
                'nicOrReg' => $sale->buyer->nic_or_reg,
                'address' => $sale->buyer->address,
                'phone' => $sale->buyer->phone,
                'email' => $sale->buyer->email,
            ];
        }

        // Try to get seller info from sellers table by matching salesperson_name
        $seller = Seller::where('name', $sale->salesperson_name)->first();
        if ($seller) {
            $data['seller'] = [
                'id' => $seller->id,
                'name' => $seller->name,
                'nicOrReg' => $seller->nic_or_reg,
                'address' => $seller->address,
                'phone' => $seller->phone,
                'email' => $seller->email,
                'sellerType' => $seller->seller_type,
            ];
        } else {
            // Fallback to salesperson_name as seller info
            $data['seller'] = [
                'name' => $sale->salesperson_name,
                'phone' => null,
            ];
        }

        return $data;
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
