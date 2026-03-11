<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Core required fields
            'stockNumber'                                  => 'required|string|max:10|unique:vehicles,stock_number',
            'vehicleType'                                  => 'required|in:Car,SUV,Van,Bus,Lorry,Truck,Pickup,Minivan,Coupe,Sedan,Hatchback,Wagon',
            'make'                                         => 'required|string|max:255',
            'model'                                        => 'required|string|max:255',
            'year'                                         => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'mileage'                                      => 'required|integer|min:0',
            'registrationType'                             => 'required|in:Registered,Unregistered',
            'price'                                        => 'required|numeric|min:0',
            'dealerId'                                     => 'required|exists:dealers,id',
            'status'                                       => 'required|in:Available,Sold,Reserved',
            // Optional/nullable fields
            'color'                                        => 'nullable|string|max:255',
            'subModel'                                     => 'nullable|string|max:255',
            'countryOfOrigin'                              => 'nullable|string|max:255',
            'fuelType'                                     => 'nullable|in:Gasoline,Diesel,Electric,Hybrid,Plug-in Hybrid',
            'transmissionType'                             => 'nullable|in:Manual,Automatic,CVT,Semi-Automatic',
            'engineSize'                                   => 'nullable|string|max:50',
            'vin'                                          => 'nullable|string|max:17',
            'description'                                  => 'nullable|string',
            // Registered vehicle details
            'registeredDetails'                            => 'required_if:registrationType,Registered|array',
            'registeredDetails.vehicleNumber'              => 'required_if:registrationType,Registered|nullable|string|max:50',
            'registeredDetails.registrationYear'           => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'registeredDetails.serviceRecord'              => 'nullable|string',
            'registeredDetails.ownerName'                  => 'nullable|string|max:150',
            'registeredDetails.ownerContact'               => 'nullable|string|max:30',
            // Unregistered vehicle details
            'unregisteredDetails'                          => 'required_if:registrationType,Unregistered|array',
            'unregisteredDetails.chassisNumber'            => 'required_if:registrationType,Unregistered|nullable|string|max:100',
            'unregisteredDetails.importedDate'             => 'nullable|date|before_or_equal:today',
            'unregisteredDetails.exporterName'             => 'nullable|string|max:150',
            'unregisteredDetails.auctionGrade'             => 'nullable|string|max:10',
            'unregisteredDetails.engineNumber'             => 'nullable|string|max:100',
            'unregisteredDetails.exporterContact'          => 'nullable|string|max:30',
            'unregisteredDetails.registerNotification'     => 'nullable|boolean',
            'unregisteredDetails.registerNotificationDate' => 'nullable|date|after:today',
            'unregisteredDetails.notificationNote'         => 'nullable|string|max:1000',
            'unregisteredDetails.importYear'               => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json(['error' => 'Validation failed', 'messages' => $validator->errors()], 422)
        );
    }
}
