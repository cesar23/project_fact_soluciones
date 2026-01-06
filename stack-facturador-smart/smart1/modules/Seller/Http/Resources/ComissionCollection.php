<?php

namespace Modules\Seller\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ComissionCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        return $this->collection->transform(function ($row, $key) use($request) {

        

            return [
                'id' => $row->id,
                'percentage' => $row->percentage,
                'margin' => $row->margin,
            ];
        });
    }


    

}
