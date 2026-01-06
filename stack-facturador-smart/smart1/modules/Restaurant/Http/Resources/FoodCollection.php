<?php

namespace Modules\Restaurant\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Storage;

class FoodCollection extends ResourceCollection
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
                'id'                => $row->id,
                'description'            => $row->description,
                'code'        => $row->code,
                'category'     => $row->category,
                'image'          => $row->image,
                'item' => $row->item,
                'active' => $row->active,
            ];
        });
    }
}
