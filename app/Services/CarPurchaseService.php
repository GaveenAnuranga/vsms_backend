<?php

namespace App\Services;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CarPurchaseService
{
    /**
     * Persist a new car sale (simplified form).
     * Creates a minimal buyer record when buyer contact is provided.
     */
    public function storePurchase(Request $request, int $tenantId, ?string $documentPath): array
    {
        // Create a buyer record only if a contact number was given
        $buyerId = null;
        $buyerContact = $request->input('buyer_contact');
        if ($buyerContact) {
            $buyerId = DB::table('buyers')->insertGetId([
                'tenant_id'  => $tenantId,
                'name'       => 'Walk-in Buyer',
                'nic_or_reg' => null,
                'address'    => null,
                'phone'      => $buyerContact,
                'email'      => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Persist into sales table with simplified fields
        $saleId = DB::table('sales')->insertGetId([
            'tenant_id'           => $tenantId,
            'vehicle_id'          => $request->vehicle_id,
            'buyer_id'            => $buyerId,
            'sale_date'           => $request->purchase_date,
            'sale_price'          => $request->purchase_price,
            'discount'            => 0,
            'final_amount'        => $request->purchase_price,
            'payment_method_id'   => $request->input('payment_method_id'),
            'invoice_number'      => $request->input('invoice_number'),
            'commission'          => 0,
            'salesperson_name'    => $request->input('salesperson_name', ''),
            'tax_amount'          => 0,
            'branch'              => $request->branch ?? null,
            'document_path'       => null,
            'description'         => $request->description ?? null,
            'payment_description' => $request->input('payment_description') ?? null,
            'reminder_date'       => ($request->input('reminder_active') && $request->input('reminder_date'))
                                      ? $request->input('reminder_date')
                                      : null,
            'reminder_note'       => ($request->input('reminder_active') && $request->input('reminder_description'))
                                      ? $request->input('reminder_description')
                                      : null,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        // Mark vehicle as Sold if still Available
        $vehicle = Vehicle::find($request->vehicle_id);
        if ($vehicle && $vehicle->status === 'Available') {
            $vehicle->update(['status' => 'Sold']);
        }

        $sale  = DB::table('sales')->where('id', $saleId)->first();
        $buyer = $buyerId ? DB::table('buyers')->where('id', $buyerId)->first() : null;

        return [
            'id'                 => $sale->id,
            'vehicleId'          => $sale->vehicle_id,
            'saleDate'           => $sale->sale_date,
            'salePrice'          => $sale->sale_price,
            'salespersonName'    => $sale->salesperson_name,
            'branch'             => $sale->branch,
            'paymentDescription' => $sale->payment_description,
            'description'        => $sale->description,
            'reminderDate'       => $sale->reminder_date,
            'reminderNote'       => $sale->reminder_note,
            'createdAt'          => $sale->created_at,
            'updatedAt'          => $sale->updated_at,
            'buyer'              => $buyer
                ? ['id' => $buyer->id, 'phone' => $buyer->phone]
                : null,
        ];
    }
}
