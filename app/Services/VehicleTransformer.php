<?php

namespace App\Services;

use App\Models\Vehicle;
use Illuminate\Support\Facades\Storage;

class VehicleTransformer
{
    /**
     * Full vehicle transform for admin / authenticated endpoints.
     */
    public function transform(Vehicle $vehicle): array
    {
        $data = [
            'id'               => $vehicle->id,
            'stockNumber'      => $vehicle->stock_number,
            'make'             => $vehicle->make,
            'model'            => $vehicle->model,
            'subModel'         => $vehicle->sub_model,
            'vehicleType'      => $vehicle->vehicle_type,
            'year'             => $vehicle->year,
            'color'            => $vehicle->color,
            'countryOfOrigin'  => $vehicle->country_of_origin,
            'fuelType'         => $vehicle->fuel_type,
            'mileage'          => $vehicle->mileage,
            'transmissionType' => $vehicle->transmission_type,
            'engineSize'       => $vehicle->engine_size,
            'registrationType' => $vehicle->registration_type,
            'vin'              => $vehicle->vin,
            'price'            => $vehicle->price,
            'dealer'           => $vehicle->dealer->name ?? null,
            'dealerId'         => $vehicle->dealer_id,
            'tenant'           => $vehicle->tenant->name ?? null,
            'tenantId'         => $vehicle->tenant_id,
            'status'           => $vehicle->status,
            'description'      => $vehicle->description,
        ];

        if ($vehicle->registration) {
            $r = $vehicle->registration;
            $data['registeredDetails'] = [
                'vehicleNumber'          => $r->vehicle_number,
                'registrationYear'       => $r->registration_year,
                'ownerName'              => $r->owner_name,
                'ownerContact'           => $r->owner_contact,
                'serviceRecord'          => $r->service_record,
                'registrationNumber'     => $r->registration_number,
                'numberPlate'            => $r->number_plate,
                'registrationDate'       => $r->registration_date,
                'numberOfPreviousOwners' => $r->number_of_previous_owners,
            ];
        }

        if ($vehicle->import) {
            $i = $vehicle->import;
            $data['unregisteredDetails'] = [
                'chassisNumber'            => $i->chassis_number,
                'engineNumber'             => $i->engine_number,
                'importedDate'             => $i->imported_date ? $i->imported_date->format('Y-m-d') : null,
                'exporterName'             => $i->exporter_name,
                'exporterContact'          => $i->exporter_contact,
                'registerNotification'     => (bool) $i->register_notification,
                'registerNotificationDate' => $i->register_notification_date
                    ? $i->register_notification_date->format('Y-m-d')
                    : null,
                'notificationNote'         => $vehicle->notification?->note,
                'notificationDismissed'    => (bool) $i->notification_dismissed,
                'importYear'               => $i->import_year,
                'auctionGrade'             => $i->auction_grade,
            ];
        }

        $data['images'] = $this->groupImages($vehicle->images);

        return $data;
    }

    /**
     * Simplified transform for public / landing-page endpoints.
     */
    public function transformForLanding(Vehicle $vehicle): array
    {
        $data = [
            'id'               => $vehicle->id,
            'stockNumber'      => $vehicle->stock_number,
            'make'             => $vehicle->make,
            'model'            => $vehicle->model,
            'vehicleType'      => $vehicle->vehicle_type,
            'year'             => $vehicle->year,
            'color'            => $vehicle->color,
            'countryOfOrigin'  => $vehicle->country_of_origin,
            'fuelType'         => $vehicle->fuel_type,
            'mileage'          => $vehicle->mileage,
            'transmissionType' => $vehicle->transmission_type,
            'registrationType' => $vehicle->registration_type,
            'price'            => $vehicle->price,
            'dealer'           => $vehicle->dealer->name ?? null,
            'status'           => $vehicle->status,
        ];

        $data['images'] = $this->groupImages($vehicle->images);

        return $data;
    }

    /**
     * Group vehicle images by category, resolving full storage URLs.
     */
    public function groupImages($images): array
    {
        $grouped = [
            'frontView'    => null,
            'rearView'     => null,
            'leftSideView' => null,
            'rightSideView'=> null,
            'interior'     => null,
            'engine'       => null,
            'dashboard'    => null,
            'others'       => [],
        ];

        foreach ($images as $image) {
            $url = $this->resolveImageUrl($image->image_url);

            if ($image->image_category === 'others') {
                $grouped['others'][] = $url;
            } else {
                $grouped[$image->image_category] = $url;
            }
        }

        return $grouped;
    }

    /**
     * Resolve a stored image value to an absolute URL.
     *
     * Handles four cases in order:
     *  1. Plain storage disk path (new format) e.g. "vehicles/51/xxx.webp"
     *     → regenerate via Storage::disk()->url() using the current disk config.
     *  2. Relative /storage/... web path (legacy local format)
     *     → rebuild with url() using current APP_URL.
     *  3. Absolute URL whose path starts with /storage/ (stored with a different
     *     APP_URL, e.g. http://localhost:8000) → extract path, rebuild with url().
     *  4. External URL (S3, CDN, etc.) → use as-is.
     */
    private function resolveImageUrl(string $storedValue): string
    {
        // Case 1: plain storage disk path — no scheme, no leading slash
        if (! str_starts_with($storedValue, '/') && ! preg_match('/^https?:\/\//', $storedValue)) {
            $disk = env('IMAGE_STORAGE_DISK', 'public');
            return Storage::disk($disk)->url($storedValue);
        }

        // Case 2: web-relative path starting with /storage/
        if (str_starts_with($storedValue, '/storage/')) {
            return url($storedValue);
        }

        // Case 3 & 4: absolute URL
        $parsed = parse_url($storedValue);
        if (isset($parsed['path']) && str_starts_with($parsed['path'], '/storage/')) {
            // Local disk URL from a different APP_URL — strip host, rebuild cleanly
            return url($parsed['path']);
        }

        // Case 4: external URL (S3, CDN, etc.) — use as-is
        return $storedValue;
    }
}
