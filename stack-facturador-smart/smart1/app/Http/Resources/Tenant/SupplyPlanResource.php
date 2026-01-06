<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class SupplyPlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'type_zone' => $this->type_zone,
            'type_plan' => $this->type_plan,
            'price_c_m' => $this->price_c_m,
            'price_s_m' => $this->price_s_m,
            'price_alc' => $this->price_alc,
            'total' => $this->total,
            'observation' => $this->observation,
            'active' => $this->active,
            'affectation_type_id' => $this->affectation_type_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}