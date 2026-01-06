<?php

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\SupplyContract;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $has_active_contract = SupplyContract::where('supply_id', $this->id)->where('active', true)->exists();
        return [
            'id' => $this->id,
            'code' => $this->code,
            'description' => $this->description,
            'person_id' => $this->person_id,
            'supply_via_id' => $this->supply_via_id,
            'sector_id' => $this->sector_id,
            'optional_address' => $this->optional_address,
            'date_start' => $this->date_start,
            'user_id' => $this->user_id,
            'state_supply_id' => $this->state_supply_id,
            'old_code' => $this->old_code,
            'cod_route' => $this->cod_route,
            'zone_type' => $this->zone_type,
            'mz' => $this->mz,
            'lte' => $this->lte,
            'und' => $this->und,
            'number' => $this->number,
            'meter' => $this->meter,
            'has_active_contract' => $has_active_contract,
            'meter_code' => $this->meter_code,
            'sewerage' => $this->sewerage,
            'active' => $this->active,
            'observation' => $this->observation,
            'person' => $this->whenLoaded('person', function() {
                return [
                    'id' => $this->person->id,
                    'name' => $this->person->name,
                    'address' => $this->person->address
                ];
            }),
            'supply_via' => $this->whenLoaded('supplyVia', function() {
                return [
                    'id' => $this->supplyVia->id,
                    'name' => $this->supplyVia->name
                ];
            }),
            'sector' => $this->whenLoaded('sector', function() {
                return [
                    'id' => $this->sector->id,
                    'name' => $this->sector->name
                ];
            }),
            'supply_state' => $this->whenLoaded('supplyState', function() {
                return [
                    'id' => $this->supplyState->id,
                    'description' => $this->supplyState->description
                ];
            }),
            'user' => $this->whenLoaded('user', function() {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name
                ];
            }),
        ];
    }
}