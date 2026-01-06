<?php

namespace Modules\Dashboard\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DashboardInventoryLotsCollection extends ResourceCollection
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
                'product' => $row->item->description,
                'series' => $row->series,
                'price' => number_format($row->item->sale_unit_price, 2),
                'date' => $row->date,
                'date_incoming' => $row->date_incoming,
                'warehouse' => $row->warehouse->description,
            ];
        });
    }
}