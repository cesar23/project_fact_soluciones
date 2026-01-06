<?php

namespace Modules\Inventory\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Item\Models\ItemProperty;

class InventoryCollection extends ResourceCollection
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
                'item_internal_id' => $row->item->internal_id,
                'item_description' => $row->item->description,
                'item_fulldescription' => ($row->item->internal_id) ? "{$row->item->internal_id} - {$row->item->description}" :$row->item->description,
                'warehouse_description' => $row->warehouse->description,
                'warehouse_id' => $row->warehouse_id,
                'attributes'       =>    ItemProperty::where('item_id',$row->item_id)->where('has_sale',0)->where('warehouse_id',$row->warehouse->id)->get()->transform(function ($row_items){
                    return [
                        'item_id'         => $row_items->item_id,
                        'warehouse_id'    => $row_items->warehouse_id,
                        'chassis'         => $row_items->chassis,
                        'attribute'       => $row_items->attribute,
                        'attribute2'      => $row_items->attribute2,
                        'attribute3'      => $row_items->attribute3,
                        'attribute4'      => $row_items->attribute4,
                        'attribute5'      => $row_items->attribute5,
                        'sales_price'     => $row_items->sales_price,
                        'state'           => $row_items->state,
                    ]; 
                }),
                'stock' => $row->stock,
                'created_at' => $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : null,
                'updated_at' => $row->updated_at ? $row->updated_at->format('Y-m-d H:i:s') : null,
            ];
        });
    }
}