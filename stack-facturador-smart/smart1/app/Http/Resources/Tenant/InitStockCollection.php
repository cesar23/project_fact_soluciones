<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\ResourceCollection;

class InitStockCollection extends ResourceCollection
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
            //            dd($row);
            return [
                'id' => $row->id,
                'init_date' => $row->init_date->format('Y-m-d'),
                'stock' => $row->stock,
                'item_id' => $row->item_id,
                'warehouse_id' => $row->warehouse_id,
            ];
        });
    }
}
