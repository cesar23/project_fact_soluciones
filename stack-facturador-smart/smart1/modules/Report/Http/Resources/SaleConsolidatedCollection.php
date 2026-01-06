<?php

namespace Modules\Report\Http\Resources;

use App\Models\Tenant\Item;
use Exception;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Log;

class SaleConsolidatedCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        return $this->collection->transform(function($row, $key){
            $item_model = isset($row->relation_item) ? $row->relation_item : $row->item;
            
            $unit_type_id = $item_model->unit_type_id;
            $unit_price = 1;
            $item_quantity = $row->quantity;
            // $category  = [
            //     'id' => '',
            //     'name' => ''
            // ];
            $brand = [
                'id' => '',
                'name' => ''
            ];
            if($row->item !== null ) {
                try {
                    if (
                        property_exists($row->item, 'presentation') &&
                        $row->item->presentation !== null &&
                        is_object($row->item->presentation) &&
                        property_exists($row->item->presentation, 'unit_type_id')
                    ) {
                        $unit_type_id = $row->item->presentation->unit_type_id;
                        if(property_exists($row->item->presentation, 'quantity_unit')){
                            // $item_quantity = $row->item->presentation->quantity_unit * $row->quantity;
                        }
                    }
                } catch (Exception $e) {
                    $unit_type_id = $item_model->unit_type_id;

                }
                // unit_price
                if($unit_type_id !== 'ZZ' && isset($row->item->internal_id)){
                    $item = Item::select('brand_id')->where('internal_id',$row->item->internal_id)->first();
                    if(!empty($item)){
                        $brand = $item->brand;
                        // $category = $item->category;
                    }
                }
                // category_id
                if (isset($row->item->unit_price)) {
                    $unit_price = $row->item->unit_price;
                    if(!is_numeric(($unit_price))){
                        //check if has commas, if has one replace by dot, if has more than one remove all commas but not the last one and replace the last one by dot
                        $unit_price = str_replace(',', '.', $unit_price);
                        $unit_price = (float) $unit_price;
                    }
                }else if (isset($row->unit_price)) {
                    $unit_price = $row->unit_price;
                    if(!is_numeric(($unit_price))){
                        //check if has commas, if has one replace by dot, if has more than one remove all commas but not the last one and replace the last one by dot
                        $unit_price = str_replace(',', '.', $unit_price);
                        $unit_price = (float) $unit_price;
                    }
                }

            }
            return [
                'id' => $row->id,
                'item_internal_id' => $item_model->internal_id,
                'item_unit_type_id' => $unit_type_id,
                // 'item_unit_type_id' => $row->relation_item->unit_type_id,
                'brand' => $brand,
                // 'category' => $category,
                'category_name' => isset($item_model->category) ? $item_model->category->name : null,
                'item_description' => $row->item->description,
                'item_quantity' => $item_quantity,
                'total_sale' => number_format($unit_price * $row->quantity, 2, '.', ''),
                'series' => $row->series ?? 'NV',
                'number' => $row->number ?? $row->id,
                'date_of_issue' => $row->date_of_issue,
            ];
        });
    }

}
