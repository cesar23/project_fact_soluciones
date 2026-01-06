<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\ResourceCollection;

class OrderConcreteCollection extends ResourceCollection
{
    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        return $this->collection->transform(function ($row, $key) {
            $document = $row->document ?? $row->sale_note;
            return [
                'id' => $row->id,
                'series' => $row->series,
                'number' => $row->number,
                'date' => $row->date,
                'hour' => $row->hour,
                'customer_name' => $row->customer->name,
                'master_name' => $row->master->name,
                'address' => $row->address,
                'electro' => $row->electro,
                'volume' => $row->volume,
                'mix_kg_cm2' => $row->mix_kg_cm2,
                'type_cement' => $row->type_cement,
                'created_at' => $row->created_at->format('Y-m-d H:i:s'),
                'document_number' => $document->number_full,
            ];
        });
    }
}
