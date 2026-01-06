<?php

namespace Modules\Survey\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class RespondetCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        return $this->collection->transform(function ($row, $key) use ($request) {



            return [
                'id' => $row->id,
                'name' => $row->name,
                'number' => $row->number,
                'email' => $row->email,
                'phone' => $row->phone,
            ];
        });
    }
}
