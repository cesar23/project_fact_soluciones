<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class SupplyContractResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'supply_solicitude_id' => $this->supply_solicitude_id,
            'person_name' => $this->person->name,
            'supply_name' => $this->supply->old_code.' | '.$this->supply->cod_route,
            'person_id' => $this->person_id,
            'supplie_plan_id' => $this->supplie_plan_id,
            'supply_id' => $this->supply_id,
            'path_solicitude' => $this->path_solicitude,
            'supply_service_id' => $this->supply_service_id,
            'address' => $this->address,
            'install_date' => $this->install_date,
            'start_date' => $this->start_date,
            'finish_date' => $this->finish_date,
            'active' => $this->active,
            'observation' => $this->observation,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'path_solicitude_url' => $this->path_solicitude ? asset('storage/solicitudes/'.$this->path_solicitude) : null   
        ];
    }
}