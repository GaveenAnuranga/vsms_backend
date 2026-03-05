<?php

namespace App\Services;

use App\Models\Seller;

class SaleTransformer
{
    /**
     * Transform a Sale model into a response array.
     */
    public function transform($sale, bool $includeBuyer = false): array
    {
        $data = [
            'id'              => $sale->id,
            'vehicleId'       => $sale->vehicle_id,
            'saleDate'        => $sale->sale_date,
            'salePrice'       => $sale->sale_price,
            'discount'        => $sale->discount,
            'finalAmount'     => $sale->final_amount,
            'paymentMethodId' => $sale->payment_method_id,
            'invoiceNumber'   => $sale->invoice_number,
            'commission'      => $sale->commission,
            'salespersonName' => $sale->salesperson_name,
            'createdAt'       => $sale->created_at,
            'updatedAt'       => $sale->updated_at,
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

        if ($includeBuyer && $sale->buyer) {
            $b = $sale->buyer;
            $data['buyer'] = [
                'id'       => $b->id,
                'name'     => $b->name,
                'nicOrReg' => $b->nic_or_reg,
                'address'  => $b->address,
                'phone'    => $b->phone,
                'email'    => $b->email,
            ];
        }

        // Resolve seller from sellers table by salesperson name, or use name as fallback
        $seller = Seller::where('name', $sale->salesperson_name)->first();
        $data['seller'] = $seller
            ? [
                'id'         => $seller->id,
                'name'       => $seller->name,
                'nicOrReg'   => $seller->nic_or_reg,
                'address'    => $seller->address,
                'phone'      => $seller->phone,
                'email'      => $seller->email,
                'sellerType' => $seller->seller_type,
            ]
            : ['name' => $sale->salesperson_name, 'phone' => null];

        return $data;
    }
}
