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
            'vehicle_id'        => 'required|exists:vehicles,id',
            'purchase_date'     => 'required|date',
            'purchase_price'    => 'required|numeric|min:0',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'invoice_number'    => 'required|string|max:100',
            'tax_amount'        => 'nullable|numeric|min:0',
            'branch'            => 'nullable|string|max:150',
            'description'       => 'nullable|string',
            'document'          => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'seller_name'       => 'required|string|max:150',
            'seller_address'    => 'required|string|max:255',
            'seller_phone'      => 'required|string|max:20',
            'seller_email'      => 'nullable|email|max:150',
            'seller_nic'        => 'nullable|string|max:100',
            'seller_type'       => 'nullable|in:individual,dealer,auction',
            'salesperson_name'  => 'required|string|max:150',
            'discount'          => 'nullable|numeric|min:0',
            'commission'        => 'nullable|numeric|min:0',
            'reminder_active'   => 'nullable|boolean',
            'reminder_date'     => 'nullable|date|after:today',
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
