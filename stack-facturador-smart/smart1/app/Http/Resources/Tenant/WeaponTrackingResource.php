<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class WeaponTrackingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'item_id' => $this->item_id,
            'person_id' => $this->person_id,
            'item_lot_id' => $this->item_lot_id,
            'date_of_issue' => $this->date_of_issue,
            'time_of_issue' => $this->time_of_issue,
            'type' => $this->type,
            'destiny' => $this->destiny,
            'observation' => $this->observation,
            'person' => $this->when($this->person, function() {
                return [
                    'id' => $this->person->id,
                    'name' => $this->person->name,
                    'number' => $this->person->number ?? '',
                ];
            }),
            'item' => $this->when($this->item, function() {
                return [
                    'id' => $this->item->id,
                    'description' => $this->item->description,
                ];
            }),
            'item_lot' => $this->when($this->item_lot, function() {
                return [
                    'id' => $this->item_lot->id,
                    'series' => $this->item_lot->series,
                ];
            }),
        ];
    }
}