<?php

namespace App\Imports;

use App\Models\Tenant\Catalogs\UnitType;
use App\Models\Tenant\Item;
use App\Models\Tenant\ItemWarehousePrice;
use App\Models\Tenant\Warehouse;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;



class PriceUpdateWarehouseImport implements ToCollection
{
    use Importable;

    protected $data;

    public function collection(Collection $rows)
    {
        $total = count($rows);
        $warehouse_id_de = request('warehouse_id');
        $registered = 0;
        unset($rows[0]);
        unset($rows[1]);
        foreach ($rows as $row) {
            $internal_id = $row[0];
            $rest = array_slice($row->toArray(), 1);
          
            $item = Item::getItemByInternalId($internal_id);
            $warehouses = Warehouse::where('active', true)->get()->toArray();
            if ($item) {
                foreach ($warehouses as $key => $warehouse) {
                    if (isset($rest[$key]) && $rest[$key] != '') {

                        $item_warehouse_price = ItemWarehousePrice::where('item_id', $item->id)->where('warehouse_id', $warehouse['id'])->first();
              
                        if (is_numeric($rest[$key])) {
                            if ($item_warehouse_price) {
                                $item_warehouse_price->update([
                                    'price' => $rest[$key],
                                ]);
                                $item_warehouse_price->save();
                            } else {
                                $item_warehouse_price = ItemWarehousePrice::create([
                                    'item_id' => $item->id,
                                    'warehouse_id' => $warehouse['id'],
                                    'price' => $rest[$key],
                                   
                                ]);
                            }
                            $registered += 1;
                        }
                    }
                }
            }
        }
        $this->data = compact('total', 'registered', 'warehouse_id_de');
    }

    public function getData()
    {
        return $this->data;
    }
}
