<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SupplyPlanCollection extends ResourceCollection
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
                'description' => $row->description,
                'total' => $row->total,
                'type_zone' => $row->type_zone,
                'type_plan' => $row->type_plan,
                'price_c_m' => $row->price_c_m,
                'price_s_m' => $row->price_s_m,
                'price_alc' => $row->price_alc,
                'observation' => $row->observation,
                'active' => $row->active,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at
            ];
        });
    }
}