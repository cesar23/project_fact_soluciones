<?php

namespace Modules\Dispatch\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TransportCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        return $this->collection->transform(function ($row, $key) {
            return [
                'id' => $row->id,
                'tuc' => $row->tuc,
                'tuc_secondary' => $row->tuc_secondary,
                'plate_number' => $row->plate_number,
                'secondary_plate_number' => $row->secondary_plate_number,
                'auth_plate_primary' => $row->auth_plate_primary,
                'auth_plate_secondary' => $row->auth_plate_secondary,
                'model' => $row->model,
                'brand' => $row->brand,
                'is_default' => $row->is_default ? 'SI' : '',
                'configuration' => $row->configuration,
                'is_active' => $row->is_active,
                'created_at' => $row->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $row->updated_at->format('Y-m-d H:i:s'),
            ];
        });
    }
}
