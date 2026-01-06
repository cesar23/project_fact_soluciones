<?php

namespace App\Http\Resources\Tenant;

use App\CoreFacturalo\Requests\Inputs\Functions;
use App\Models\Tenant\ApportionmentItemsStock;
use App\Models\Tenant\ItemSizeStock;
use App\Models\Tenant\Purchase;
use Modules\Inventory\Models\Warehouse;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $purchase = Purchase::find($this->id);
        $purchase->purchase_payments = self::getTransformPayments($purchase->purchase_payments);
        $purchase->items = self::getTransformItems($purchase->items,$purchase->was_dollar,$purchase->exchange_rate_sale);
        $purchase->customer_number = $purchase->customer_id ? $purchase->customer->number : null;
        $purchase->fee = $purchase->fee;

        return [
            'id' => $this->id,
            'external_id' => $this->external_id,
            'group_id' => $this->group_id,
            'number' => $this->number_full,
            'date_of_issue' => $this->date_of_issue->format('Y-m-d'),
            'purchase' => $purchase
        ];
    }


    public static function getTransformPayments($payments)
    {

        return $payments->transform(function ($row, $key) {
            return [
                'id' => $row->id,
                'purchase_id' => $row->purchase_id,
                'date_of_payment' => $row->date_of_payment->format('Y-m-d'),
                'payment_method_type_id' => $row->payment_method_type_id,
                'has_card' => $row->has_card,
                'card_brand_id' => $row->card_brand_id,
                'reference' => $row->reference,
                'payment' => $row->payment,
                'payment_method_type' => $row->payment_method_type,
                'payment_destination_id' => ($row->global_payment) ? ($row->global_payment->type_record == 'cash' ? 'cash' : $row->global_payment->destination_id) : null,
                'payment_filename' => ($row->payment_file) ? $row->payment_file->filename : null,
            ];
        });
    }


    public static function getTransformItems($items,$was_dollar,$exchange_rate_sale)
    {


        return $items->transform(function ($row, $key) use ($was_dollar,$exchange_rate_sale) {
            $sizes_added = [];
            $sizes = [];
            $warehouse_id = $row->warehouse_id;
            $unit_price = $row->unit_price;
            $unit_value = $row->unit_value;
            if($was_dollar &&  !$row->affected) {
                $unit_price = $unit_price / $exchange_rate_sale;
                $unit_value = $unit_value / $exchange_rate_sale;
            }
            $observation_apportionment = $row->observation_apportionment;
            $purchase_item_id = $row->id;
            $apportionment_items_stock = ApportionmentItemsStock::where('purchase_item_id', $purchase_item_id)->first();
            if($apportionment_items_stock){
                $observation_apportionment = $apportionment_items_stock->observation;
            }

            if (isset($row->item->sizes_added)) {
                $sizes_added = $row->item->sizes_added;
                foreach ($sizes_added as $size) {
                    $id = null;
                    $item_size = ItemSizeStock::where('item_id', $row->item_id)
                        ->where('size', $size->size)
                        ->where('warehouse_id', $warehouse_id)
                        ->first();
                    if ($item_size) {
                        $id = $item_size->id;
                    }
                    $sizes[] = [
                        'id' => $id,
                        'size' => $size->size,
                        'stock' => $size->stock,
                    ];
                }
            }

            return [
                'sizes' => $sizes,
                'id' => $row->id,
                'purchase_id' => $row->purchase_id,
                'item_id' => $row->item_id,
                'item' => $row->item,
                'lot_code' => $row->lot_code,
                'item_lot_group_id' => $row->item_lot_group_id,
                'quantity' => $row->quantity,
                'unit_value' => $unit_value,
                'date_of_due' => $row->date_of_due,
                'affectation_igv_type_id' => $row->affectation_igv_type_id,
                'total_base_igv' => $row->total_base_igv,
                'percentage_igv' => $row->percentage_igv,
                'total_igv' => $row->total_igv,
                'system_isc_type_id' => $row->system_isc_type_id,
                'total_base_isc' => $row->total_base_isc,
                'percentage_isc' => $row->percentage_isc,
                'total_isc' => $row->total_isc,
                'total_base_other_taxes' => $row->total_base_other_taxes,
                'percentage_other_taxes' => $row->percentage_other_taxes,
                'total_other_taxes' => $row->total_other_taxes,
                'total_taxes' => $row->total_taxes,
                'price_type_id' => $row->price_type_id,
                'unit_price' => $unit_price,
                'total_value' => $row->total_value,
                'total_charge' => $row->total_charge,
                'total_discount' => $row->total_discount,
                'total' => $row->total,
                'attributes' => $row->attributes,
                'discounts' => $row->discounts,
                'charges' => $row->charges,
                'warehouse_id' => $row->warehouse_id,
                'affectation_igv_type' => $row->affectation_igv_type,
                'system_isc_type' => $row->system_isc_type,
                'price_type' => $row->price_type,
                'lots' => $row->lots,
                'warehouse' => ($row->warehouse) ? $row->warehouse :  self::getWarehouse($row->purchase->establishment_id),
                'name_product_pdf' => $row->name_product_pdf,
                'unit_value_apportioned_affected' => $row->unit_value_apportioned_affected,
                'unit_price_apportioned_affected' => $row->unit_price_apportioned_affected,
                'quantity_apportionment' => $row->quantity_apportionment,
                'stock_before_apportionment' => $row->stock_before_apportionment,
                'unit_price_apportioned' => $row->unit_price_apportioned,
                'total_apportioned' => $row->total_apportioned,
                'discount_apportioned' => $row->discount_apportioned,
                'observation_apportionment' => $observation_apportionment,
                'affected' => $row->affected,
            ];
        });
    }

    public static function getWarehouse($establishment_id)
    {
        return Warehouse::where('establishment_id', $establishment_id)->first();
    }
}
