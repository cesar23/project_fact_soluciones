<?php

namespace Modules\Item\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Carbon\Carbon;

class ItemHistoryPurchasesCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request) {
        return $this->collection->transform(function($row, $key) {
            $unit_price_apportioned_affected = null;
            $unit_value_apportioned_affected = null;
            $total = $row->total;
            $is_dollar = $row->currency_type_id == 'USD' || $row->was_dollar == 1;
            if($is_dollar && $row->affectation_igv_type_id !== '15'){
                if($row->was_dollar == 1 && $row->currency_type_id == 'PEN'){
                    $total = $total / $row->exchange_rate_sale;
                    $unit_price_apportioned_affected = (($row->unit_price_apportioned_affected && $row->unit_price_apportioned_affected > 0) 
                    ? $row->unit_price_apportioned_affected 
                    : $row->unit_price) / $row->exchange_rate_sale;
                $unit_value_apportioned_affected = (($row->unit_value_apportioned_affected && $row->unit_value_apportioned_affected > 0) 
                    ? $row->unit_value_apportioned_affected  
                    : $row->unit_value) / $row->exchange_rate_sale;
                }else{
                    $unit_price_apportioned_affected = ($row->unit_price_apportioned_affected && $row->unit_price_apportioned_affected > 0) 
                    ? $row->unit_price_apportioned_affected 
                    : $row->unit_price;
                $unit_value_apportioned_affected = ($row->unit_value_apportioned_affected && $row->unit_value_apportioned_affected > 0) 
                    ? $row->unit_value_apportioned_affected  
                    : $row->unit_value;
                }
                
            }
        
            return [
                'affectation_igv_type_id' => $row->affectation_igv_type_id,
                'was_dollar' => (bool) $row->was_dollar,
                'id' => $row->id,
                'number_full' => "{$row->series}-{$row->number}",
                'series' => $row->series,
                'number' => $row->number,
                'date_of_issue' => $row->date_of_issue,
                'price' => $row->price, 
                'exchange_rate_sale' => $row->exchange_rate_sale,
                'currency_type_id' => $row->currency_type_id,
                'total' =>number_format($total, 2),  
                'quantity' => number_format($row->quantity, 2), 
                'supplier_name' => $row->supplier_name, 
                'supplier_number' => $row->supplier_number, 
                'quantity_apportionment' => $row->quantity_apportionment,
                'unit_value_apportioned_affected' => $unit_value_apportioned_affected ? number_format($unit_value_apportioned_affected, 2) : null,
                'total_apportioned' => $row->total_apportioned,
                'unit_price_apportioned' => $row->unit_price_apportioned,
                'unit_price_apportioned_affected' => $unit_price_apportioned_affected ? number_format($unit_price_apportioned_affected, 2) : null,
                'discount_apportioned' => $row->discount_apportioned,
                'affected' => $row->affected,
                'stock_before_apportionment' => $row->stock_before_apportionment,
            ];
        });
    }
}
