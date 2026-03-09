<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class VehicleImageController extends Controller
{
    /**
     * Upload images for a specific vehicle.
     */
    public function upload(Request $request, $id)
    {
        $vehicle = Vehicle::find($id);

        if (!$vehicle) {
            return response()->json(['error' => 'Vehicle not found'], 404);
        }

        Log::info('Upload Images Request Data:', [
            'all_input'    => $request->all(),
            'all_files'    => $request->allFiles(),
            'content_type' => $request->header('Content-Type'),
        ]);

        // Parse images from indexed multipart input
        $images     = [];
        $imageIndex = 0;

        while ($request->has("images.$imageIndex.category")) {
            $category = $request->input("images.$imageIndex.category");
            $file     = $request->file("images.$imageIndex.file");

            if ($category && $file) {
                $images[] = ['category' => $category, 'file' => $file];
            }

            $imageIndex++;
        }

        if (empty($images)) {
            return response()->json(['error' => 'No images provided'], 422);
        }

        $validator = Validator::make(
            ['images' => $images],
            [
                'images'              => 'required|array',
                'images.*.category'   => 'required|in:frontView,rearView,leftSideView,rightSideView,interior,engine,dashboard,others',
                'images.*.file'       => 'required|file|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => 'Validation failed', 'messages' => $validator->errors()], 422);
        }

        try {
            $uploaded = [];

            foreach ($images as $imageData) {
                $file     = $imageData['file'];
                $category = $imageData['category'];

                // Always upload to Supabase storage bucket.
                // Store directly under the vehicle ID folder.
                $path     = $file->store((string) $vehicle->id, 'supabase');
                // Resolve the full public URL immediately and persist it in the DB.
                // This keeps image retrieval simple: read the URL, use it directly.
                $imageUrl = Storage::disk('supabase')->url($path);

                Log::info('Image stored in Supabase:', ['path' => $path, 'category' => $category, 'url' => $imageUrl]);

                $image    = VehicleImage::create([
                    'vehicle_id'     => $vehicle->id,
                    'image_category' => $category,
                    'image_url'      => $imageUrl, // full Supabase public URL
                ]);
                $imageArr = $image->toArray();
                $uploaded[] = $imageArr;
            }

            return response()->json(['message' => 'Images uploaded successfully', 'images' => $uploaded], 201);

        } catch (\Exception $e) {
            Log::error('Image upload failed:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to upload images', 'message' => $e->getMessage()], 500);
        }
    }
}
