<?php

namespace Modules\Dispatch\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransportResource extends JsonResource
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
            'plate_number' => $this->plate_number,
            'auth_plate_primary' => $this->auth_plate_primary,
            'auth_plate_secondary' => $this->auth_plate_secondary,
            'secondary_plate_number' => $this->secondary_plate_number,
            'tuc' => $this->tuc,
            'tuc_secondary' => $this->tuc_secondary,
            'model' => $this->model,
            'brand' => $this->brand,
            'is_default' => $this->is_default,
            'is_active' => $this->is_active,
            'configuration' => $this->configuration,
        ];
    }
}
