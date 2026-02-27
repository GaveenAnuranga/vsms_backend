<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transfer;
use App\Models\Vehicle;
use App\Models\Dealer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Controller for handling car transfer operations
 */
class TransferController extends Controller
{
    /**
     * Get all transfers with optional filters
     */
    public function index(Request $request)
    {
        $query = Transfer::with(['vehicle', 'fromDealer', 'toDealer']);

        // Filter by tenant if authenticated
        if (Auth::check() && Auth::user()->tenant_id) {
            $query->where('tenant_id', Auth::user()->tenant_id);
        }

        // Filter by vehicle
        if ($request->has('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        // Filter by from dealer
        if ($request->has('from_dealer_id')) {
            $query->where('from_dealer_id', $request->from_dealer_id);
        }

        // Filter by to dealer
        if ($request->has('to_dealer_id')) {
            $query->where('to_dealer_id', $request->to_dealer_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('transfer_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('transfer_date', '<=', $request->end_date);
        }

        $transfers = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'transfers' => $transfers->map(function ($transfer) {
                return $this->transformTransfer($transfer);
            }),
        ]);
    }

    /**
     * Get single transfer by ID
     */
    public function show($id)
    {
        $transfer = Transfer::with(['vehicle', 'fromDealer', 'toDealer'])->find($id);

        if (!$transfer) {
            return response()->json(['error' => 'Transfer not found'], 404);
        }

        return response()->json([
            'transfer' => $this->transformTransfer($transfer),
        ]);
    }

    /**
     * Create new transfer
     */
    public function store(Request $request)
    {
        $validator = $this->validateTransferData($request);

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

            // Create transfer record
            $transfer = Transfer::create([
                'tenant_id' => $tenantId,
                'vehicle_id' => $request->vehicle_id,
                'from_dealer_id' => $request->from_dealer_id ?: null,
                'to_dealer_id' => $request->to_dealer_id ?: null,
                'transfer_date' => $request->transfer_date,
                'transfer_price' => $request->transfer_price ?? 0,
                'transport_cost' => $request->transport_cost ?? 0,
                'status' => $request->status ?? 'pending',
                'responsible_person' => $request->responsible_person ?? null,
            ]);

            // Update vehicle's dealer if transfer is completed
            if ($request->status === 'completed' && $request->to_dealer_id) {
                $vehicle = Vehicle::find($request->vehicle_id);
                if ($vehicle) {
                    $vehicle->update(['dealer_id' => $request->to_dealer_id]);
                    
                    // Update vehicle status to Transferred
                    if ($vehicle->status === 'Available') {
                        $vehicle->update(['status' => 'Transferred']);
                    }
                }
            }

            DB::commit();

            // Load relationships for response
            $transfer->load(['vehicle', 'fromDealer', 'toDealer']);

            return response()->json([
                'message' => 'Transfer created successfully',
                'transfer' => $this->transformTransfer($transfer),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transfer creation failed: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to create transfer',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update existing transfer
     */
    public function update(Request $request, $id)
    {
        $transfer = Transfer::find($id);

        if (!$transfer) {
            return response()->json(['error' => 'Transfer not found'], 404);
        }

        $validator = $this->validateTransferData($request, true);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Check if status is changing to completed
            $wasCompleted = $transfer->status === 'completed';
            $willBeCompleted = $request->status === 'completed';

            // Update transfer record
            $transfer->update([
                'vehicle_id' => $request->vehicle_id,
                'from_dealer_id' => $request->from_dealer_id ?: null,
                'to_dealer_id' => $request->to_dealer_id ?: null,
                'transfer_date' => $request->transfer_date,
                'transfer_price' => $request->transfer_price ?? 0,
                'transport_cost' => $request->transport_cost ?? 0,
                'status' => $request->status ?? 'pending',
                'responsible_person' => $request->responsible_person ?? null,
            ]);

            // Update vehicle's dealer if transfer is now completed
            if (!$wasCompleted && $willBeCompleted && $request->to_dealer_id) {
                $vehicle = Vehicle::find($request->vehicle_id);
                if ($vehicle) {
                    $vehicle->update(['dealer_id' => $request->to_dealer_id]);
                    
                    // Update vehicle status to Transferred
                    if ($vehicle->status === 'Available') {
                        $vehicle->update(['status' => 'Transferred']);
                    }
                }
            }

            DB::commit();

            $transfer->load(['vehicle', 'fromDealer', 'toDealer']);

            return response()->json([
                'message' => 'Transfer updated successfully',
                'transfer' => $this->transformTransfer($transfer),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transfer update failed: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to update transfer',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete transfer
     */
    public function destroy($id)
    {
        $transfer = Transfer::find($id);

        if (!$transfer) {
            return response()->json(['error' => 'Transfer not found'], 404);
        }

        try {
            $transfer->delete();

            return response()->json([
                'message' => 'Transfer deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Transfer deletion failed: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to delete transfer',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all dealers (for dropdown).
     * Returns ALL dealers regardless of status so that the "From Entity"
     * field can correctly display a vehicle's current branch even if that
     * branch is marked inactive.
     */
    public function getDealers()
    {
        $dealers = Dealer::select('id', 'name', 'address', 'status')
            ->orderBy('name')
            ->get();

        return response()->json(['dealers' => $dealers]);
    }

    /**
     * Transform transfer data for response
     */
    private function transformTransfer($transfer): array
    {
        $data = [
            'id' => $transfer->id,
            'tenantId' => $transfer->tenant_id,
            'vehicleId' => $transfer->vehicle_id,
            'fromDealerId' => $transfer->from_dealer_id,
            'toDealerId' => $transfer->to_dealer_id,
            'transferDate' => $transfer->transfer_date,
            'transferPrice' => $transfer->transfer_price,
            'transportCost' => $transfer->transport_cost,
            'status' => $transfer->status,
            'responsiblePerson' => $transfer->responsible_person,
            'createdAt' => $transfer->created_at,
            'updatedAt' => $transfer->updated_at,
        ];

        // Add vehicle info
        if ($transfer->vehicle) {
            $data['vehicle'] = [
                'id' => $transfer->vehicle->id,
                'stockNumber' => $transfer->vehicle->stock_number,
                'make' => $transfer->vehicle->make,
                'model' => $transfer->vehicle->model,
                'year' => $transfer->vehicle->year,
                'color' => $transfer->vehicle->color,
                'price' => $transfer->vehicle->price,
            ];
        }

        // Add from dealer info
        if ($transfer->fromDealer) {
            $data['fromDealer'] = [
                'id' => $transfer->fromDealer->id,
                'name' => $transfer->fromDealer->name,
                'address' => $transfer->fromDealer->address,
            ];
        }

        // Add to dealer info
        if ($transfer->toDealer) {
            $data['toDealer'] = [
                'id' => $transfer->toDealer->id,
                'name' => $transfer->toDealer->name,
                'address' => $transfer->toDealer->address,
            ];
        }

        return $data;
    }

    /**
     * Validate transfer data
     */
    private function validateTransferData(Request $request, $isUpdate = false): \Illuminate\Contracts\Validation\Validator
    {
        $rules = [
            'vehicle_id' => 'required|exists:vehicles,id',
            'from_dealer_id' => 'nullable|exists:dealers,id',
            'to_dealer_id' => 'required|exists:dealers,id',
            'transfer_date' => 'required|date',
            'transfer_price' => 'nullable|numeric|min:0',
            'transport_cost' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:pending,completed',
            'responsible_person' => 'nullable|string|max:255',
        ];

        return Validator::make($request->all(), $rules);
    }
}
