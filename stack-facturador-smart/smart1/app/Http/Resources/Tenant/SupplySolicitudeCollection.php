<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SupplySolicitudeCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        return $this->collection->transform(function($row, $key) {
            return [
                'id' => $row->id,
                'person_name' => $row->person->name,
                'supply' => $row->supply->old_code,
                'user_name' => $row->user ? $row->user->name : '-',
                'service' => $row->supplyService ? $row->supplyService->name : 'RECONEXIÓN',
                'review' => $row->review,

            ];
        });
    }
}