<?php

namespace App\Services;

use App\Models\PaymentMethod;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CarPurchaseService
{
    /**
     * Handle document upload from request. Returns storage path or null.
     */
    public function handleDocumentUpload(Request $request, ?string $existingPath = null): ?string
    {
        if (!$request->hasFile('document')) {
            return $existingPath;
        }

        if ($existingPath) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $existingPath));
        }

        try {
            $path = $request->file('document')->store('payment_docs', 'public');
            return $path ? '/storage/' . $path : null;
        } catch (\Exception $e) {
            Log::warning('Document upload failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Persist a new car sale. Stores buyer info in buyers table and
     * the sale record in the sales table. Returns a formatted response array.
     *
     * Note: The "seller_*" fields from the form represent the BUYER
     * (the customer purchasing the car from the dealership).
     */
    public function storePurchase(Request $request, int $tenantId, ?string $documentPath): array
    {
        // Create buyer record from the form's "seller" fields (the customer buying the car)
        $buyerId = DB::table('buyers')->insertGetId([
            'tenant_id'  => $tenantId,
            'name'       => $request->input('seller_name', 'Walk-in Buyer'),
            'nic_or_reg' => $request->input('seller_nic', ''),
            'address'    => $request->input('seller_address', ''),
            'phone'      => $request->input('seller_phone', ''),
            'email'      => $request->input('seller_email', ''),
        ]);

        // Persist into sales table with all available fields
        $saleId = DB::table('sales')->insertGetId([
            'tenant_id'         => $tenantId,
            'vehicle_id'        => $request->vehicle_id,
            'buyer_id'          => $buyerId,
            'sale_date'         => $request->purchase_date,
            'sale_price'        => $request->purchase_price,
            'discount'          => $request->input('discount', 0),
            'final_amount'      => $request->purchase_price - ($request->input('discount', 0)),
            'payment_method_id' => $request->payment_method_id,
            'invoice_number'    => $request->invoice_number,
            'commission'        => $request->input('commission', 0),
            'salesperson_name'  => $request->input('salesperson_name', ''),
            'tax_amount'        => $request->input('tax_amount', 0),
            'branch'            => $request->branch ?? null,
            'document_path'     => $documentPath,
            'tax_details'       => $request->tax_details ?? null,
            'reminder_date'     => ($request->input('reminder_active') && $request->input('reminder_date'))
                                    ? $request->input('reminder_date')
                                    : null,
            'reminder_note'     => ($request->input('reminder_active') && $request->input('reminder_description'))
                                    ? $request->input('reminder_description')
                                    : null,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // Mark vehicle as Sold if still Available
        $vehicle = Vehicle::find($request->vehicle_id);
        if ($vehicle && $vehicle->status === 'Available') {
            $vehicle->update(['status' => 'Sold']);
        }

        $sale          = DB::table('sales')->where('id', $saleId)->first();
        $buyer         = DB::table('buyers')->where('id', $buyerId)->first();
        $paymentMethod = PaymentMethod::find($sale->payment_method_id);

        return [
            'id'              => $sale->id,
            'vehicleId'       => $sale->vehicle_id,
            'purchaseDate'    => $sale->sale_date,
            'purchasePrice'   => $sale->sale_price,
            'paymentMethodId' => $sale->payment_method_id,
            'invoiceNumber'   => $sale->invoice_number,
            'taxAmount'       => $sale->tax_amount,
            'branch'          => $sale->branch,
            'documentPath'    => $documentPath,
            'taxDetails'      => $sale->tax_details,
            'reminderDate'    => $sale->reminder_date,
            'reminderNote'    => $sale->reminder_note,
            'createdAt'       => $sale->created_at,
            'updatedAt'       => $sale->updated_at,
            'paymentMethod'   => $paymentMethod
                ? ['id' => $paymentMethod->id, 'name' => $paymentMethod->name]
                : null,
            'buyer' => $buyer
                ? ['id' => $buyer->id, 'name' => $buyer->name, 'phone' => $buyer->phone, 'nicOrReg' => $buyer->nic_or_reg]
                : null,
        ];
    }
}
