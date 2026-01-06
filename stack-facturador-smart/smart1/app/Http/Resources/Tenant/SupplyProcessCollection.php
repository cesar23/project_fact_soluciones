<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SupplyProcessCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return $this->collection->transform(function($row, $key) {
            $supply_name = $row->supply ? $row->supply->cod_route . ' - ' . $row->supply->old_code : '-';
            return [
                'id' => $row->id,
                'record' => $row->record,
                'supply_name' => $supply_name,
                'person_number' => $row->person ? $row->person->number : '-',
                'person_name' => $row->person ? $row->person->name : '-',
                'document' => $row->document,
                'receive_date' => $row->receive_date->format('Y-m-d'),
                'state' => $row->state,
            ];
        });
    }
}