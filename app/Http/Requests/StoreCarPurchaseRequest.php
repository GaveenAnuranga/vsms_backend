<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCarPurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicle_id'          => 'required|exists:vehicles,id',
            'purchase_date'       => 'required|date',
            'branch'              => 'nullable|string|max:150',
            'purchase_price'      => 'required|numeric|min:0',
            'salesperson_name'    => 'nullable|string|max:150',
            'payment_method_id'   => 'nullable|exists:payment_methods,id',
            'invoice_number'      => 'nullable|string|max:100',
            'seller_name'         => 'nullable|string|max:150',
            'seller_address'      => 'nullable|string|max:255',
            'seller_phone'        => 'nullable|string|max:30',
            'payment_description' => 'nullable|string',
            'buyer_contact'       => 'nullable|string|max:20',
            'description'         => 'nullable|string',
            'reminder_active'     => 'nullable|boolean',
            'reminder_date'       => 'nullable|date|after:today',
            'reminder_description' => 'nullable|string|max:1000',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json(['error' => 'Validation failed', 'messages' => $validator->errors()], 422)
        );
    }
}
