<?php

namespace Modules\Inventory\Http\Resources;

use App\Models\Tenant\Configuration;
use App\Models\Tenant\Document;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\SaleNote;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Inventory\Models\Devolution;
use Modules\Inventory\Models\Inventory;

class ItemCostHistoryCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        $configuration = Configuration::select('stock_decimal', 'decimal_quantity')->first();
        $with_format = $request->get('with_format', false);
        //stock_decimal
        //decimal_quantity
        return $this->collection->transform(function ($row, $key) use ($configuration, $with_format) {
            $date = $row->date;
            $average_cost = $with_format ? number_format($row->average_cost, $configuration->decimal_quantity) : $row->average_cost;
            $stock = $with_format ? number_format($row->stock, $configuration->stock_decimal) : $row->stock;
            $balance = $with_format ? number_format($row->balance, $configuration->stock_decimal) : $row->balance;
            $quantity = $with_format ? number_format($row->quantity, $configuration->stock_decimal) : $row->quantity;
            if($row->inventory_kardexable_type == Inventory::class){
                $unit_price = $average_cost;
            }else{
                $unit_price = $with_format ? number_format($row->unit_price, $configuration->decimal_quantity) : $row->unit_price;
            }
            $to_return = [
                'id' => $row->id,
                'item_id' => $row->item_id,
                'unit_price' => $unit_price,
                'item_internal_id' => $row->item->internal_id,
                'item_description' => $row->item->description,
                'item_fulldescription' => ($row->item->internal_id) ? "{$row->item->internal_id} - {$row->item->description}" : $row->item->description,
                'warehouse_description' => $row->warehouse->description,
                'stock' => $stock,
                'average_cost' => $average_cost,
                'date' => $date,
                'input' => $row->type == 'in' ? $quantity : null,
                'output' => $row->type == 'out' ? $quantity : null,
                'balance' => $balance,
                'average_cost' => $average_cost,
                'created_at' => $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : null,
                'updated_at' => $row->updated_at ? $row->updated_at->format('Y-m-d H:i:s') : null,
            ];
            $to_return = array_merge($to_return, $this->formatData($row));
            return $to_return;
        });
    }

    public function formatData($row)
    {
        $inventory_kardexable_type = $row->inventory_kardexable_type;
        $type = $row->type;
        $number = '-';
        $anulation = false;
        $type_transaction = null;
        switch ($inventory_kardexable_type) {
            case Document::class:
                $type_transaction = $type == 'in' ? 'Anulación de venta' : 'Venta';
                $anulation = $type == 'out';
                $number = $row->inventoryKardexable->number_full;
                break;
            case Purchase::class:
                $type_transaction = $type == 'in' ? 'Compra' : 'Anulación de compra';
                $anulation = $type == 'out';
                $number = $row->inventoryKardexable->number_full;
                break;
            case SaleNote::class:
                $type_transaction = $type == 'in' ? 'Anulación de venta' : 'Venta';
                $anulation = $type == 'out';
                $number = $row->inventoryKardexable->number_full;
                break;
            case Inventory::class:
                $type_transaction = $row->inventoryKardexable->description;
                break;

            case Devolution::class:
                $type_transaction = $type == 'in' ? 'Anulación de devolución' : 'Devolución';
                $number = $row->inventoryKardexable->prefix . ' ' . $row->inventoryKardexable->id;
                break;

            default:
                $type_transaction = 'Otros';
        }

        return [
            'type_transaction' => $type_transaction,
            'number' => $number,
            'anulation' => $anulation,
        ];
    }
}
