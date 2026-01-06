<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class AdvanceResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
            return [
                'id' => $this->id,
                'person_id' => $this->person_id,
                'person' => $this->person->name,
                'date_opening' => $this->date_opening,
                'time_opening' => $this->time_opening,
                'opening' => "{$this->date_opening} {$this->time_opening}",
                'date_closed' => $this->date_closed,
                'time_closed' => $this->time_closed, 
                'closed' => "{$this->date_closed} {$this->time_closed}",
                'beginning_balance' => $this->beginning_balance,
                'final_balance' => $this->final_balance,
                'state_description' => ($this->state) ? 'Aperturada':'Cerrada', 
                'state' => (bool) $this->state,
                'reference_number' => $this->reference_number
            ];

        
    }
}