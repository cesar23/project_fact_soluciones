<?php

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\Sector;
use App\Models\Tenant\SupplyVia;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SupplyTypeViaCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->transform(function(SupplyVia $row, $key) {

            return [
                'id' => $row->id,
                'code' => $row->code,
                'description' => $row->description,
                'short' => $row->short,
            ];
        });

    }

}
