<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SupplySolicitudeNoContractCollection extends ResourceCollection
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
                'supply_solicitude_id' => $row->id,
                'person_name' => $row->person->name,
                'service_name' => '-',
                'supply_name' => $row->supply->old_code .' - '. $row->supply->cod_route,
                'start_date' => '-',
                'tariff' => '-',
                'cost' => '-',
                'state' => null,
                'active' => null,

            ];
        });
    }
}