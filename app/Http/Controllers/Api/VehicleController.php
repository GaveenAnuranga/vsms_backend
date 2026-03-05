<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Models\Vehicle;
use App\Services\VehicleService;
use App\Services\VehicleTransformer;
use App\Traits\ResolvesTenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VehicleController extends Controller
{
    use ResolvesTenant;

    public function __construct(
        private VehicleService $vehicleService,
        private VehicleTransformer $transformer
    ) {}
public function index(Request $request)
    {
        $query = Vehicle::with(['registration', 'import', 'notification', 'images', 'dealer', 'tenant']);

        if ($request->has('status')) {
            if ($request->status !== 'all') {
                $query->byStatus($request->status);
            }
        } else {
            $query->where('status', 'Available');
        }

        if ($request->has('dealer')) {
            $query->byDealer($request->dealer);
        }

        $page  = $request->input('page', 1);
        $limit = $request->input('limit', 50);
        $total = $query->count();

        $vehicles = $query->skip(($page - 1) * $limit)->take($limit)->get();

        return response()->json([
            'vehicles' => $vehicles->map(fn($v) => $this->transformer->transform($v)),
            'total'    => $total,
            'page'     => (int) $page,
            'limit'    => (int) $limit,
        ]);
    }

    public function show($id)
    {
        $vehicle = Vehicle::with(['registration', 'import', 'notification', 'images', 'dealer', 'tenant'])->find($id);

        if (!$vehicle) {
            return response()->json(['error' => 'Vehicle not found'], 404);
        }

        return response()->json(['vehicle' => $this->transformer->transform($vehicle)]);
    }

    public function showPublic($id)
    {
        $vehicle = Vehicle::with(['images', 'dealer'])->find($id);

        if (!$vehicle) {
            return response()->json(['error' => 'Vehicle not found'], 404);
        }

        return response()->json(['vehicle' => $this->transformer->transformForLanding($vehicle)]);
    }

    public function getLandingPageVehicles(Request $request)
    {
        $query = Vehicle::with(['images', 'dealer'])->where('status', 'Available');

        foreach (['make', 'fuel_type', 'transmission_type'] as $filter) {
            if ($request->has($filter)) {
                $query->where($filter, $request->$filter);
            }
        }

        if ($request->has('min_price')) $query->where('price', '>=', $request->min_price);
        if ($request->has('max_price')) $query->where('price', '<=', $request->max_price);

        $page  = $request->input('page', 1);
        $limit = $request->input('limit', 8);
        $total = $query->count();

        $vehicles = $query->orderBy('created_at', 'desc')->skip(($page - 1) * $limit)->take($limit)->get();

        return response()->json([
            'vehicles' => $vehicles->map(fn($v) => $this->transformer->transformForLanding($v)),
            'total'    => $total,
            'page'     => (int) $page,
            'limit'    => (int) $limit,
        ]);
    }

    public function store(StoreVehicleRequest $request)
    {
        try {
            DB::beginTransaction();

            $vehicle = $this->vehicleService->createVehicle($request, $this->resolveTenantId());

            if ($request->registrationType === 'Registered') {
                $this->vehicleService->syncRegistration($vehicle->id, $request->registeredDetails);
            } else {
                $this->vehicleService->syncImport($vehicle->id, $request->unregisteredDetails);
                $this->vehicleService->syncNotification($vehicle->id, $request->unregisteredDetails);
            }

            if ($request->has('images')) {
                $this->vehicleService->createImages($vehicle->id, $request->images);
            }

            DB::commit();

            $vehicle->load(['registration', 'import', 'notification', 'images', 'dealer']);

            return response()->json([
                'message' => 'Vehicle created successfully',
                'vehicle' => $this->transformer->transform($vehicle),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create vehicle', 'message' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateVehicleRequest $request, $id)
    {
        $vehicle = Vehicle::find($id);

        if (!$vehicle) {
            return response()->json(['error' => 'Vehicle not found'], 404);
        }

        try {
            DB::beginTransaction();

            $this->vehicleService->updateVehicle($vehicle, $request);

            if ($request->registrationType === 'Registered') {
                $vehicle->import()->delete();
                $this->vehicleService->syncNotification($vehicle->id, null);
                $this->vehicleService->syncRegistration($vehicle->id, $request->registeredDetails ?? []);
            } else {
                $vehicle->registration()->delete();
                $this->vehicleService->syncImport($vehicle->id, $request->unregisteredDetails ?? []);
                $this->vehicleService->syncNotification($vehicle->id, $request->unregisteredDetails ?? []);
            }

            DB::commit();

            $vehicle->load(['registration', 'import', 'notification', 'images', 'dealer']);

            return response()->json([
                'message' => 'Vehicle updated successfully',
                'vehicle' => $this->transformer->transform($vehicle),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update vehicle', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $vehicle = Vehicle::find($id);

        if (!$vehicle) {
            return response()->json(['error' => 'Vehicle not found'], 404);
        }

        try {
            $vehicle->delete();
            return response()->json(['message' => 'Vehicle deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete vehicle', 'message' => $e->getMessage()], 500);
        }
    }
}

