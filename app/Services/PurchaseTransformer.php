<?php

namespace App\Services;

class PurchaseTransformer
{
    /**
     * Transform a CarPurchase model (or stdClass from raw query) into a response array.
     *
     * @param mixed $purchase
     */
    public function transform($purchase): array
    {
        $data = [
            'id'              => $purchase->id,
            'vehicleId'       => $purchase->vehicle_id,
            'purchaseDate'    => $purchase->purchase_date,
            'purchasePrice'   => $purchase->purchase_price,
            'paymentMethodId' => $purchase->payment_method_id,
            'invoiceNumber'   => $purchase->invoice_number,
            'taxAmount'       => $purchase->tax_amount,
            'branch'          => $purchase->branch,
            'documentPath'    => $purchase->document_path,
            'description'     => $purchase->description,
            'createdAt'       => $purchase->created_at,
            'updatedAt'       => $purchase->updated_at,
        ];

        if (!empty($purchase->vehicle)) {
            $v = $purchase->vehicle;
            $data['vehicle'] = [
                'id'          => $v->id,
                'stockNumber' => $v->stock_number,
                'make'        => $v->make,
                'model'       => $v->model,
                'year'        => $v->year,
                'color'       => $v->color,
                'price'       => $v->price,
            ];
        }

        if (!empty($purchase->paymentMethod)) {
            $data['paymentMethod'] = [
                'id'   => $purchase->paymentMethod->id,
                'name' => $purchase->paymentMethod->name,
            ];
        }

        $data['sellers'] = collect($purchase->sellers ?? [])->map(fn ($s) => [
            'id'         => $s->id,
            'name'       => $s->name,
            'nicOrReg'   => $s->nic_or_reg,
            'address'    => $s->address,
            'phone'      => $s->phone,
            'email'      => $s->email,
            'sellerType' => $s->seller_type,
        ])->values();

        return $data;
    }
}
