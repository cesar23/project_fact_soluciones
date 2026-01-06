<?php

namespace Modules\Item\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class HistorySetReplaceCollection extends ResourceCollection
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
                'internal_id_item' => $row->internal_id_item,
                'description_item' => $row->description_item,
                'item_id' => $row->item_id,
                'internal_id_replace' => $row->internal_id_replace,
                'description_replace' => $row->description_replace,
                'full_description_replace' => $row->internal_id_replace . ' - ' . $row->description_replace,
                'full_description' => $row->internal_id_item . ' - ' . $row->description_item,
                'replace_id' => $row->replace_id,
                'quantity' => $row->quantity,
                'date' => $row->created_at->format('Y-m-d'),
                'user_name' => $row->user->name,
            ];
        });
    }
}
