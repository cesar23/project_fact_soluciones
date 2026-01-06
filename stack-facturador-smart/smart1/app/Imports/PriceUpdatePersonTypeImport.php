<?php

namespace App\Imports;

use App\Models\Tenant\Item;
use App\Models\Tenant\PersonType;
use App\Models\Tenant\PersonTypePrice;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;



class PriceUpdatePersonTypeImport implements ToCollection
{
    use Importable;

    protected $data;

    public function collection(Collection $rows)
    {
        $total = count($rows);
        $type_id_de = request('type_id');
        $registered = 0;
        unset($rows[0]);
        unset($rows[1]);
        foreach ($rows as $row) {
            $internal_id = $row[0];
            $rest = array_slice($row->toArray(), 1);
          
            $item = Item::getItemByInternalId($internal_id);
            $types = PersonType::all()->toArray();
            if ($item) {
                foreach ($types as $key => $type) {
                    if (isset($rest[$key]) && $rest[$key] != '') {

                        $item_type_price = PersonTypePrice::where('item_id', $item->id)->where('person_type_id', $type['id'])->first();
              
                        if (is_numeric($rest[$key])) {
                            if ($item_type_price) {
                                $item_type_price->update([
                                    'price' => $rest[$key],
                                ]);
                                $item_type_price->save();
                            } else {
                                $item_type_price = PersonTypePrice::create([
                                    'item_id' => $item->id,
                                    'person_type_id' => $type['id'],
                                    'price' => $rest[$key],
                                   
                                ]);
                            }
                            $registered += 1;
                        }
                    }
                }
            }
        }
        $this->data = compact('total', 'registered', 'type_id_de');
    }

    public function getData()
    {
        return $this->data;
    }
}
