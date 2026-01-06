<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AdvanceCollection extends ResourceCollection
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
                'person_id' => $row->person_id,
                'person' => $row->person->name,
                'date_opening' => $row->date_opening,
                'time_opening' => $row->time_opening,
                'opening' => "{$row->date_opening} {$row->time_opening}",
                'date_closed' => $row->date_closed,
                'time_closed' => $row->time_closed, 
                'closed' => "{$row->date_closed} {$row->time_closed}",
                'beginning_balance' => $row->beginning_balance,
                'final_balance' => $row->final_balance,
                'state' => (bool) $row->state, 
                'reference_number' => $row->reference_number === 0 ?  $row->user->name:$row->reference_number,
            ];
        });
    }
}