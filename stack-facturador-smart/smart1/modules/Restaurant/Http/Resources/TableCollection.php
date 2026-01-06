<?php

namespace Modules\Restaurant\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Restaurant\Models\Orden;

class TableCollection extends ResourceCollection
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
                'id'                => $row->id,
                'number'            => $row->number,
                'area_description'  => optional($row->area)->description,
                'status_table_id'   => $row->status_table->id,
                'status_table'      => $row->status_table->description,
                'area_id'           => $row->area_id,
                'active'            => (bool) $row->active,
            ];
        });
    }
}
