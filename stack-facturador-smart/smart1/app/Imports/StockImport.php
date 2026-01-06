<?php

namespace App\Imports;

use App\Models\Tenant\Item;
use App\Models\Tenant\Warehouse;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Modules\Item\Models\Category;
use Modules\Item\Models\Brand;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Modules\Inventory\Models\InventoryTransaction;
use Modules\Inventory\Models\Inventory;
use Modules\Inventory\Models\ItemWarehouse;
use Exception;
use Modules\Item\Models\ItemLotsGroup;


class StockImport implements ToCollection
{
    use Importable;

    protected $data;

    public function collection(Collection $rows)
    {
        $total = count($rows);
        $warehouse_id_de = request('warehouse_id');
        $registered = 0;
        unset($rows[0]);
        foreach ($rows as $row) {
            $internal_id = ($row[0]) ?: null;
            $quantity_real = ($row[1]) ?: null;

            $item = Item::whereRaw('internal_id REGEXP ?', ["^" . preg_quote($internal_id) . "$"])
            ->first();
            $inventory_transaction_id = null;
            if ($item) {
                $quantity = ItemWarehouse::where('item_id', $item->id)
                    ->where('warehouse_id', $warehouse_id_de)
                    ->select('stock')->get();
                if($quantity->isEmpty()){
                    throw new Exception("El item $internal_id no tiene stock en el warehouse $warehouse_id_de");
                }
                //8
                $quantity = $quantity[0]->stock;
                //4 - 8 = -4
                $quantity_new = $quantity_real - $quantity;
                if ($quantity_new > 0) {
                    $type = 1;
                    // $type = 1; // Entrada

                } else if ($quantity_new < 0) {
                    $type = 3; // Salida
                    $quantity_new = abs($quantity_new); // Convertimos a positivo para guardar
                } else {
                    continue; // No hay cambio, saltamos al siguiente item
                }
                $inventory_transaction_id = 102;


                $inventory = new Inventory();
                $inventory->description = 'Ajuste de Stock';
                $inventory->item_id = $item->id;
                $inventory->inventory_transaction_id = $inventory_transaction_id;
                $inventory->warehouse_id = $warehouse_id_de;
                $inventory->quantity = $quantity_new;
                $inventory->type = $type;



                $inventory->save();

                $registered += 1;
            }
        }
        $this->data = compact('total', 'registered', 'warehouse_id_de');
    }

    public function getData()
    {
        return $this->data;
    }
}
