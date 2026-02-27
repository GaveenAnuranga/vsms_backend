<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CarPurchase;
use App\Models\Seller;
use App\Models\Vehicle;
use App\Models\Dealer;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

/**
 * Controller for handling car purchase operations
 */
class CarPurchaseController extends Controller
{
    /**
     * Get all car purchases with optional filters
     */
    public function index(Request $request)
    {
        $query = CarPurchase::with(['vehicle', 'sellers', 'paymentMethod']);

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
            $query->where('purchase_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('purchase_date', '<=', $request->end_date);
        }

        $purchases = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'purchases' => $purchases->map(function ($purchase) {
                return $this->transformPurchase($purchase);
            }),
        ]);
    }

    /**
     * Get single car purchase by ID
     */
    public function show($id)
    {
        $purchase = CarPurchase::with(['vehicle', 'sellers', 'paymentMethod'])->find($id);

        if (!$purchase) {
            return response()->json(['error' => 'Purchase not found'], 404);
        }

        return response()->json([
            'purchase' => $this->transformPurchase($purchase),
        ]);
    }

    /**
     * Search vehicles by vehicle code (for autocomplete)
     */
    public function searchVehicles(Request $request)
    {
        $searchTerm = $request->query('q', '');

        if (strlen($searchTerm) < 2) {
            return response()->json(['vehicles' => []]);
        }

        $vehicles = Vehicle::leftJoin('dealers', 'vehicles.dealer_id', '=', 'dealers.id')
            ->where(function ($q) use ($searchTerm) {
                $q->where('vehicles.stock_number', 'like', "%{$searchTerm}%")
                  ->orWhere('vehicles.make', 'like', "%{$searchTerm}%")
                  ->orWhere('vehicles.model', 'like', "%{$searchTerm}%");
            })
            ->select(
                'vehicles.id',
                'vehicles.stock_number',
                'vehicles.make',
                'vehicles.model',
                'vehicles.year',
                'vehicles.price',
                'vehicles.status',
                'vehicles.dealer_id',
                'dealers.name as dealer_name'
            )
            ->limit(20)
            ->get();

        return response()->json(['vehicles' => $vehicles]);
    }

    /**
     * Get vehicle details by ID
     */
    public function getVehicleDetails($id)
    {
        $vehicle = Vehicle::find($id);

        if (!$vehicle) {
            return response()->json(['error' => 'Vehicle not found'], 404);
        }

        return response()->json([
            'vehicle' => [
                'id' => $vehicle->id,
                'stockNumber' => $vehicle->stock_number,
                'make' => $vehicle->make,
                'model' => $vehicle->model,
                'year' => $vehicle->year,
                'price' => $vehicle->price,
                'color' => $vehicle->color,
                'status' => $vehicle->status,
            ],
        ]);
    }

    /**
     * Get all branches (dealers)
     */
    public function getBranches()
    {
        $dealers = Dealer::where('status', 'active')
            ->select('id', 'name', 'address')
            ->orderBy('name')
            ->get();

return response()->json(['branches' => $dealers]);
    }

    /**
     * Get all payment methods
     */
    public function getPaymentMethods()
    {
        $paymentMethods = PaymentMethod::orderBy('name')->get();

        return response()->json(['paymentMethods' => $paymentMethods]);
    }

    /**
     * Create new car purchase
     */
    public function store(Request $request)
    {
        $validator = $this->validatePurchaseData($request);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $tenantId = null;
            if (Auth::check()) {
                $tenantId = Auth::user()->tenant_id;
            } else {
                $tenantId = \App\Models\Tenant::first()->id ?? 1;
            }
            // Handle document upload (resilient: don't fail the whole request if storage fails)
            $documentPath = null;
            if ($request->hasFile('document')) {
                try {
                    $file = $request->file('document');
                    $path = $file->store('payment_docs', 'public');
                    if ($path) {
                        $documentPath = '/storage/' . $path;
                    }
                } catch (\Exception $e) {
                    Log::warning('Document upload failed: ' . $e->getMessage());
                    $documentPath = null;
                }
            }

            // Prepare buyer data and ensure buyer exists
            $buyerName = $request->input('buyer_name') ?? $request->input('seller_name') ?? 'Walk-in Buyer';
            $buyerNic = $request->input('buyer_nic') ?? $request->input('seller_nic') ?? '';
            $buyerAddress = $request->input('buyer_address') ?? $request->input('seller_address') ?? '';
            $buyerPhone = $request->input('buyer_phone') ?? $request->input('seller_phone') ?? '';
            $buyerEmail = $request->input('buyer_email') ?? '';

            $buyerId = DB::table('buyers')->insertGetId([
                'tenant_id' => $tenantId,
                'name' => $buyerName,
                'nic_or_reg' => $buyerNic,
                'address' => $buyerAddress,
                'phone' => $buyerPhone,
                'email' => $buyerEmail,
            ]);

            // Persist sale into existing `sales` table (project uses `sales` + `sellers` tables)
            $saleData = [
                'tenant_id' => $tenantId,
                'vehicle_id' => $request->vehicle_id,
                'buyer_id' => $buyerId,
                'sale_date' => $request->purchase_date,
                'sale_price' => $request->purchase_price,
                'discount' => 0,
                'final_amount' => $request->purchase_price,
                'payment_method_id' => $request->payment_method_id,
                'invoice_number' => $request->invoice_number,
                'commission' => 0,
                'salesperson_name' => $request->seller_name,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $saleId = DB::table('sales')->insertGetId($saleData);

            // Create or update seller record in `sellers` table
            $seller = Seller::updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'phone' => $request->seller_phone,
                ],
                [
                    'name' => $request->seller_name,
                    'nic_or_reg' => $request->seller_nic ?? null,
                    'address' => $request->seller_address,
                    'email' => $request->seller_email ?? null,
                    'seller_type' => $request->seller_type ?? 'individual',
                ]
            );

            // Update vehicle status to "Purchased" if applicable
            $vehicle = Vehicle::find($request->vehicle_id);
            if ($vehicle && $vehicle->status === 'Available') {
                $vehicle->update(['status' => 'Sold']);
            }

            DB::commit();

            // Build response payload from sale + related data
            $sale = DB::table('sales')->where('id', $saleId)->first();
            $paymentMethod = PaymentMethod::find($sale->payment_method_id);

            $response = [
                'id' => $sale->id,
                'vehicleId' => $sale->vehicle_id,
                'purchaseDate' => $sale->sale_date,
                'purchasePrice' => $sale->sale_price,
                'paymentMethodId' => $sale->payment_method_id,
                'invoiceNumber' => $sale->invoice_number,
                'taxAmount' => 0,
                'branch' => $request->branch ?? null,
                'documentPath' => $documentPath,
                'taxDetails' => $request->tax_details ?? null,
                'createdAt' => $sale->created_at,
                'updatedAt' => $sale->updated_at,
                'paymentMethod' => $paymentMethod ? ['id' => $paymentMethod->id, 'name' => $paymentMethod->name] : null,
                'seller' => [
                    'id' => $seller->id,
                    'name' => $seller->name,
                    'phone' => $seller->phone,
                ],
            ];

            return response()->json([
                'message' => 'Car purchase created successfully',
                'purchase' => $response,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Car purchase creation failed: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to create purchase',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update existing car purchase
     */
    public function update(Request $request, $id)
    {
        $purchase = CarPurchase::find($id);

        if (!$purchase) {
            return response()->json(['error' => 'Purchase not found'], 404);
        }

        $validator = $this->validatePurchaseData($request, true);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Handle document upload if new file provided
            $documentPath = $purchase->document_path;
            if ($request->hasFile('document')) {
                // Delete old file if exists
                if ($purchase->document_path) {
                    $oldPath = str_replace('/storage/', '', $purchase->document_path);
                    Storage::disk('public')->delete($oldPath);
                }
                
                $file = $request->file('document');
                $path = $file->store('payment_docs', 'public');
                $documentPath = '/storage/' . $path;
            }

            // Update purchase record
            $purchase->update([
                'vehicle_id' => $request->vehicle_id,
                'purchase_date' => $request->purchase_date,
                'purchase_price' => $request->purchase_price,
                'payment_method_id' => $request->payment_method_id,
                'invoice_number' => $request->invoice_number,
                'tax_amount' => $request->tax_amount ?? 0,
                'branch' => $request->branch,
                'document_path' => $documentPath,
                'tax_details' => $request->tax_details,
            ]);

            // Update or create seller
            $seller = Seller::updateOrCreate(
                [
                    'tenant_id' => $purchase->tenant_id,
                    'phone' => $request->seller_phone,
                ],
                [
                    'name' => $request->seller_name,
                    'nic_or_reg' => $request->seller_nic ?? null,
                    'address' => $request->seller_address,
                    'email' => $request->seller_email ?? null,
                    'seller_type' => $request->seller_type ?? 'individual',
                ]
            );

            // Sync sellers (replace existing)
            $purchase->sellers()->sync([$seller->id]);

            DB::commit();

            $purchase->load(['vehicle', 'sellers', 'paymentMethod']);

            return response()->json([
                'message' => 'Car purchase updated successfully',
                'purchase' => $this->transformPurchase($purchase),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Car purchase update failed: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to update purchase',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete car purchase
     */
    public function destroy($id)
    {
        $purchase = CarPurchase::find($id);

        if (!$purchase) {
            return response()->json(['error' => 'Purchase not found'], 404);
        }

        try {
            // Delete document file if exists
            if ($purchase->document_path) {
                $path = str_replace('/storage/', '', $purchase->document_path);
                Storage::disk('public')->delete($path);
            }

            // Detach sellers
            $purchase->sellers()->detach();

            $purchase->delete();

            return response()->json([
                'message' => 'Car purchase deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Car purchase deletion failed: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to delete purchase',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Transform purchase data for response
     */
    /**
     * Accept a CarPurchase model or a stdClass/array from raw queries
     *
     * @param mixed $purchase
     */
    private function transformPurchase($purchase): array
    {
        $data = [
            'id' => $purchase->id,
            'vehicleId' => $purchase->vehicle_id,
            'purchaseDate' => $purchase->purchase_date,
            'purchasePrice' => $purchase->purchase_price,
            'paymentMethodId' => $purchase->payment_method_id,
            'invoiceNumber' => $purchase->invoice_number,
            'taxAmount' => $purchase->tax_amount,
            'branch' => $purchase->branch,
            'documentPath' => $purchase->document_path,
            'taxDetails' => $purchase->tax_details,
            'createdAt' => $purchase->created_at,
            'updatedAt' => $purchase->updated_at,
        ];

        // Add vehicle info
        if ($purchase->vehicle) {
            $data['vehicle'] = [
                'id' => $purchase->vehicle->id,
                'stockNumber' => $purchase->vehicle->stock_number,
                'make' => $purchase->vehicle->make,
                'model' => $purchase->vehicle->model,
                'year' => $purchase->vehicle->year,
                'color' => $purchase->vehicle->color,
                'price' => $purchase->vehicle->price,
            ];
        }

        // Add payment method info
        if ($purchase->paymentMethod) {
            $data['paymentMethod'] = [
                'id' => $purchase->paymentMethod->id,
                'name' => $purchase->paymentMethod->name,
            ];
        }

        // Add sellers info
        $data['sellers'] = $purchase->sellers->map(function ($seller) {
            return [
                'id' => $seller->id,
                'name' => $seller->name,
                'nicOrReg' => $seller->nic_or_reg,
                'address' => $seller->address,
                'phone' => $seller->phone,
                'email' => $seller->email,
                'sellerType' => $seller->seller_type,
            ];
        });

        return $data;
    }

    /**
     * Validate purchase data
     */
    private function validatePurchaseData(Request $request, $isUpdate = false): \Illuminate\Contracts\Validation\Validator
    {
        $rules = [
            'vehicle_id' => 'required|exists:vehicles,id',
            'purchase_date' => 'required|date',
            'purchase_price' => 'required|numeric|min:0',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'invoice_number' => 'required|string|max:100',
            'tax_amount' => 'nullable|numeric|min:0',
            'branch' => 'nullable|string|max:150',
            'tax_details' => 'nullable|string',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            
            // Seller validation
            'seller_name' => 'required|string|max:150',
            'seller_address' => 'required|string|max:255',
            'seller_phone' => 'required|string|max:20',
            'seller_email' => 'nullable|email|max:150',
            'seller_nic' => 'nullable|string|max:100',
            'seller_type' => 'nullable|in:individual,dealer,auction',
        ];

        return Validator::make($request->all(), $rules);
    }
}
