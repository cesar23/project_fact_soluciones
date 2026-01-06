<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SaleNoteToDeleteCollection extends ResourceCollection
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
            
            return [
                'id' => $row->id,
                'date_of_issue' => $row->date_of_issue->format('Y-m-d'),
                'user_name' => $row->user->name,
                'customer_name' => $row->customer->name,
                'customer_number' => $row->customer->number,
                'total' => number_format($row->total,2),            
                'full_number' => $row->number_full,
            ];
        });
    }


    
}
