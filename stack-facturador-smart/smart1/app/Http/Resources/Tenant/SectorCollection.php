<?php

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\Sector;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SectorCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->transform(function(Sector $row, $key) {

            return [
                'id' => $row->id,
                'name' => $row->name,
                'code' => $row->code,
            ];
        });

    }

}
