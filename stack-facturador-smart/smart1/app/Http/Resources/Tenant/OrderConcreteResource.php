<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderConcreteResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'series' => $this->series,
            'number' => $this->number,
            'date' => $this->date,
            'hour' => $this->hour,
            'customer_name' => $this->customer->name,
            'master_name' => $this->master->name,
            'address' => $this->address,
            'electro' => $this->electro,
            'volume' => $this->volume,
            'mix_kg_cm2' => $this->mix_kg_cm2,
            'type_cement' => $this->type_cement,
            'created_at' => $this->created_at->format('Y-m-d H:i:s')
        ];
    }
} 