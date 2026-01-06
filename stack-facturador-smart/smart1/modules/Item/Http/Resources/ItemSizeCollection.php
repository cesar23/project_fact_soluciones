<?php

namespace Modules\Item\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ItemSizeCollection extends ResourceCollection
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



            $status = $row->state;


            return [
                'id' => $row->id,
                'size' => $row->size,
                'item_description' => $row->item->description,
                'stock' => $row->stock,
            ];
        });
    }
}
