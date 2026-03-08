<?php

namespace App\Services;

use App\Models\Vehicle;

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
     * Resolve a stored image URL to an absolute URL using the current APP_URL.
     *
     * Handles three cases:
     *  1. Relative /storage/... path (old format) → rebuild with url()
     *  2. Absolute URL whose path starts with /storage/ (stored with a different
     *     APP_URL, e.g. http://localhost:8000) → extract path, rebuild with url()
     *  3. External URL (S3, CDN, etc.) → use as-is
     */
    private function resolveImageUrl(string $storedUrl): string
    {
        // Case 1: relative path
        if (str_starts_with($storedUrl, '/storage/')) {
            return url($storedUrl);
        }

        // Case 2: absolute URL for local disk (path begins with /storage/)
        $parsed = parse_url($storedUrl);
        if (isset($parsed['path']) && str_starts_with($parsed['path'], '/storage/')) {
            return url($parsed['path']);
        }

        // Case 3: external URL (S3, CDN, etc.) — use as-is
        return $storedUrl;
    }
}
