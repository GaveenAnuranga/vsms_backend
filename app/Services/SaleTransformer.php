<?php

namespace App\Services;

class SaleTransformer
{
    /**
     * Transform a Sale model into a response array.
     */
    public function transform($sale, bool $includeBuyer = true): array
    {
        $data = [
            'id'                 => $sale->id,
            'vehicleId'          => $sale->vehicle_id,
            'saleDate'           => $sale->sale_date,
            'salePrice'          => $sale->sale_price,
            'discount'           => $sale->discount,
            'finalAmount'        => $sale->final_amount,
            'paymentMethodId'    => $sale->payment_method_id,
            'invoiceNumber'      => $sale->invoice_number,
            'commission'         => $sale->commission,
            'salespersonName'    => $sale->salesperson_name,
            'taxAmount'          => $sale->tax_amount ?? 0,
            'branch'             => $sale->branch ?? null,
            'documentPath'       => $sale->document_path ?? null,
            'description'        => $sale->description ?? null,
            'paymentDescription' => $sale->payment_description ?? null,
            'reminderDate'       => $sale->reminder_date,
            'reminderNote'       => $sale->reminder_note ?? null,
            'createdAt'          => $sale->created_at,
            'updatedAt'          => $sale->updated_at,
        ];

        if ($sale->vehicle) {
            $v = $sale->vehicle;
            $data['vehicle'] = [
                'id'               => $v->id,
                'stockNumber'      => $v->stock_number,
                'make'             => $v->make,
                'model'            => $v->model,
                'subModel'         => $v->sub_model,
                'year'             => $v->year,
                'color'            => $v->color,
                'fuelType'         => $v->fuel_type,
                'transmissionType' => $v->transmission_type,
                'mileage'          => $v->mileage,
                'price'            => $v->price,
                'status'           => $v->status,
            ];
        }

        if ($sale->paymentMethod) {
            $data['paymentMethod'] = [
                'id'   => $sale->paymentMethod->id,
                'name' => $sale->paymentMethod->name,
            ];
        }

        // Include buyer info if available (buyer is optional)
        if ($sale->buyer) {
            $b = $sale->buyer;
            $data['buyer'] = [
                'id'       => $b->id,
                'name'     => $b->name,
                'nicOrReg' => $b->nic_or_reg,
                'address'  => $b->address,
                'phone'    => $b->phone,
                'email'    => $b->email,
            ];
        } else {
            $data['buyer'] = null;
        }

        return $data;
    }
}
