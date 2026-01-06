<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SupplyContractCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return $this->collection->transform(function($row, $key) {
            return [
                'id' => $row->id,
                'supply_id' => $row->supply_id,
                'supply_solicitude_id' => $row->supply_solicitude_id,
                'person_name' => $row->person->name. ' - ' . $row->person->number,
                'service_name' => $row->supplyService->name,
                'supply_name' => $row->supply->old_code,
                'start_date' => $row->start_date->format('Y-m-d'),
                'tariff' => $row->suppliePlan->description,
                'cost' => $row->suppliePlan->total,
                'state' => $row->active ? 'Activo' : 'Inactivo',
                'active' => $row->active,
                
            ];
        });
    }
}