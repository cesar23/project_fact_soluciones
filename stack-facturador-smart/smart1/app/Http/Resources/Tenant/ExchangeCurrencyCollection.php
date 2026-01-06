<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ExchangeCurrencyCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        return $this->collection->transform(function ($row, $key) {
            // $currency = 
            return [
                'id' => $row->id,
                'currency' => $row->currency->description,
                'currency_id' => $row->currency_id,
                'date' => $row->date,
                'sale' => $row->sale,
                'purchase' => $row->purchase,
            ];
        });
    }
}
