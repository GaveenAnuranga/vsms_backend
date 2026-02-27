<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleRegistration;
use App\Models\VehicleImport;
use App\Models\VehicleImage;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VehicleController extends Controller
{
    /**
     * Get all vehicles with optional filters
     * By default, returns only Available vehicles for public display
     * Pass status=all to get all vehicles regardless of status
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Vehicle::with(['registration', 'import', 'images', 'dealer', 'tenant']);

        // By default, only show Available vehicles (can be overridden with status parameter)
        if ($request->has('status')) {
            if ($request->status !== 'all') {
                $query->byStatus($request->status);
            }
            // If status=all, don't filter by status at all
        } else {
            $query->where('status', 'Available');
        }

        if ($request->has('dealer')) {
            $query->byDealer($request->dealer);
        }

        // Pagination
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 50);

        $total = $query->count();
        $vehicles = $query->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        // Transform data
        $vehiclesData = $vehicles->map(function (Vehicle $vehicle) {
            return $this->transformVehicle($vehicle);
        });

        return response()->json([
            'vehicles' => $vehiclesData,
            'total' => $total,
            'page' => (int) $page,
            'limit' => (int) $limit,
        ]);
    }

    /**
     * Get single vehicle by ID (Admin/Authenticated users only)
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $vehicle = Vehicle::with(['registration', 'import', 'images', 'dealer', 'tenant'])
            ->find($id);

        if (!$vehicle) {
            return response()->json(['error' => 'Vehicle not found'], 404);
        }

        return response()->json([
            'vehicle' => $this->transformVehicle($vehicle),
        ]);
    }

    /**
     * Get single vehicle by ID for public display
     * Returns limited data without sensitive information
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function showPublic($id)
    {
        $vehicle = Vehicle::with(['images', 'dealer'])
            ->find($id);

        if (!$vehicle) {
            return response()->json(['error' => 'Vehicle not found'], 404);
        }

        return response()->json([
            'vehicle' => $this->transformVehicleForLanding($vehicle),
        ]);
    }

    /**
     * Get vehicles for landing page
     * Returns available vehicles suitable for public display
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLandingPageVehicles(Request $request)
    {
        // Get all available vehicles with dealer info
        $query = Vehicle::with(['images', 'dealer'])
            ->where('status', 'Available');

        // Optional filters
        if ($request->has('make')) {
            $query->where('make', $request->make);
        }

        if ($request->has('fuel_type')) {
            $query->where('fuel_type', $request->fuel_type);
        }

        if ($request->has('transmission_type')) {
            $query->where('transmission_type', $request->transmission_type);
        }

        // Price range filter
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Pagination with default limit
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 8); // Default 8 for landing page grid

        $total = $query->count();
        $vehicles = $query->orderBy('created_at', 'desc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        // Transform data with simplified structure for landing page
        $vehiclesData = $vehicles->map(function (Vehicle $vehicle) {
            return $this->transformVehicleForLanding($vehicle);
        });

        return response()->json([
            'vehicles' => $vehiclesData,
            'total' => $total,
            'page' => (int) $page,
            'limit' => (int) $limit,
        ]);
    }

    /**
     * Create new vehicle
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate request
        $validator = $this->validateVehicleData($request);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create vehicle
            $vehicle = $this->createVehicle($request);

            // Create registration or import details
            if ($request->registrationType === 'Registered') {
                $this->createRegistration($vehicle->id, $request->registeredDetails);
            } else {
                $this->createImport($vehicle->id, $request->unregisteredDetails);
            }

            // Create images
            if ($request->has('images')) {
                $this->createImages($vehicle->id, $request->images);
            }

            DB::commit();

            // Load relationships
            $vehicle->load(['registration', 'import', 'images', 'dealer']);

            return response()->json([
                'message' => 'Vehicle created successfully',
                'vehicle' => $this->transformVehicle($vehicle),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to create vehicle',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Transform vehicle data for response
     */
    private function transformVehicle(Vehicle $vehicle): array
    {
        $data = [
            'id' => $vehicle->id,
            'stockNumber' => $vehicle->stock_number,
            'make' => $vehicle->make,
            'model' => $vehicle->model,
            'subModel' => $vehicle->sub_model,
            'vehicleType' => $vehicle->vehicle_type,
            'year' => $vehicle->year,
            'color' => $vehicle->color,
            'countryOfOrigin' => $vehicle->country_of_origin,
            'fuelType' => $vehicle->fuel_type,
            'mileage' => $vehicle->mileage,
            'transmissionType' => $vehicle->transmission_type,
            'engineSize' => $vehicle->engine_size,
            'registrationType' => $vehicle->registration_type,
            'vin' => $vehicle->vin,
            'price' => $vehicle->price,
            'dealer' => $vehicle->dealer->name ?? null,
            'dealerId' => $vehicle->dealer_id,
            'tenant' => $vehicle->tenant->name ?? null,
            'tenantId' => $vehicle->tenant_id,
            'status' => $vehicle->status,
            'description' => $vehicle->description,
        ];

        // Add registration details if registered
        if ($vehicle->registration) {
            $data['registeredDetails'] = [
                'registrationNumber' => $vehicle->registration->registration_number,
                'numberPlate' => $vehicle->registration->number_plate,
                'registrationDate' => $vehicle->registration->registration_date,
                'numberOfPreviousOwners' => $vehicle->registration->number_of_previous_owners,
            ];
        }

        // Add import details if unregistered
        if ($vehicle->import) {
            $data['unregisteredDetails'] = [
                'chassisNumber' => $vehicle->import->chassis_number,
                'engineNumber' => $vehicle->import->engine_number,
                'importYear' => $vehicle->import->import_year,
                'auctionGrade' => $vehicle->import->auction_grade,
            ];
        }

        // Add images grouped by category
        $data['images'] = $this->groupImages($vehicle->images);

        return $data;
    }

    /**
     * Transform vehicle data for landing page
     * Returns simplified data structure suitable for public display
     */
    private function transformVehicleForLanding(Vehicle $vehicle): array
    {
        $data = [
            'id' => $vehicle->id,
            'stockNumber' => $vehicle->stock_number,
            'make' => $vehicle->make,
            'model' => $vehicle->model,
            'vehicleType' => $vehicle->vehicle_type,
            'year' => $vehicle->year,
            'color' => $vehicle->color,
            'countryOfOrigin' => $vehicle->country_of_origin,
            'fuelType' => $vehicle->fuel_type,
            'mileage' => $vehicle->mileage,
            'transmissionType' => $vehicle->transmission_type,
            'registrationType' => $vehicle->registration_type,
            'price' => $vehicle->price,
            'dealer' => $vehicle->dealer->name ?? null,
            'status' => $vehicle->status,
        ];

        // Add only relevant images for landing page (front view mainly)
        $data['images'] = $this->groupImages($vehicle->images);

        return $data;
    }

    /**
     * Group images by category
     */
    private function groupImages($images): array
    {
        $grouped = [
            'frontView' => null,
            'rearView' => null,
            'leftSideView' => null,
            'rightSideView' => null,
            'interior' => null,
            'engine' => null,
            'dashboard' => null,
            'others' => [],
        ];

        foreach ($images as $image) {
            // Convert relative path to full URL
            $imageUrl = $image->image_url;
            if (str_starts_with($imageUrl, '/storage/')) {
                $imageUrl = url($imageUrl);
            }
            
            if ($image->image_category === 'others') {
                $grouped['others'][] = $imageUrl;
            } else {
                $grouped[$image->image_category] = $imageUrl;
            }
        }

        return $grouped;
    }

    /**
     * Validate vehicle data
     */
    private function validateVehicleData(Request $request): ValidatorContract
    {
        return Validator::make($request->all(), [
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'subModel' => 'nullable|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'color' => 'required|string|max:255',
            'countryOfOrigin' => 'required|string|max:255',
            'fuelType' => 'required|in:Gasoline,Diesel,Electric,Hybrid,Plug-in Hybrid',
            'mileage' => 'required|integer|min:0',
            'transmissionType' => 'required|in:Manual,Automatic,CVT,Semi-Automatic',
            'engineSize' => 'nullable|string|max:50',
            'vin' => 'nullable|string|max:255',
            'registrationType' => 'required|in:Registered,Unregistered',
            'price' => 'required|numeric|min:0',
            'dealerId' => 'required|exists:dealers,id',
            'status' => 'required|in:Available,Sold,Transferred,Reserved',
            'description' => 'nullable|string',
            'registeredDetails' => 'required_if:registrationType,Registered',
            'unregisteredDetails' => 'required_if:registrationType,Unregistered',
        ]);
    }

    /**
     * Create vehicle record
     */
    private function createVehicle(Request $request): Vehicle
    {
        $tenantId = null;
        
        // Get tenant_id from authenticated user if available
        if (Auth::check()) {
            $tenantId = Auth::user()->tenant_id;
        } else {
            // For development: use first available tenant if no auth
            $tenantId = \App\Models\Tenant::first()->id ?? 1;
        }
        
        return Vehicle::create([
            'tenant_id' => $tenantId,
            'vehicle_code' => $this->generateVehicleCode(),
            'stock_number' => $this->generateStockNumber(),
            'make' => $request->make,
            'model' => $request->model,
            'sub_model' => $request->subModel,
            'year' => $request->year,
            'color' => $request->color,
            'country_of_origin' => $request->countryOfOrigin,
            'fuel_type' => $request->fuelType,
            'mileage' => $request->mileage,
            'transmission_type' => $request->transmissionType,
            'engine_size' => $request->engineSize,
            'vin' => $request->vin,
            'registration_type' => $request->registrationType,
            'price' => $request->price,
            'dealer_id' => $request->dealerId,
            'status' => $request->status,
            'description' => $request->description,
        ]);
    }

    /**
     * Generate unique vehicle code (internal system identifier)
     */
    private function generateVehicleCode(): string
    {
        do {
            $code = 'VH' . date('Y') . rand(1000, 9999);
        } while (Vehicle::where('vehicle_code', $code)->exists());

        return $code;
    }

    /**
     * Generate unique stock number (5 uppercase alphanumeric chars, user-facing identifier)
     */
    private function generateStockNumber(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        do {
            $code = '';
            for ($i = 0; $i < 5; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while (Vehicle::where('stock_number', $code)->exists());

        return $code;
    }

    /**
     * Create registration details
     */
    private function createRegistration(int $vehicleId, ?array $details): void
    {
        if (!$details) return;

        VehicleRegistration::create([
            'vehicle_id' => $vehicleId,
            'registration_number' => $details['registrationNumber'] ?? null,
            'number_plate' => $details['numberPlate'] ?? null,
            'registration_date' => $details['registrationDate'] ?? null,
            'number_of_previous_owners' => $details['numberOfPreviousOwners'] ?? 0,
        ]);
    }

    /**
     * Create import details
     */
    private function createImport(int $vehicleId, ?array $details): void
    {
        if (!$details) return;

        VehicleImport::create([
            'vehicle_id' => $vehicleId,
            'chassis_number' => $details['chassisNumber'] ?? null,
            'engine_number' => $details['engineNumber'] ?? null,
            'import_year' => $details['importYear'] ?? null,
            'auction_grade' => $details['auctionGrade'] ?? null,
        ]);
    }

    /**
     * Create vehicle images
     */
    private function createImages(int $vehicleId, array $images): void
    {
        foreach ($images as $category => $urls) {
            if ($category === 'others' && is_array($urls)) {
                foreach ($urls as $url) {
                    VehicleImage::create([
                        'vehicle_id' => $vehicleId,
                        'image_category' => 'others',
                        'image_url' => $url,
                    ]);
                }
            } elseif (!is_array($urls) && $urls) {
                VehicleImage::create([
                    'vehicle_id' => $vehicleId,
                    'image_category' => $category,
                    'image_url' => $urls,
                ]);
            }
        }
    }

    /**
     * Update existing vehicle
     */
    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::find($id);

        if (!$vehicle) {
            return response()->json(['error' => 'Vehicle not found'], 404);
        }

        $validator = $this->validateVehicleData($request);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $vehicle->update([
                'make' => $request->make,
                'model' => $request->model,
                'sub_model' => $request->subModel,
                'year' => $request->year,
                'color' => $request->color,
                'country_of_origin' => $request->countryOfOrigin,
                'fuel_type' => $request->fuelType,
                'mileage' => $request->mileage,
                'transmission_type' => $request->transmissionType,
                'engine_size' => $request->engineSize,
                'vin' => $request->vin,
                'registration_type' => $request->registrationType,
                'price' => $request->price,
                'dealer_id' => $request->dealerId,
                'status' => $request->status,
                'description' => $request->description,
            ]);

            // Update or create registration/import details
            if ($request->registrationType === 'Registered') {
                $vehicle->import()->delete();
                $vehicle->registration()->updateOrCreate(
                    ['vehicle_id' => $vehicle->id],
                    [
                        'registration_number' => $request->registeredDetails['registrationNumber'] ?? null,
                        'number_plate' => $request->registeredDetails['numberPlate'] ?? null,
                        'registration_date' => $request->registeredDetails['registrationDate'] ?? null,
                        'number_of_previous_owners' => $request->registeredDetails['numberOfPreviousOwners'] ?? 0,
                    ]
                );
            } else {
                $vehicle->registration()->delete();
                $vehicle->import()->updateOrCreate(
                    ['vehicle_id' => $vehicle->id],
                    [
                        'chassis_number' => $request->unregisteredDetails['chassisNumber'] ?? null,
                        'engine_number' => $request->unregisteredDetails['engineNumber'] ?? null,
                        'import_year' => $request->unregisteredDetails['importYear'] ?? null,
                        'auction_grade' => $request->unregisteredDetails['auctionGrade'] ?? null,
                    ]
                );
            }

            DB::commit();

            $vehicle->load(['registration', 'import', 'images', 'dealer']);

            return response()->json([
                'message' => 'Vehicle updated successfully',
                'vehicle' => $this->transformVehicle($vehicle),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to update vehicle',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete vehicle
     */
    public function destroy($id)
    {
        $vehicle = Vehicle::find($id);

        if (!$vehicle) {
            return response()->json(['error' => 'Vehicle not found'], 404);
        }

        try {
            $vehicle->delete();

            return response()->json([
                'message' => 'Vehicle deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete vehicle',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload vehicle images
     */
    public function uploadImages(Request $request, $id)
    {
        $vehicle = Vehicle::find($id);

        if (!$vehicle) {
            return response()->json(['error' => 'Vehicle not found'], 404);
        }

        // Debug: Log what we're receiving
        Log::info('Upload Images Request Data:', [
            'all_input' => $request->all(),
            'all_files' => $request->allFiles(),
            'content_type' => $request->header('Content-Type'),
        ]);

        // Build images array manually from request
        $images = [];
        $imageIndex = 0;
        
        while ($request->has("images.$imageIndex.category")) {
            $category = $request->input("images.$imageIndex.category");
            $file = $request->file("images.$imageIndex.file");
            
            if ($category && $file) {
                $images[] = [
                    'category' => $category,
                    'file' => $file
                ];
            }
            $imageIndex++;
        }

        Log::info('Parsed images:', ['count' => count($images), 'images' => $images]);

        // Validate
        if (empty($images)) {
            return response()->json([
                'error' => 'No images provided'
            ], 422);
        }

        $validator = Validator::make(['images' => $images], [
            'images' => 'required|array',
            'images.*.category' => 'required|in:frontView,rearView,leftSideView,rightSideView,interior,engine,dashboard,others',
            'images.*.file' => 'required|file|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }

        try {
            $uploadedImages = [];

            foreach ($images as $imageData) {
                $file = $imageData['file'];
                $category = $imageData['category'];

                // Store file
                $path = $file->store('vehicles/' . $vehicle->id, 'public');

                Log::info('Image stored:', ['path' => $path, 'category' => $category]);

                // Create image record with relative path
                $relativePath = '/storage/' . $path;
                $image = VehicleImage::create([
                    'vehicle_id' => $vehicle->id,
                    'image_category' => $category,
                    'image_url' => $relativePath,
                ]);

                // Add full URL to response
                $imageResponse = $image->toArray();
                $imageResponse['image_url'] = url($relativePath);
                
                $uploadedImages[] = $imageResponse;
            }

            Log::info('All images uploaded successfully', ['count' => count($uploadedImages)]);

            return response()->json([
                'message' => 'Images uploaded successfully',
                'images' => $uploadedImages,
            ], 201);

        } catch (\Exception $e) {
            Log::error('Image upload failed:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            
            return response()->json([
                'error' => 'Failed to upload images',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
