<?php

namespace Modules\Optometry\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OptometryServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        return $this->getCollectionData();
    }
}
