<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\ResourceCollection;

class WeaponTrackingCollection extends ResourceCollection
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
                'item_id' => $row->item_id,
                'person_id' => $row->person_id,
                'item_lot_id' => $row->item_lot_id,
                'date_of_issue' => $row->date_of_issue,
                'time_of_issue' => $row->time_of_issue,
                'type' => $row->type,
                'destiny' => $row->destiny,
                'observation' => $row->observation,
                'is_last_record' => $row->is_last_record ?? false,
                'current_status' => $row->current_status ?? null,
                'person' => $row->person ? [
                    'id' => $row->person->id,
                    'name' => $row->person->name,
                    'number' => $row->person->number ?? '',
                ] : null,
                'item' => $row->item ? [
                    'id' => $row->item->id,
                    'description' => $row->item->description,
                    'internal_id' => $row->item->internal_id ?? null,
                    'brand' => $row->item->brand ? [
                        'id' => $row->item->brand->id,
                        'name' => $row->item->brand->name,
                    ] : null,
                ] : null,
                'item_lot' => $row->item_lot ? [
                    'id' => $row->item_lot->id,
                    'series' => $row->item_lot->series,
                ] : null,
            ];
        });
    }
}