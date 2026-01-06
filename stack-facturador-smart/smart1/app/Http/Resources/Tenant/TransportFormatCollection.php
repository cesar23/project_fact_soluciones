<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TransportFormatCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->transform(function($row, $key) {
            return [
                'id' => $row->id,
                'date_of_issue' => $row->date_of_issue,
                'created_at' => $row->created_at->format('Y-m-d H:i:s'),
                'transport_format_items' => $row->transportFormatItems->map(function($item) {
                    return [
                        'id' => $item->id,
                        'sale_note' => [
                            'id' => $item->saleNote->id,
                            'series' => $item->saleNote->series,
                            'number' => $item->saleNote->number,
                            'customer_name' => $item->saleNote->customer->name,
                            'total' => $item->saleNote->total
                        ]
                    ];
                })
            ];
        });
    }
} 