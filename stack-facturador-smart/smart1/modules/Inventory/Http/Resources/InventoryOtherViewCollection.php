<?php

namespace Modules\Inventory\Http\Resources;

use App\Models\Tenant\Configuration;
use App\Models\Tenant\Warehouse;
use Illuminate\Http\Resources\Json\ResourceCollection;

class InventoryOtherViewCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        $wareouses = Warehouse::all();
        $configuration = Configuration::getConfig();
        return $this->collection->transform(function($row, $key) use ($wareouses, $configuration) {
            $item_warehouses = $row->warehouses;
                $id = $row->id;
                $item_fulldescription = $row->internal_id ? "{$row->internal_id} - {$row->description}" : $row->description;
                
                
            $warehouses_to_return = [];
            foreach ($wareouses as $warehouse) {
                $w = $item_warehouses->where('warehouse_id', $warehouse->id)->first();
                $warehouses_to_return['warehouse_'.$warehouse->id] = $w ? number_format($w->stock, $configuration->stock_decimal) : 0;
            }

            return [
                'id' => $id,
                'item_fulldescription' => $item_fulldescription,
                'warehouses' => array_values($warehouses_to_return),
            ];

            
        });
    }
}