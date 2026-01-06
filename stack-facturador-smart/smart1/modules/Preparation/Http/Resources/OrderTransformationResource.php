<?php

namespace Modules\Preparation\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class OrderTransformationResource
 *
 * @package Modules\Preparation\Http\Resources
 * @mixin JsonResource
 */
class OrderTransformationResource extends JsonResource
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
            'series' => $this->series,
            'number' => $this->number,
            'date_of_issue' => $this->date_of_issue ? $this->date_of_issue->format('d/m/Y') : null,
            'date_of_issue_format' => $this->date_of_issue ? $this->date_of_issue->format('Y-m-d') : null,
            'person_id' => $this->person_id,
            'person' => $this->whenLoaded('person', function () {
                return [
                    'id' => $this->person->id,
                    'name' => $this->person->name,
                ];
            }),
            'user_id' => $this->user_id,
            'warehouse_id' => $this->warehouse_id,
            'warehouse' => $this->whenLoaded('warehouse', function () {
                return [
                    'id' => $this->warehouse->id,
                    'description' => $this->warehouse->description,
                ];
            }),
            'destination_warehouse_id' => $this->destination_warehouse_id,
            'destination_warehouse' => $this->whenLoaded('destinationWarehouse', function () {
                return [
                    'id' => $this->destinationWarehouse->id,
                    'description' => $this->destinationWarehouse->description,
                ];
            }),
            'condition' => $this->condition,
            'status' => $this->status,
            'prod_start_date' => $this->prod_start_date,
            'prod_start_time' => $this->prod_start_time,
            'prod_end_date' => $this->prod_end_date,
            'prod_end_time' => $this->prod_end_time,
            'prod_responsible' => $this->prod_responsible,
            'mix_start_date' => $this->mix_start_date,
            'mix_start_time' => $this->mix_start_time,
            'mix_end_date' => $this->mix_end_date,
            'mix_end_time' => $this->mix_end_time,
            'mix_responsible' => $this->mix_responsible,
            'observation' => $this->observation,
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'item_id' => $item->item_id,
                        'quantity' => (float) $item->quantity,
                        'unit_price' => (float) $item->unit_price,
                        'lot_code' => $item->lot_code,
                        'status' => $item->status,
                        'item_type' => $item->item_type,
                        'item' => ($item->relationLoaded('item') && $item->item) ? [
                            'id' => $item->item->id,
                            'description' => $item->item->description,
                            'full_description' => $item->item->internal_id ? "{$item->item->internal_id} - {$item->item->description}" : $item->item->description,
                            'internal_id' => $item->item->internal_id,
                            'unit_type_id' => $item->item->unit_type_id,
                        ] : null,
                    ];
                });
            }),
            'created_at' => $this->created_at ? $this->created_at->format('d/m/Y H:i') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('d/m/Y H:i') : null,
        ];
    }
}