<?php

namespace Modules\Item\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ItemLotCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    #dat
    public function toArray($request)
    {
        $now = Carbon::now()->startOfDay();
        
        return $this->collection->transform(function ($row, $key) use ($now) {

            $status = '';

            if ($row->has_sale) {
                $status = 'SI';
            } else {
                $status = 'NO';
            }


            return [
                'id' => $row->id,
                'warehouse_description' => $row->warehouse ? $row->warehouse->description : null,
                'series' => $row->series,
                'item_description' => $row->item->description,
                'date' => $row->date,
                'date_incoming' => $row->date_incoming,
                'state' => $row->state,
                'item_id' => $row->item_id,
                'warehouse_id' => $row->warehouse_id,
                'date_due' => $now->diffInDays($row->date, false),
                'status' => $status,
                'has_sale' => (bool)$row->has_sale,
                // 'lot_code' => ($row->item_loteable_type) ? (isset($row->item_loteable->lot_code) ? $row->item_loteable->lot_code:null):null
            ];
        });
    }
}
