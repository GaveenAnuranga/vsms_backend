<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCarPurchaseRequest;
use App\Models\CarPurchase;
use App\Models\Dealer;
use App\Models\PaymentMethod;
use App\Models\Seller;
use App\Models\Vehicle;
use App\Services\CarPurchaseService;
use App\Services\PurchaseTransformer;
use App\Traits\ResolvesTenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CarPurchaseController extends Controller
{
    use ResolvesTenant;

    public function __construct(
        private CarPurchaseService $purchaseService,
        private PurchaseTransformer $transformer
    ) {}
    public function index(Request $request)
    {
        $query = CarPurchase::with(['vehicle', 'sellers', 'paymentMethod']);

        if (Auth::check() && Auth::user()->tenant_id) {
            $query->where('tenant_id', Auth::user()->tenant_id);
        }

        if ($request->has('vehicle_id'))  $query->where('vehicle_id', $request->vehicle_id);
        if ($request->has('start_date'))  $query->where('purchase_date', '>=', $request->start_date);
        if ($request->has('end_date'))    $query->where('purchase_date', '<=', $request->end_date);

        $purchases = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'purchases' => $purchases->map(fn($p) => $this->transformer->transform($p)),
        ]);
    }

    public function show($id)
    {
        $purchase = CarPurchase::with(['vehicle', 'sellers', 'paymentMethod'])->find($id);

        if (!$purchase) {
            return response()->json(['error' => 'Purchase not found'], 404);
        }

        return response()->json(['purchase' => $this->transformer->transform($purchase)]);
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
                'dealer_id' => $vehicle->dealer_id,
            ],
        ]);
    }

    public function getBranches()
    {
        $dealers = Dealer::where('status', 'active')->select('id', 'name', 'address')->orderBy('name')->get();

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

    public function store(StoreCarPurchaseRequest $request)
    {
        try {
            DB::beginTransaction();

            $tenantId     = $this->resolveTenantId();
            $documentPath = $this->purchaseService->handleDocumentUpload($request);
            $response     = $this->purchaseService->storePurchase($request, $tenantId, $documentPath);

            DB::commit();

            return response()->json(['message' => 'Car purchase created successfully', 'purchase' => $response], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Car purchase creation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create purchase', 'message' => $e->getMessage()], 500);
        }
    }

    public function update(StoreCarPurchaseRequest $request, $id)
    {
        $purchase = CarPurchase::find($id);

        if (!$purchase) {
            return response()->json(['error' => 'Purchase not found'], 404);
        }

        try {
            DB::beginTransaction();

            $documentPath = $this->purchaseService->handleDocumentUpload($request, $purchase->document_path);

            $purchase->update([
                'vehicle_id'        => $request->vehicle_id,
                'purchase_date'     => $request->purchase_date,
                'purchase_price'    => $request->purchase_price,
                'payment_method_id' => $request->payment_method_id,
                'invoice_number'    => $request->invoice_number,
                'tax_amount'        => $request->tax_amount ?? 0,
                'branch'            => $request->branch,
                'document_path'     => $documentPath,
                'tax_details'       => $request->tax_details,
            ]);

            $seller = Seller::updateOrCreate(
                ['tenant_id' => $purchase->tenant_id, 'phone' => $request->seller_phone],
                [
                    'name'        => $request->seller_name,
                    'nic_or_reg'  => $request->seller_nic ?? null,
                    'address'     => $request->seller_address,
                    'email'       => $request->seller_email ?? null,
                    'seller_type' => $request->seller_type ?? 'individual',
                ]
            );

            $purchase->sellers()->sync([$seller->id]);

            DB::commit();

            $purchase->load(['vehicle', 'sellers', 'paymentMethod']);

            return response()->json([
                'message'  => 'Car purchase updated successfully',
                'purchase' => $this->transformer->transform($purchase),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Car purchase update failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update purchase', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $purchase = CarPurchase::find($id);

        if (!$purchase) {
            return response()->json(['error' => 'Purchase not found'], 404);
        }

        try {
            if ($purchase->document_path) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $purchase->document_path));
            }

            $purchase->sellers()->detach();
            $purchase->delete();

            return response()->json(['message' => 'Car purchase deleted successfully']);

        } catch (\Exception $e) {
            Log::error('Car purchase deletion failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete purchase', 'message' => $e->getMessage()], 500);
        }
    }
}
