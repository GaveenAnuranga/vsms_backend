<?php

namespace App\Services;

class TransferTransformer
{
    /**
     * Transform a Transfer model into a response array.
     */
    public function transform($transfer): array
    {
        $data = [
            'id'                => $transfer->id,
            'tenantId'          => $transfer->tenant_id,
            'vehicleId'         => $transfer->vehicle_id,
            'fromDealerId'      => $transfer->from_dealer_id,
            'toDealerId'        => $transfer->to_dealer_id,
            'transferDate'      => $transfer->transfer_date,
            'transferPrice'     => $transfer->transfer_price,
            'transportCost'     => $transfer->transport_cost,
            'status'            => $transfer->status,
            'responsiblePerson' => $transfer->responsible_person,
            'createdAt'         => $transfer->created_at,
            'updatedAt'         => $transfer->updated_at,
        ];

        if ($transfer->vehicle) {
            $v = $transfer->vehicle;
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

        if ($transfer->fromDealer) {
            $data['fromDealer'] = [
                'id'      => $transfer->fromDealer->id,
                'name'    => $transfer->fromDealer->name,
                'address' => $transfer->fromDealer->address,
            ];
        }

        if ($transfer->toDealer) {
            $data['toDealer'] = [
                'id'      => $transfer->toDealer->id,
                'name'    => $transfer->toDealer->name,
                'address' => $transfer->toDealer->address,
            ];
        }

        return $data;
    }
}
