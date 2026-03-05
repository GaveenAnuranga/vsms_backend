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
            'stockNumber'                                  => 'required|string|digits:5|unique:vehicles,stock_number',
            'vehicleType'                                  => 'required|in:Car,SUV,Van,Bus,Lorry,Truck,Pickup,Minivan,Coupe,Sedan,Hatchback,Wagon',
            'make'                                         => 'required|string|max:255',
            'model'                                        => 'required|string|max:255',
            'subModel'                                     => 'nullable|string|max:255',
            'year'                                         => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'color'                                        => 'required|string|max:255',
            'countryOfOrigin'                              => 'required|string|max:255',
            'fuelType'                                     => 'required|in:Gasoline,Diesel,Electric,Hybrid,Plug-in Hybrid',
            'mileage'                                      => 'required|integer|min:0',
            'transmissionType'                             => 'required|in:Manual,Automatic,CVT,Semi-Automatic',
            'engineSize'                                   => 'nullable|string|max:50',
            'vin'                                          => 'nullable|string|max:17',
            'registrationType'                             => 'required|in:Registered,Unregistered',
            'price'                                        => 'required|numeric|min:0',
            'dealerId'                                     => 'required|exists:dealers,id',
            'status'                                       => 'required|in:Available,Sold,Transferred,Reserved',
            'description'                                  => 'nullable|string',
            'registeredDetails'                            => 'required_if:registrationType,Registered|array',
            'registeredDetails.vehicleNumber'              => 'required_if:registrationType,Registered|nullable|string|max:50',
            'registeredDetails.registrationYear'           => 'required_if:registrationType,Registered|nullable|integer|min:1900|max:' . (date('Y') + 1),
            'registeredDetails.ownerName'                  => 'required_if:registrationType,Registered|nullable|string|max:150',
            'registeredDetails.ownerContact'               => 'required_if:registrationType,Registered|nullable|string|max:30',
            'registeredDetails.serviceRecord'              => 'nullable|string',
            'unregisteredDetails'                          => 'required_if:registrationType,Unregistered|array',
            'unregisteredDetails.chassisNumber'            => 'required_if:registrationType,Unregistered|nullable|string|max:100',
            'unregisteredDetails.engineNumber'             => 'required_if:registrationType,Unregistered|nullable|string|max:100',
            'unregisteredDetails.importerName'             => 'required_if:registrationType,Unregistered|nullable|string|max:150',
            'unregisteredDetails.importerContact'          => 'required_if:registrationType,Unregistered|nullable|string|max:30',
            'unregisteredDetails.registerNotification'     => 'nullable|boolean',
            'unregisteredDetails.registerNotificationDate' => 'nullable|date|after:today',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json(['error' => 'Validation failed', 'messages' => $validator->errors()], 422)
        );
    }
}
