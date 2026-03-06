<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\VehicleRegistration;
use App\Models\VehicleImport;
use App\Models\VehicleImage;
use App\Models\VehicleNotification;
use Illuminate\Http\Request;

class VehicleService
{
    /**
     * Create a new vehicle record from request data.
     */
    public function createVehicle(Request $request, int $tenantId): Vehicle
    {
        return Vehicle::create([
            'tenant_id'         => $tenantId,
            'vehicle_code'      => $this->generateVehicleCode(),
            'stock_number'      => $request->stockNumber,
            'vehicle_type'      => $request->vehicleType,
            'make'              => $request->make,
            'model'             => $request->model,
            'sub_model'         => $request->subModel,
            'year'              => $request->year,
            'color'             => $request->color,
            'country_of_origin' => $request->countryOfOrigin,
            'fuel_type'         => $request->fuelType,
            'mileage'           => $request->mileage,
            'transmission_type' => $request->transmissionType,
            'engine_size'       => $request->engineSize,
            'vin'               => $request->vin,
            'registration_type' => $request->registrationType,
            'price'             => $request->price,
            'dealer_id'         => $request->dealerId,
            'status'            => $request->status,
            'description'       => $request->description,
        ]);
    }

    /**
     * Apply core field updates to an existing vehicle.
     */
    public function updateVehicle(Vehicle $vehicle, Request $request): void
    {
        $vehicle->update([
            'stock_number'      => $request->stockNumber ?? $vehicle->stock_number,
            'vehicle_type'      => $request->vehicleType ?? $vehicle->vehicle_type,
            'make'              => $request->make,
            'model'             => $request->model,
            'sub_model'         => $request->subModel,
            'year'              => $request->year,
            'color'             => $request->color,
            'country_of_origin' => $request->countryOfOrigin,
            'fuel_type'         => $request->fuelType,
            'mileage'           => $request->mileage,
            'transmission_type' => $request->transmissionType,
            'engine_size'       => $request->engineSize,
            'vin'               => $request->vin,
            'registration_type' => $request->registrationType,
            'price'             => $request->price,
            'dealer_id'         => $request->dealerId,
            'status'            => $request->status,
            'description'       => $request->description,
        ]);
    }

    /**
     * Create or update registration details for a vehicle.
     */
    public function syncRegistration(int $vehicleId, ?array $details): void
    {
        if (!$details) return;

        VehicleRegistration::updateOrCreate(
            ['vehicle_id' => $vehicleId],
            [
                'vehicle_number'            => $details['vehicleNumber'] ?? null,
                'registration_year'         => $details['registrationYear'] ?? null,
                'owner_name'                => $details['ownerName'] ?? null,
                'owner_contact'             => $details['ownerContact'] ?? null,
                'service_record'            => $details['serviceRecord'] ?? null,
                'registration_number'       => $details['registrationNumber'] ?? null,
                'number_plate'              => $details['numberPlate'] ?? null,
                'registration_date'         => $details['registrationDate'] ?? null,
                'number_of_previous_owners' => $details['numberOfPreviousOwners'] ?? 0,
            ]
        );
    }

    /**
     * Create or update import details for a vehicle.
     */
    public function syncImport(int $vehicleId, ?array $details): void
    {
        if (!$details) return;

        VehicleImport::updateOrCreate(
            ['vehicle_id' => $vehicleId],
            [
                'chassis_number'             => $details['chassisNumber'] ?? null,
                'engine_number'              => $details['engineNumber'] ?? null,
                'imported_date'              => $details['importedDate'] ?? null,
                'exporter_name'              => $details['exporterName'] ?? null,
                'exporter_contact'           => $details['exporterContact'] ?? null,
                'register_notification'      => $details['registerNotification'] ?? false,
                'register_notification_date' => $details['registerNotificationDate'] ?? null,
                'notification_dismissed'     => $details['notificationDismissed'] ?? false,
                'import_year'                => $details['importYear'] ?? null,
                'auction_grade'              => $details['auctionGrade'] ?? null,
            ]
        );
    }

    /**
     * Create or update the vehicle_notifications record for an unregistered vehicle.
     * Deletes the record when the notification is turned off.
     */
    public function syncNotification(int $vehicleId, ?array $details): void
    {
        if (!$details) {
            VehicleNotification::where('vehicle_id', $vehicleId)->delete();
            return;
        }

        $active = $details['registerNotification'] ?? false;
        $date   = $details['registerNotificationDate'] ?? null;
        $note   = $details['notificationNote'] ?? null;

        if ($active && $date) {
            VehicleNotification::updateOrCreate(
                ['vehicle_id' => $vehicleId],
                ['date' => $date, 'note' => $note]
            );
        } else {
            VehicleNotification::where('vehicle_id', $vehicleId)->delete();
        }
    }

    /**
     * Persist a batch of vehicle images by category.
     */
    public function createImages(int $vehicleId, array $images): void
    {
        foreach ($images as $category => $urls) {
            if ($category === 'others' && is_array($urls)) {
                foreach ($urls as $url) {
                    VehicleImage::create(['vehicle_id' => $vehicleId, 'image_category' => 'others', 'image_url' => $url]);
                }
            } elseif (!is_array($urls) && $urls) {
                VehicleImage::create(['vehicle_id' => $vehicleId, 'image_category' => $category, 'image_url' => $urls]);
            }
        }
    }

    /**
     * Generate a unique internal vehicle code.
     */
    public function generateVehicleCode(): string
    {
        do {
            $code = 'VH' . date('Y') . rand(1000, 9999);
        } while (Vehicle::where('vehicle_code', $code)->exists());

        return $code;
    }
}
