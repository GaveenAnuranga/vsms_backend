<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicle_id'         => 'required|exists:vehicles,id',
            'from_dealer_id'     => 'nullable|exists:dealers,id',
            'to_dealer_id'       => 'required|exists:dealers,id',
            'transfer_date'      => 'required|date',
            'transfer_price'     => 'nullable|numeric|min:0',
            'transport_cost'     => 'nullable|numeric|min:0',
            'status'             => 'nullable|in:pending,completed',
            'responsible_person' => 'nullable|string|max:255',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json(['error' => 'Validation failed', 'messages' => $validator->errors()], 422)
        );
    }
}
