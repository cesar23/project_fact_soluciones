<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SupplyCollection extends ResourceCollection
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
                'code' => $row->code,
                'description' => $row->description,
                'person' => $row->person ? [
                    'id' => $row->person->id,
                    'name' => $row->person->name,
                    'number' => $row->person->number
                ] : null,
                'supply_via' => $row->supplyVia ? [
                    'id' => $row->supplyVia->id,
                    'name' =>  $row->supplyVia->supplyTypeVia->short . ' ' . $row->supplyVia->name
                ] : null,
                'sector' => $row->sector ? [
                    'id' => $row->sector->id,
                    'name' => $row->sector->name
                ] : null,
                'supply_state' => $row->supplyState ? [
                    'id' => $row->supplyState->id,
                    'name' => $row->supplyState->name
                ] : null,
                'old_code' => $row->old_code,
                'cod_route' => $row->cod_route,
                'zone_type' => $row->zone_type,
                'mz' => $row->mz,
                'lte' => $row->lte,
                'und' => $row->und,
                'number' => $row->number,
                'meter' => $row->meter,
                'meter_code' => $row->meter_code,
                'sewerage' => $row->sewerage,
                'active' => $row->active,
                'observation' => $row->observation,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
                'address' => $row->getAddressFullAttribute()
            ];
        });
    }
}