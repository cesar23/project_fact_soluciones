<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class SupplyOutageResource extends JsonResource
{
    public function toArray($request)
    {
        $supply = $this->supplyContract->supply;
        return [
            'id' => $this->id,
            'supply_contract_id' => $this->supply_contract_id,
            'supply_id' => $supply->id,
            'observation' => $this->observation,
            'state' => $this->state,
            'person_id' => $this->person_id,
            'type' => $this->type,
            'date_of_outage' => $this->date_of_outage,
            'supply' => $this->whenLoaded('supplyContract', function() use ($supply) {
                return [
                    'id' => $supply->id,
                    'description' => ($supply->person ? $supply->person->name . ' - ' : '') . 
                    ($supply->cod_route ? $supply->cod_route : '') .
                    ($supply->old_code ? ' (' . $supply->old_code . ')' : '')
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}