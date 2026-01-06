<?php

namespace Modules\Item\Imports;

use App\Models\Tenant\Item;
use App\Models\Tenant\ItemSizeStock;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;

use App\Models\Tenant\ItemWarehouse;


class ItemListSizeImport implements ToCollection
{
    use Importable;

    protected $data;

    public function collection(Collection $rows)
    {
            $warehouse_id = request('warehouse_id');
            $total = count($rows);
            $registered = 0;
            unset($rows[0]);

            foreach ($rows as $row)
            {
                $internal_id = ($row[0])?:null;
                $size = $row[1];
                $stock = $row[2];
            
 

                $item = null;

                if($internal_id) {
                    $item = Item::where('internal_id', $internal_id)
                                    ->first();
                }

                
                if($item) {
                    $item_size_stock = ItemSizeStock::where('item_id', $item->id)
                                                    ->where('size', $size)
                                                    ->where('warehouse_id', $warehouse_id)
                                                    ->first();
                   
                    if($item_size_stock) {
                        $item_size_stock->stock += $stock;
                        $item_size_stock->save();
                    }else{
                        $item_size_stock = new ItemSizeStock();
                        $item_size_stock->item_id = $item->id;
                        $item_size_stock->size = $size;
                        $item_size_stock->stock = $stock;
                        $item_size_stock->warehouse_id = $warehouse_id;
                        $item_size_stock->save();
                    }
                    
                    //aumentar el stock en Item y ItemWarehouse (si este no existe crearlo)
                    $item->stock += $stock;
                    $item->has_sizes = 1;
                    $item->save();
                    $item_warehouse = ItemWarehouse::where('item_id', $item->id)
                                                    ->where('warehouse_id', $warehouse_id)
                                                    ->first();
                    if($item_warehouse) {
                        $item_warehouse->stock += $stock;
                        $item_warehouse->save();
                    }else{
                        $item_warehouse = new ItemWarehouse();
                        $item_warehouse->item_id = $item->id;
                        $item_warehouse->warehouse_id = $warehouse_id;
                        $item_warehouse->stock = $stock;
                        $item_warehouse->save();
                    }


    
                    $registered += 1;

                } 

            }

            $this->data = compact('total', 'registered');

    }

    public function getData()
    {
        return $this->data;
    }
}
