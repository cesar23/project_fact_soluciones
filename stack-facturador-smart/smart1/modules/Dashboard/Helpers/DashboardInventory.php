<?php

namespace Modules\Dashboard\Helpers;

use Modules\Inventory\Models\ItemWarehouse;
use Modules\Dashboard\Http\Resources\DashboardInventoryCollection;
use App\Models\Tenant\Establishment;
use Modules\Dashboard\Http\Resources\DashboardInventoryLotsCollection;
use Modules\Item\Models\ItemLot;
use Modules\Item\Models\ItemLotsGroup;

class DashboardInventory
{

    public function data($request)
    {
        $establishment_id = $request->establishment_id;
        $date_start = $request->date_start;
        $date_end = $request->date_end;

        return $this->products_date_of_due($establishment_id, $date_start, $date_end);
    }

    public function data_lots($request){
        $establishment_id = $request->establishment_id;
        $date_start = $request->date_start;
        $date_end = $request->date_end;

        return $this->products_date_of_due_lots($establishment_id, $date_start, $date_end);
    }

    private function products_date_of_due_lots($establishment_id, $date_start, $date_end){
        $item_lots_group_query = ItemLot::query()
            ->select('item_lots.id', 'item_lots.item_id', 'item_lots.warehouse_id', 'item_lots.series', 'item_lots.date', 'item_lots.date_incoming')

            ->join('items', 'item_lots.item_id', '=', 'items.id') 
            ->join('warehouses', 'item_lots.warehouse_id', '=', 'warehouses.id')
            ->where('items.unit_type_id', '!=', 'ZZ')
            ->where('item_lots.has_sale', false)
            ->where('item_lots.state', 'Activo')
            ->where(function ($query) use ($date_start, $date_end) {
                if ($date_start && $date_end) {
                    $query->whereBetween('item_lots.date', [$date_start, $date_end]);
                    
                }
            })
            ->where('warehouses.establishment_id', $establishment_id)
            ->orderBy('item_lots.date', 'asc');
    

        return new DashboardInventoryLotsCollection($item_lots_group_query->paginate(config(20)));

    }


    private function products_date_of_due($establishment_id, $date_start, $date_end)
    {

        if (!$establishment_id) {
            $establishment_id = Establishment::select('id')->first()->id;
        }

        $item_warehouse_query = ItemWarehouse::query()
            ->select('item_warehouse.id', 'item_warehouse.item_id', 'item_warehouse.warehouse_id', 'item_warehouse.stock', 'items.date_of_due')
            ->join('items', 'item_warehouse.item_id', '=', 'items.id')
            ->where('items.unit_type_id', '!=', 'ZZ')
            ->where(function ($query) use ($date_start, $date_end) {
                if ($date_start && $date_end) {
                    $query->whereBetween('items.date_of_due', [$date_start, $date_end]);
                }
            })
            ->whereHas('warehouse', function ($query) use ($establishment_id) {
                $query->where('establishment_id', $establishment_id);
            });

        $item_lots_group_query = ItemLotsGroup::query()
            ->select('item_lots_group.id', 'item_lots_group.item_id', 'item_lots_group.warehouse_id', 'item_lots_group.quantity as stock', 'item_lots_group.date_of_due')
            ->join('items', 'item_lots_group.item_id', '=', 'items.id')
            ->where('items.unit_type_id', '!=', 'ZZ')
            ->where(function ($query) use ($date_start, $date_end) {
                if ($date_start && $date_end) {
                    $query->whereBetween('item_lots_group.date_of_due', [$date_start, $date_end]);
                    
                }
            })
            ->whereHas('warehouse', function ($query) use ($establishment_id) {
                $query->where('establishment_id', $establishment_id);
            });

        $products = $item_warehouse_query->union($item_lots_group_query)->paginate(config('tenant.items_per_page_simple_d_table'));

        return new DashboardInventoryCollection($products);
    }
}
