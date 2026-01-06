<?php

namespace Modules\Inventory\Imports;

use App\Models\Tenant\Item;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Modules\Inventory\Models\ItemWarehouse;

class TransferImport implements ToCollection
{
    use Importable;
    protected $data;
    public function collection(Collection $rows)
    {
        $warehouse_id = request('warehouse_id');
        unset($rows[0]);
        foreach ($rows as $row) {
            $internal_code = $row[0];
            $stock = $row[1];

            $item = Item::where('internal_id', $internal_code)->first();

            if ($item && !$item->lots_enabled && !$item->series_enabled && !$item->is_set) {
                $item_warehouse = ItemWarehouse::where('item_id', $item->id)
                    ->where('warehouse_id', $warehouse_id)
                    ->first();

                if ($item_warehouse) {
                    $this->data[] = [
                        'id' => $item->id,
                        'internal_id' => $item->internal_id,
                        'description' => $item->description,
                        'barcode' => $item->barcode,
                        'current_stock' => $item_warehouse->stock,
                        'quantity' => $stock,
                        'lots' => []
                    ];
                
                } 
            }
        }
    }
    public function getData()
    {
        return $this->data;
    }
}
