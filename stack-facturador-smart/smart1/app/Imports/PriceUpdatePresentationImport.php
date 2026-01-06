<?php

namespace App\Imports;

use App\Models\Tenant\Catalogs\UnitType;
use App\Models\Tenant\Item;
use App\Models\Tenant\ItemUnitType;
use App\Models\Tenant\ItemWarehousePrice;
use App\Models\Tenant\Warehouse;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;



class PriceUpdatePresentationImport implements ToCollection
{
    use Importable;

    protected $data;

    public function collection(Collection $rows)
    {
        $total = count($rows);
        $registered = 0;
        unset($rows[0]);
        foreach ($rows as $row) {
            $internal_id = $row[0];
            $description = strtoupper($row[1]);
            $price1 = $row[2];
            $price2 = $row[3];
            $price3 = $row[4];
    
            $rest = array_slice($row->toArray(), 1);
            $item = Item::getItemByInternalId($internal_id);
            if(!$item){
                continue;
            }
            $item_id = $item->id;
            $item_unit_type = ItemUnitType::where('item_id', $item_id)->where(DB::raw('UPPER(description)'), $description)->first();
            if(!$item_unit_type){
                continue;
            }
            $item_unit_type->update([
                'price1' => $price1 ?? 0,
                'price2' => $price2 ?? 0,
                'price3' => $price3 ?? 0,
            ]);
            $registered += 1;
        }
        $this->data = compact('total', 'registered');
    }

    public function getData()
    {
        return $this->data;
    }
}
