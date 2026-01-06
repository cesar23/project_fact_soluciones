<?php

namespace Modules\Optometry\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class OptometryServiceCollection extends ResourceCollection
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
            /** @var OptometryService $row */
            return $row->getCollectionData();
        
        });
    }

}
