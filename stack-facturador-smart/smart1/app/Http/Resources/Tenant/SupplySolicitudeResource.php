<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class SupplySolicitudeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'person_id' => $this->person_id,
            'person_name' => $this->person->name ?? null,
            'supply_id' => $this->supply_id,
            'supply_name' => $this->supply->old_code.' | '.$this->supply->cod_route,
            'user_id' => $this->user_id,
            'supply_service_id' => $this->supply_service_id,
            'program_date' => $this->program_date,
            'start_date' => $this->start_date,
            'finish_date' => $this->finish_date,
            'use' => $this->use,
            'active' => $this->active,
            'review' => $this->review,
            'cod_tipo' => $this->cod_tipo,
            'supply_debt_id' => $this->supply_debt_id,
            'observation' => $this->observation,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relaciones incluidas
            'person' => $this->whenLoaded('person'),
            'supply' => $this->whenLoaded('supply'),
            'user' => $this->whenLoaded('user'),
            'supply_service' => $this->whenLoaded('supplyService'),
            'supply_solicitude_items' => $this->whenLoaded('supplySolicitudeItems')
        ];
    }
}