<?php

namespace App\Http\Resources\System;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ClientPaymentCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        return $this->collection->transform(function($row, $key) {
            return [
                'id' => $row->id,
                'date_of_payment' => $row->date_of_payment->format('d/m/Y'),
                'payment_method_type_description' => $row->payment_method_type->description,
                'card_brand' => $row->card_brand,
                'reference' => $row->reference,
                'payment' => $row->payment, 
                'state' => $row->state,
                'photos' => $row->photos,
                //return the url to download payment_file
                'payment_file' => ($row->payment_file) ? asset('storage'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'payments'.DIRECTORY_SEPARATOR.$row->payment_file) : null,
                
                'state_description' => ($row->state) ? 'Pagado': (($row->date_of_payment >= date('Y-m-d')) ? 'Pendiente':'Vencido'),
            ];
        });
    }
}