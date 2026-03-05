<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransferRequest;
use App\Models\Dealer;
use App\Models\Transfer;
use App\Models\Vehicle;
use App\Services\TransferTransformer;
use App\Traits\ResolvesTenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransferController extends Controller
{
    use ResolvesTenant;

    public function __construct(private TransferTransformer $transformer) {}

    public function index(Request $request)
    {
        $query = Transfer::with(['vehicle', 'fromDealer', 'toDealer']);

        if (Auth::check() && Auth::user()->tenant_id) {
            $query->where('tenant_id', Auth::user()->tenant_id);
        }

        foreach (['vehicle_id', 'from_dealer_id', 'to_dealer_id', 'status'] as $filter) {
            if ($request->has($filter)) {
                $query->where($filter, $request->$filter);
            }
        }

        if ($request->has('start_date')) {
            $query->where('transfer_date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('transfer_date', '<=', $request->end_date);
        }

        return response()->json([
            'transfers' => $query->orderBy('created_at', 'desc')->get()
                ->map(fn($t) => $this->transformer->transform($t)),
        ]);
    }

    public function show($id)
    {
        $transfer = Transfer::with(['vehicle', 'fromDealer', 'toDealer'])->find($id);

        if (!$transfer) {
            return response()->json(['error' => 'Transfer not found'], 404);
        }

        return response()->json(['transfer' => $this->transformer->transform($transfer)]);
    }

    public function store(StoreTransferRequest $request)
    {
        try {
            DB::beginTransaction();

            $transfer = Transfer::create([
                'tenant_id' => $this->resolveTenantId(),
                'vehicle_id' => $request->vehicle_id,
                'from_dealer_id' => $request->from_dealer_id ?: null,
                'to_dealer_id' => $request->to_dealer_id ?: null,
                'transfer_date' => $request->transfer_date,
                'transfer_price' => $request->transfer_price ?? 0,
                'transport_cost' => $request->transport_cost ?? 0,
                'status' => $request->status ?? 'pending',
                'responsible_person' => $request->responsible_person ?? null,
            ]);

            if ($request->status === 'completed' && $request->to_dealer_id) {
                $vehicle = Vehicle::find($request->vehicle_id);
                if ($vehicle) {
                    $vehicle->update(['dealer_id' => $request->to_dealer_id]);
                }
            }

            DB::commit();
            $transfer->load(['vehicle', 'fromDealer', 'toDealer']);

            return response()->json([
                'message' => 'Transfer created successfully',
                'transfer' => $this->transformer->transform($transfer),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transfer creation failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create transfer', 'message' => $e->getMessage()], 500);
        }
    }

    public function update(StoreTransferRequest $request, $id)
    {
        $transfer = Transfer::find($id);

        if (!$transfer) {
            return response()->json(['error' => 'Transfer not found'], 404);
        }

        try {
            DB::beginTransaction();

            $wasCompleted = $transfer->status === 'completed';
            $willBeCompleted = $request->status === 'completed';

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

            if (!$wasCompleted && $willBeCompleted && $request->to_dealer_id) {
                $vehicle = Vehicle::find($request->vehicle_id);
                if ($vehicle) {
                    $vehicle->update(['dealer_id' => $request->to_dealer_id]);
                }
            }

            DB::commit();
            $transfer->load(['vehicle', 'fromDealer', 'toDealer']);

            return response()->json([
                'message' => 'Transfer updated successfully',
                'transfer' => $this->transformer->transform($transfer),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transfer update failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update transfer', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $transfer = Transfer::find($id);

        if (!$transfer) {
            return response()->json(['error' => 'Transfer not found'], 404);
        }

        try {
            $transfer->delete();
            return response()->json(['message' => 'Transfer deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Transfer deletion failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete transfer', 'message' => $e->getMessage()], 500);
        }
    }

    public function getDealers()
    {
        $dealers = Dealer::select('id', 'name', 'address', 'status')
            ->orderBy('name')
            ->get();

        return response()->json(['dealers' => $dealers]);
    }
}
