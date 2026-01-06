<?php

namespace Modules\Preparation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class RegisterInputsMovementResource
 *
 * @package Modules\Preparation\Http\Resources
 * @mixin JsonResource
 */
class RegisterInputsMovementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'date_of_issue' => $this->date_of_issue ? $this->date_of_issue->format('d/m/Y') : null,
            'date_of_issue_format' => $this->date_of_issue ? $this->date_of_issue->format('Y-m-d') : null,
            'person_id' => $this->person_id,
            'person' => $this->whenLoaded('person', function () {
                return [
                    'id' => $this->person->id,
                    'name' => $this->person->name,
                ];
            }),
            'item_id' => $this->item_id,
            'item' => $this->whenLoaded('item', function () {
                return [
                    'id' => $this->item->id,
                    'description' => $this->item->description,
                    'internal_id' => $this->item->internal_id ?? null,
                ];
            }),
            'quantity' => (float) $this->quantity,
            'warehouse_id' => $this->warehouse_id,
            'warehouse' => $this->whenLoaded('warehouse', function () {
                return [
                    'id' => $this->warehouse->id,
                    'description' => $this->warehouse->description,
                ];
            }),
            'lot_code' => $this->lot_code,
            'observation' => $this->observation,
            'created_at' => $this->created_at ? $this->created_at->format('d/m/Y H:i') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('d/m/Y H:i') : null,
        ];
    }
}