<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\Tenant\DispatchOrderRoute;

class DispatchOrderRouteCollection extends ResourceCollection
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
            /** @var DispatchOrderRoute $row */
            return $row->getCollectionData();
           
        });
    }

}
