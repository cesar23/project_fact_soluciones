<?php

namespace Modules\Inventory\Http\Resources;

use Modules\Inventory\Models\Warehouse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Item\Models\ItemProperty;
use Modules\Item\Models\ItemPropertyInventory;

class TransferCollection extends ResourceCollection
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
            $transfers = $row->inventory_transfer_item->transform(function ($o) use ($row) {
                if ($o->item_lots_group_id != null) {
                    return [
                        'item_id' => $o->item_lots_group->item_id,
                        'lot_code' => $o->item_lots_group->code,
                    ];
                    // return [
                    //     'item_lots_group_id' => $o->item_lots_group_id,
                    //     'item_id' => $o->item_lots_group->item_id,
                    //     'lot_code' => $o->item_lots_group->code,
                    //     'relation' => $o->item_lots_group,
                    // ];
                }
                if ($o->item_lot_id != null) {
                    return [
                        'item_lot_id' => $o->item_lot_id,
                        'item_id' => $o->item_lot->item_id,
                        'relation' => $o->item_lot,
                    ];
                }
            });
            $user = auth()->user();
            $warehouse = Warehouse::where('establishment_id', $user->establishment_id)->first();
            $warehouse_id = $warehouse->id;
            $can_confirm = $warehouse_id === $row->warehouse_destination_id;
            $can_confirm = $row->user_accept_id ? ($row->user_accept_id === $user->id) : $can_confirm;
            $user_to_accept = optional($row->user_to_accept)->name;

            $description = $user_to_accept ? "{$row->description} - Para ser aceptado por {$user_to_accept}" : $row->description;
            $dispatch = $row->dispatch ? $row->dispatch->number_full : null;
            return [
                'dispatch' => $dispatch,
                'can_confirm' => $can_confirm,
                'state' => $row->state,
                'series' => $row->series,
                'number' => $row->number,
                'id' => $row->id,
                'user_name' => $row->user->name,
                'description' => $description,
                'quantity' => round($row->quantity, 1),
                'warehouse' => $row->warehouse->description,
                'warehouse_destination' => $row->warehouse_destination->description,
                'created_at' => $row->created_at->format('Y-m-d H:i:s'),
                'pending' =>
                $row->state !== 1 ? [] :
                    $row->inventory_to_accept->transform(function ($o) use ($row, $transfers) {
                        $lots_enabled = isset($o->series_lots["lot_groups"]) ? count($o->series_lots["lot_groups"]) > 0 : false;
                        $lots = isset($o->series_lots["lots"]) ? $o->series_lots["lots"] : [];
                        return [
                            'id' => $o->item->id,
                            'description' => $o->item->description,
                            'quantity' => $o->quantity,
                            'lots_enabled' => $lots_enabled,
                            'lot_codes' => isset($o->series_lots["lot_groups"]) ? $o->series_lots["lot_groups"] : null,
                            // 'lots' => [],
                            'lots' => $lots,
                        ];
                    }),
                'inventory' => $row->inventory->transform(function ($o) use ($row, $transfers) {
                    $inventory_id = $o->id;
                    $attributes_ids = ItemPropertyInventory::where('inventory_id', $inventory_id)->pluck('item_property_id');
                    $inventory_attributes = collect([]);
                    if ($attributes_ids->count() > 0) {
                        $inventory_attributes = ItemProperty::whereIn('id', $attributes_ids)->get();
                    }
                    return [
                        'id' => $o->item->id,
                        'description' => $o->item->description,
                        'attributes' => $inventory_attributes,
                        'quantity' => $o->quantity,
                        'lots_enabled' => (bool)$o->item->lots_enabled,
                        'lot_codes' => (bool)$o->item->lots_enabled ? $this->codes($transfers, $o->item->id) : null,
                        'lots' => $o->item->item_lots->where('has_sale', false)
                            ->where('warehouse_id', $row->warehouse_destination_id)
                            ->where('updated_at', $o->created_at)
                            ->transform(function ($row) {
                                return [
                                    'id' => $row->id,
                                    'series' => $row->series,
                                    'date' => $row->date,
                                    'item_id' => $row->item_id,
                                    'warehouse_id' => $row->warehouse_id,
                                    'has_sale' => (bool)$row->has_sale,
                                    'lot_code' => ($row->item_loteable_type) ? (isset($row->item_loteable->lot_code) ? $row->item_loteable->lot_code : null) : null
                                ];
                            }),
                    ];
                })
            ];
        });
    }

    public function codes($transfers, $item_id)
    {
        $response = $transfers->filter(function ($value) use ($item_id) {
            return $value['item_id'] == $item_id;
        });

        return $response;
    }
}
