<?php

namespace App\Imports;

use App\Http\Controllers\SearchItemController;
use App\Models\Tenant\Catalogs\UnitType;
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
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class ItemsImportDispatch implements ToCollection
{
    use Importable;

    protected $data;

    public function collection(Collection $rows)
    {
        $total = count($rows) - 1;
        $warehouse_id_de = request('warehouse_id');
        $type = request('type');
        $registered = 0;
        $states = ["incomplete", "not found"];
        $errors = [];
        $items = [];
        unset($rows[0]);
        try {
            DB::beginTransaction();
            foreach ($rows as $row) {


                $internal_id = isset($row[0]) ? $row[0] : null;
                $quantity = isset($row[1]) ? $row[1] : 1;
                $weight = isset($row[2]) ? $row[2] : 1;
                $weight = floatval($weight);

                if ($internal_id) {
                    $item = Item::where('internal_id', $internal_id)
                        ->first();

                    if ($item && $quantity > 0) {


                        $found =   (new SearchItemController)->getItemsToDocuments(null, $item->id);

                        //obtener el primer elemento de found que es una coleccion
                        $found = isset($found[0]) ? $found[0] : null;
                        //cambiar sale_unit_price de $found por 18
                        $found["weight"] = $weight;
                        
                        $found["quantity"] = $quantity;
                        if ($found) {
                            $items[] = $found;
                        }
                        $registered++;
                    } else {
                        $message = 'No se encontró el producto';
                        if ($quantity == 0) {
                            $message = 'La cantidad no puede ser 0';
                        }

                        $errors[] = [
                            'internal_id' => $internal_id,
                            'quantity' => $quantity,
                            'weight' => $weight,
                            'message' => $message
                        ];
                    }
                } else {
                    $item = null;
                }
            }
            //si errors tiene elementos crear un hash de 7 caracteres y guardar la data en el cache
            $hash = "";
            if (count($errors) > 0) {
                $hash = Str::random(7);
                Cache::put($hash, $errors, now()->addMinutes(10));
            }
            $this->data = compact('total', 'items', 'registered', 'warehouse_id_de', 'errors', 'states', 'hash');
            DB::commit();
        } catch (Exception $e) {
            Log::error($e->getMessage());
            DB::rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    public function getData()
    {
        return $this->data;
    }
}
