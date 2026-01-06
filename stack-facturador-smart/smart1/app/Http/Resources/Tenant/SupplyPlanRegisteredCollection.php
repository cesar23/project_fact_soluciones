<?php

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\Sector;
use App\Models\Tenant\SupplyPlanRegistered;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SupplyPlanRegisteredCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->transform(function(SupplyPlanRegistered $row, $key) {

            return [
                'id' => $row->id,
                'supply_id' => $row->supply_id,
                'user_id' => $row->user_id,
                'contract_number' => $row->contract_number,
                'observation' => $row->observation,
                'active' => $row->active,
                'supply' => $row->supply,
                'user' => $row->user,
                'supply_plan' => $row->supplyPlan,
                'address' => $row->getAddressFullAttribute(),
            ];
        });

    }

}
