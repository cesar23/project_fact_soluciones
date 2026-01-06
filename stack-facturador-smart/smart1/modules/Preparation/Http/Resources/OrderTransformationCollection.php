<?php

namespace Modules\Preparation\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class OrderTransformationCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->transform(function($transformation) {
            return [
                'id' => $transformation->id,
                'series' => $transformation->series,
                'number' => $transformation->number,
                'user_name' => $transformation->user ? $transformation->user->name : null,
                'date_of_issue' => $transformation->date_of_issue ? $transformation->date_of_issue->format('d/m/Y') : null,
                'status' => $transformation->status,
                'is_available' => $transformation->status == 'completed',
                'items' => $transformation->items->where('item_type','final_product')->map(function($item) {
                    $item_relation = $item->item;
                    $full_description = $item_relation->internal_id ? "{$item_relation->internal_id} - {$item_relation->description}" : $item_relation->description;
                    return [
                        'id' => $item->id,
                        'item_id' => $item->item_id,
                        'item_description' => $full_description,
                        'quantity' => $item->quantity,
                    ];
                }),
            ];
        });
    }
}