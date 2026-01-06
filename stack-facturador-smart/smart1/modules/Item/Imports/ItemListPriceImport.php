<?php

namespace Modules\Item\Imports;

use App\Models\Tenant\Item;
use App\Models\Tenant\Warehouse;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Modules\Item\Models\Category;
use Modules\Item\Models\Brand;
use App\Models\Tenant\ItemUnitType;


class ItemListPriceImport implements ToCollection
{
    use Importable;

    protected $data;

    public function collection(Collection $rows)
    {
            $total = count($rows);
            $registered = 0;
            unset($rows[0]);

            foreach ($rows as $row)
            {
                $internal_id = ($row[0])?:null;
                $description = $row[1];
                $barcode = $row[2];
                $unit_type_id = $row[3];
                $factor = $row[4];
                $price1 = $row[5];
                $price2 = $row[6];
                $price3 = $row[7];
                $price_default = $row[8];
                $factor_default = $row[9];
                if($factor_default !== null){
                    $factor_default = strtolower($factor_default);
                }
 

                $item = null;

                if($internal_id) {
                    $item = Item::where('internal_id', $internal_id)
                                    ->first();
                }

                
                if($item) {
                    
                    $item_unit_type = ItemUnitType::where('item_id', $item->id);
                    if($barcode) {
                        $item_unit_type = $item_unit_type->where('barcode', $barcode);
                    }else{
                        $item_unit_type = $item_unit_type->whereDescription($description);
                    }
                    $item_unit_type = $item_unit_type->first();
                                                    // ->whereDescription($description)
                    
                    
                    
                    if(!$item_unit_type){
                        if($factor_default == "si"){
                            ItemUnitType::where('item_id', $item->id)
                            ->update([
                                'factor_default' => 0
                            ]);
                        }
                        $item->item_unit_types()->create([
                            'factor_default' => $factor_default == "si" ? 1 : 0, 
                            'barcode' => $barcode,
                            'description' => $description,
                            'unit_type_id' => $unit_type_id,
                            'quantity_unit' => $factor,
                            'price1' => $price1,
                            'price2' => $price2,
                            'price3' => $price3,
                            'price_default' => $price_default,
                        ]);
                    }else{
                        $item_unit_type->update([
                            'price1' => $price1,
                            'price2' => $price2,
                            'price3' => $price3,
                            'price_default' => $price_default,
                            'factor_default' => $factor_default == "si" ? 1 : 0, 
                            'description' => $description,
                            'unit_type_id' => $unit_type_id,
                            'quantity_unit' => $factor,
                        ]);
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
