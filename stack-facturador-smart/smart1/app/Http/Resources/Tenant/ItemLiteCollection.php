<?php

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\Catalogs\CurrencyType;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\ExchangeCurrency;
use App\Models\Tenant\Item;
use App\Models\Tenant\ItemWarehousePrice;
use Illuminate\Support\Facades\DB;

class ItemLiteCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     * 
     * Optimizations implemented:
     * - Batch loading of ItemWarehousePrice records to avoid N+1 queries
     * - Batch loading of apportionment_items_stock records when purchase_apportionment is enabled
     * - Preloading of item IDs and warehouse IDs for efficient querying
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        $configuration = Configuration::first();
        $decimal_units = (int)$configuration->decimal_quantity;
        $affectation_igv_types_exonerated_unaffected = Item::AffectationIgvTypesExoneratedUnaffected();

        // Preload data in batches to avoid N+1 queries
        $item_ids = $this->collection->pluck('id')->toArray();
        $warehouse_ids = $this->collection->flatMap(function ($item) {
            return $item->warehouses->pluck('warehouse_id');
        })->unique()->toArray();

        // Batch load unit types data
        $unit_type_ids = $this->collection->pluck('unit_type_id')->filter()->unique()->toArray();
        $unit_types = [];
        if (!empty($unit_type_ids)) {
            $unit_types_data = DB::connection('tenant')
                ->table('cat_unit_types')
                ->whereIn('id', $unit_type_ids)
                ->get()
                ->keyBy('id');

            foreach ($unit_types_data as $unit_type) {
                $unit_types[$unit_type->id] = $unit_type->description;
            }
        }

        // Batch load categories data
        $category_ids = $this->collection->pluck('category_id')->filter()->unique()->toArray();
        $categories = [];
        if (!empty($category_ids)) {
            $categories_data = DB::connection('tenant')
                ->table('categories')
                ->whereIn('id', $category_ids)
                ->get()
                ->keyBy('id');

            foreach ($categories_data as $category) {
                $categories[$category->id] = $category->name;
            }
        }

        // Batch load ItemWarehousePrice data
        $item_warehouse_prices = [];
        $item_warehouse_prices_full = [];
        if (!empty($warehouse_ids) && !empty($item_ids)) {
            $prices = ItemWarehousePrice::with('warehouse')->whereIn('warehouse_id', $warehouse_ids)
                ->whereIn('item_id', $item_ids)
                ->get();

            foreach ($prices as $price) {
                $item_warehouse_prices[$price->item_id][$price->warehouse_id] = $price->price;

                if (!isset($item_warehouse_prices_full[$price->item_id])) {
                    $item_warehouse_prices_full[$price->item_id] = [];
                }

                $item_warehouse_prices_full[$price->item_id][] = [
                    'id' => $price->id,
                    'item_id' => $price->item_id,
                    'warehouse_id' => $price->warehouse_id,
                    'price' => $price->price,
                    'description' => $price->warehouse->description,
                ];
            }
        }
        $update_items_prices_with_exchange_rate_sale = $configuration->update_items_prices_with_exchange_rate_sale;
        $exchange_rate_sale = null;
        if ($update_items_prices_with_exchange_rate_sale) {
            $last_exchange_rate_sale = ExchangeCurrency::whereNotNull('id')->orderBy('id', 'desc')->first();
            if ($last_exchange_rate_sale) {
                $exchange_rate_sale = $last_exchange_rate_sale->sale;
            }
        }
        // Batch load apportionment data if needed
        $apportionment_data = [];
        if ($configuration->purchase_apportionment && !empty($item_ids)) {
            $apportionments = DB::connection('tenant')
                ->table('apportionment_items_stock')
                ->whereIn('item_id', $item_ids)
                ->where('stock_remaining', '>', 0)
                ->orderBy('created_at', 'asc')
                ->get();
            
            foreach ($apportionments as $apportionment) {
                if (!isset($apportionment_data[$apportionment->item_id])) {
                    $apportionment_data[$apportionment->item_id] = $apportionment;
                }
            }
        }

        return $this->collection->transform(function ($row, $key) use (
            $configuration,
            $decimal_units,
            $affectation_igv_types_exonerated_unaffected,
            $item_warehouse_prices,
            $item_warehouse_prices_full,
            $apportionment_data,
            $unit_types,
            $categories,
            $exchange_rate_sale
        ) {
            /** @var \App\Models\Tenant\Item  $row */

            $reg_san = '';
            if ($configuration->isPharmacy()) {
                $digemid = $row->getCatDigemid();
                if (!empty($digemid)) {
                    $reg_san = $digemid->getNumRegSan();
                }
            }
            if (in_array($row->sale_affectation_igv_type_id, $affectation_igv_types_exonerated_unaffected)) {
                $igv = 1;
            }
            $has_igv_description = null;
            $purchase_has_igv_description = null;
            $igv = 1.18; // E
            $salePriceWithIgv = ($row->has_igv == true) ? $row->sale_unit_price : ($row->sale_unit_price * $igv);
            $salePriceWithIgv = number_format($salePriceWithIgv, $configuration->decimal_quantity, '.', '');
            $purchase_unit_price = $row->purchase_unit_price;
            $observation_apportionment = null;
            $has_apportionment = false;
            $apportionment_id = null;
            if ($configuration->purchase_apportionment && isset($apportionment_data[$row->id])) {
                $apportionment_items_stock = $apportionment_data[$row->id];
                $has_apportionment = true;
                $observation_apportionment = $apportionment_items_stock->observation;
                $purchase_unit_price = $apportionment_items_stock->unit_price_apportioned;
                $apportionment_id = $apportionment_items_stock->id;
            }

            if (in_array($row->sale_affectation_igv_type_id, $affectation_igv_types_exonerated_unaffected)) {
                $has_igv_description = 'No';
            } else {
                $has_igv_description = ((bool)$row->has_igv) ? 'Si' : 'No';
            }

            if (in_array($row->purchase_affectation_igv_type_id, $affectation_igv_types_exonerated_unaffected)) {
                $purchase_has_igv_description = 'No';
            } else {
                $purchase_has_igv_description = ((bool)$row->purchase_has_igv) ? 'Si' : 'No';
            }
            $salePriceWithIgv = ($row->has_igv == true) ? $row->sale_unit_price : ($row->sale_unit_price * $igv);
            $currency = $row->currency_type;
            if (empty($currency)) {
                $currency = new CurrencyType();
            }
            $currency_sale_unit_price_with_igv = $currency->symbol; 
            if($exchange_rate_sale && $row->currency_type_id == 'USD'){
                $salePriceWithIgv = $salePriceWithIgv * $exchange_rate_sale;
                $currency_sale_unit_price_with_igv = "S/";
            }
            $salePriceWithIgv = number_format($salePriceWithIgv, $configuration->decimal_quantity, '.', '');
        
            $number_format = number_format($row->getFormatSaleUnitPrice(), $configuration->decimal_quantity, '.', '');
            $show_sale_unit_price = "{$currency->symbol} {$number_format}";
            return [
                'has_apportionment' => $has_apportionment,
                'apportionment_id' => $apportionment_id,
                'apply_store' => (bool)$row->apply_store,
                'has_igv_description' => $has_igv_description,
                'purchase_has_igv_description' => $purchase_has_igv_description,
                'id' => $row->id,
                'sale_unit_price_with_igv' => "{$currency_sale_unit_price_with_igv} $salePriceWithIgv",
                'observation_apportionment' => $observation_apportionment,
                'name' => $row->name,
                'meter' => $row->meter,
                'model' => $row->model,
                'barcode' => $row->barcode,
                'brand' => $row->brand ? $row->brand->name : null,
                'category_description' => isset($categories[$row->category_id]) ? $categories[$row->category_id] : null,
                'item_code' => $row->item_code,
                'category' => $row->category,
                'sale_unit_price' =>  $show_sale_unit_price,
                'commission_amount' => $row->commission_amount,
                'commission_type' => $row->commission_type,
                'stock' => $row->stock,
                'currency_type_id' => $row->currency_type_id,
                'currency_type_symbol' => $currency->symbol,
                'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                'amount_sale_unit_price' => (float) $row->sale_unit_price,
                'calculate_quantity' => (bool) $row->calculate_quantity,
                'has_igv' => (bool)$row->has_igv,
                'stock_min' => $row->stock_min,
                'percentage_of_profit' => $row->percentage_of_profit,
                'internal_id' => $row->internal_id,
                'description' => $row->description,
                'purchase_unit_price' => "{$currency->symbol} {$purchase_unit_price}",
                'unit_type_id' => $row->unit_type_id,
                'image_url' => route('cached.item.image', ['filename' => $row->image]),
                'image_url_medium' => route('cached.item.image', ['filename' => $row->image_medium]),
                'image_url_small' => route('cached.item.image', ['filename' => $row->image_small]),
                'label_color' => $row->labelColor && $configuration->label_item_color ? [
                    'id' => $row->labelColor->id,
                    'description' => $row->labelColor->labelColor->description,
                    'color' => $row->labelColor->labelColor->color,
                ] : null,
                'sanitary' => $reg_san, 
                'cod_digemid' => $row->cod_digemid,
                'stock_in_more_warehouses' => $row->item_warehouse->count() > 1,
                'stock' => $row->getStockByWarehouse(),
                'stock_by_extra' => [],
                'warehouses' => collect($row->warehouses)->transform(function ($warehouse_row) use ($salePriceWithIgv, $item_warehouse_prices, $row) {
                    $price = $salePriceWithIgv;
                    $item_id = $row->id;
                    $warehouse_id = $warehouse_row->warehouse_id;
                    
                    if (isset($item_warehouse_prices[$item_id][$warehouse_id])) {
                        $price = $item_warehouse_prices[$item_id][$warehouse_id];
                    }
                    
                    return [
                        'warehouse_id' => $warehouse_row->warehouse_id,
                        'price' => $price,
                        'warehouse_description' => $warehouse_row->warehouse->description,
                        'stock' => $warehouse_row->stock,
                    ];
                }),
                'item_unit_types' => $row->item_unit_types->transform(function ($row1) use ($decimal_units, $configuration) {
                    /** @var ItemUnitType $row1 */
                    return $row1->getCollectionData($decimal_units);
                }),
                'item_warehouse_prices' => isset($item_warehouse_prices_full[$row->id]) ? $item_warehouse_prices_full[$row->id] : [],
                'frequent' => (bool) $row->frequent,
                'active' => (bool) $row->active,
                'unit_type_text' => isset($unit_types[$row->unit_type_id]) ? $unit_types[$row->unit_type_id] : null,
            ];
        });
    }
}
