<?php

namespace Modules\Inventory\Http\Resources;

use App\Models\Tenant\Item;
use App\Models\Tenant\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;
use Modules\ApiPeruDev\Data\ServiceData;

class ReportInventoryCollection extends ResourceCollection
{

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        $response_exchange_rate =(new ServiceData())->exchange(Carbon::now()->format('Y-m-d'));
        $itemsId = $this->collection->pluck('item_id');
        $w_id = $request->warehouse_id;
        $establishment_id = null;
        if($w_id !== 'all'){
            $establishment_id = Warehouse::find($w_id)->establishment_id;
        }
        $warehousesId = $this->collection->pluck('warehouse_id')->unique();
        $warehouses = Warehouse::whereIn('id', $warehousesId)->get()->keyBy('id');
        $exchange_rate = $response_exchange_rate['sale'];
        $connection = DB::connection('tenant');
        $quotation_items = $connection->table('quotation_items')
        ->select([
            'quotation_items.id',
            'quotation_items.quotation_id',
            'quotation_items.item_id',
            'quotation_items.quantity',
            'quotations.establishment_id',
            DB::raw("COALESCE(JSON_UNQUOTE(JSON_EXTRACT(quotation_items.item, '$.presentation.quantity_unit')), '1') as quantity_unit"),

        ])
        ->join('quotations', 'quotation_items.quotation_id', '=', 'quotations.id')
        ->leftJoin('sale_notes', 'quotations.id', '=', 'sale_notes.quotation_id')
        ->leftJoin('documents', 'quotations.id', '=', 'documents.quotation_id')
        ->whereNull('sale_notes.quotation_id')
        ->whereNull('documents.quotation_id')
        ->whereIn('quotations.state_type_id', ['01', '03', '05', '07'])
        ->when($establishment_id, function ($query) use ($establishment_id) {
            $query->where('quotations.establishment_id', $establishment_id);
        })
        ->get()->groupBy(['item_id','establishment_id']);

        $purchase_order_items = $connection->table('purchase_order_items')
        ->select([
            'purchase_order_items.id',
            'purchase_order_items.purchase_order_id', 
            'purchase_order_items.item_id',
            'purchase_order_items.quantity',
            'purchase_orders.establishment_id' // Asegurarte de incluir este campo
        ])
        ->whereIn('item_id', $itemsId)
        ->join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
        ->leftJoin('purchases', 'purchase_orders.id', '=', 'purchases.purchase_order_id')
        ->whereNull('purchases.id')
        ->whereIn('purchase_orders.state_type_id', ['01', '03', '05', '07'])
        ->when($establishment_id, function ($query) use ($establishment_id) {
            $query->where('purchase_orders.establishment_id', $establishment_id);
        })
        ->get()->groupBy(['item_id','establishment_id']);
        $currency = $request->currency;

        $itemsData = Item::whereIn('id', $itemsId)->get()->keyBy('id');
        return $this->collection->transform(function ($row, $key) use ($exchange_rate,$currency,$itemsData,$purchase_order_items,$quotation_items,$warehouses) {

            $item = $itemsData[$row->item_id];
            $sale_unit_price = $item->sale_unit_price;
            $purchase_unit_price = $item->purchase_unit_price;
            $currency_type_id = $item->currency_type_id;
            if($currency !== 'MIX'){
                if($currency_type_id == 'PEN' && $currency == 'USD'){
                    $sale_unit_price = number_format($sale_unit_price / $exchange_rate, 2, '.', '');
                    $purchase_unit_price = number_format($purchase_unit_price / $exchange_rate, 2, '.', '');
                }
                if($currency_type_id == 'USD' && $currency == 'PEN'){
                    $sale_unit_price = number_format($sale_unit_price * $exchange_rate, 2, '.', '');
                    $purchase_unit_price = number_format($purchase_unit_price * $exchange_rate, 2, '.', '');
                }
            }
            $establishment_id = $warehouses[$row->warehouse_id]->establishment_id;
            $out_stock = $quotation_items->get($row->item_id,collect([]))
                ->get($establishment_id,collect([]))->sum(function ($row) {
                    return $row->quantity * $row->quantity_unit;
                });
            $in_stock = $purchase_order_items->get($row->item_id,collect([]))
                ->get($establishment_id,collect([]))->sum('quantity');
            $stock = $row->stock;
            $future_stock = $in_stock + $stock - $out_stock;
            return [
                'out_stock' => $out_stock,
                'in_stock' => $in_stock,
                'future_stock' => $future_stock,
                'meter' => $item->meter,
                'laboratory' => optional($item->cat_digemid)->nom_titular,
                'num_reg_san' => optional($item->cat_digemid)->num_reg_san,
                'kardex_quantity' => (float) $row->kardex_quantity ?? 0,
                'lots_group' => $item->lots_group->transform(function ($row, $key) {
                    if (is_array($row)) {
                        $row = (object) $row;
                    }
                    return [
                        'id' => $row->id,
                        'code' => $row->code,
                        'quantity' => $row->quantity,
                        'date_of_due' => $row->date_of_due,
                    ];
                }),
                'currency_type_id' => $item->currency_type_id,
                'barcode' => $item->barcode,
                'internal_id' => $item->internal_id,
                'name' => $item->description,
                'description' => $item->name,
                'item_category_name' => optional($item->category)->name,
                'stock_min' => $item->stock_min,
                'stock' => $row->stock,
                'sale_unit_price' => $sale_unit_price,
                'purchase_unit_price' => $purchase_unit_price,
                'profit' => number_format($sale_unit_price - $purchase_unit_price, 2, '.', ''),
                'model' => $item->model,
                'brand_name' => $item->brand->name,
                'date_of_due' => optional($item->date_of_due)->format('d/m/Y'),
                'warehouse_name' => $row->warehouse->description
            ];
        });
    }
}
