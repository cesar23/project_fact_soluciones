<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SupplyOutageCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return $this->collection->transform(function($row, $key) {
            $supply = null;
            $person = $row->person;
            if($row->supplyContract){
                $supplyContract = $row->supplyContract;
                $supply = $supplyContract->supply;
                $person = $supplyContract->person;
            }
            return [
                'id' => $row->id,
                'person_name' => $person ? $person->name : '-',
                'supply_name' => $supply ? $supply->cod_route : '-',
                'observation' => $row->observation,
                'date_of_outage' =>$row->date_of_outage ? $row->date_of_outage : '-',
                'type' => $row->type,
                'state' => $row->state,
            ];
        });
    }
}