<?php

namespace Modules\Preparation\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class RegisterInputsMovementCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->transform(function($movement) {
            return [
                'id' => $movement->id,
                'date_of_issue' => $movement->date_of_issue ? $movement->date_of_issue->format('d/m/Y') : null,
                'person' => $movement->person ? $movement->person->name : null,
                'item' => $movement->item ? $movement->item->description : null,
                'item_internal_id' => $movement->item ? $movement->item->internal_id : null,
                'quantity' => (float) $movement->quantity,
                'warehouse' => $movement->warehouse ? $movement->warehouse->description : null,
                'lot_code' => $movement->lot_code,
                'observation' => $movement->observation,
                'created_at' => $movement->created_at ? $movement->created_at->format('d/m/Y H:i') : null,
            ];
        });
    }
}