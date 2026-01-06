<?php

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\Sector;
use App\Models\Tenant\SupplyVia;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SupplyViaCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->transform(function(SupplyVia $row, $key) {

            return [
                'id' => $row->id,
                'name' => $row->name,
                'code' => $row->code,
                'obsevation' => $row->obsevation,
                'supply_type_via' => $row->supplyTypeVia ? [
                    'id' => $row->supplyTypeVia->id,
                    'name' => $row->supplyTypeVia->description
                ] : null,
                'sector' => $row->sector ? [
                    'id' => $row->sector->id,
                    'name' => $row->sector->name
                ] : null,
            ];
        });

    }

}
