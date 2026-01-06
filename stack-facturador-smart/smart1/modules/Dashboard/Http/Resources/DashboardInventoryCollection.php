<?php

namespace Modules\Dashboard\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DashboardInventoryCollection extends ResourceCollection
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
            $date = $row instanceof \App\Models\Tenant\ItemWarehouse ? $row->item->date_of_due->format('Y-m-d') : $row->date_of_due;
            return [
                'id' => $row->id,
                'product' => $row->item->description,
                'stock' => number_format($row->stock, 2, ".", ""),
                'lot' => $row->item->lot_code,
                'price' => number_format($row->item->sale_unit_price, 2),
                'date' => $date,
                'warehouse' => $row->warehouse->description,
            ];
        });
    }
}