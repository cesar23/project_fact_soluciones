<?php

namespace Modules\Item\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Carbon\Carbon;
use Modules\Item\Models\WebPlatform;

class ItemHistorySalesCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request) {
        return $this->collection->transform(function($row, $key) {
            $web_platform = null;
            if($row->web_platform_id) {
                $web_platform = WebPlatform::find($row->web_platform_id)->name;
            }
            return [
                'id' => $row->id,
                'is_set' => (bool) $row->is_set,
                'web_platform' => $web_platform,
                'number_full' => "{$row->series}-{$row->number}",
                'series' => $row->series,
                'number' => $row->number,
                'state_type_id' => $row->state_type_id,
                'state_type_description' => $row->state_type_description,
                'date_of_issue' => $row->date_of_issue,
                'price' => $row->price, 
                'total' => $row->total, 
                'quantity' => $row->quantity, 
                'customer_name' => $row->customer_name, 
                'customer_number' => $row->customer_number, 
            ];
        });
    }
}
