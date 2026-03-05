<?php

namespace App\Services;

use App\Models\PaymentMethod;
use App\Models\Seller;
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
     * Persist a new purchase. Stores records in sales + buyers + sellers tables
     * (existing schema design). Returns the formatted response array.
     */
    public function storePurchase(Request $request, int $tenantId, ?string $documentPath): array
    {
        // Create buyer record
        $buyerId = DB::table('buyers')->insertGetId([
            'tenant_id'  => $tenantId,
            'name'       => $request->input('buyer_name', $request->input('seller_name', 'Walk-in Buyer')),
            'nic_or_reg' => $request->input('buyer_nic', $request->input('seller_nic', '')),
            'address'    => $request->input('buyer_address', $request->input('seller_address', '')),
            'phone'      => $request->input('buyer_phone', $request->input('seller_phone', '')),
            'email'      => $request->input('buyer_email', ''),
        ]);

        // Persist into sales table
        $saleId = DB::table('sales')->insertGetId([
            'tenant_id'         => $tenantId,
            'vehicle_id'        => $request->vehicle_id,
            'buyer_id'          => $buyerId,
            'sale_date'         => $request->purchase_date,
            'sale_price'        => $request->purchase_price,
            'discount'          => 0,
            'final_amount'      => $request->purchase_price,
            'payment_method_id' => $request->payment_method_id,
            'invoice_number'    => $request->invoice_number,
            'commission'        => 0,
            'salesperson_name'  => $request->seller_name,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // Upsert seller record
        $seller = Seller::updateOrCreate(
            ['tenant_id' => $tenantId, 'phone' => $request->seller_phone],
            [
                'name'        => $request->seller_name,
                'nic_or_reg'  => $request->seller_nic ?? null,
                'address'     => $request->seller_address,
                'email'       => $request->seller_email ?? null,
                'seller_type' => $request->seller_type ?? 'individual',
            ]
        );

        // Mark vehicle as Sold if still Available
        $vehicle = Vehicle::find($request->vehicle_id);
        if ($vehicle && $vehicle->status === 'Available') {
            $vehicle->update(['status' => 'Sold']);
        }

        $sale          = DB::table('sales')->where('id', $saleId)->first();
        $paymentMethod = PaymentMethod::find($sale->payment_method_id);

        return [
            'id'              => $sale->id,
            'vehicleId'       => $sale->vehicle_id,
            'purchaseDate'    => $sale->sale_date,
            'purchasePrice'   => $sale->sale_price,
            'paymentMethodId' => $sale->payment_method_id,
            'invoiceNumber'   => $sale->invoice_number,
            'taxAmount'       => 0,
            'branch'          => $request->branch ?? null,
            'documentPath'    => $documentPath,
            'taxDetails'      => $request->tax_details ?? null,
            'createdAt'       => $sale->created_at,
            'updatedAt'       => $sale->updated_at,
            'paymentMethod'   => $paymentMethod
                ? ['id' => $paymentMethod->id, 'name' => $paymentMethod->name]
                : null,
            'seller' => ['id' => $seller->id, 'name' => $seller->name, 'phone' => $seller->phone],
        ];
    }
}
