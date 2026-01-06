<?php

namespace App\Traits;

use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\Document;
use App\Models\Tenant\DocumentItem;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\InitStock;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\PurchaseItem;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\SaleNoteItem;
use Carbon\Carbon;
use Modules\Inventory\Models\CostAverage;
use Modules\Inventory\Models\Inventory;
use Modules\Inventory\Models\InventoryKardex;
use Modules\Order\Models\OrderNote;
use Modules\Order\Models\OrderNoteItem;

/**
 * 
 */
trait CostAverageTrait
{

    /**
     * Get type doc
     * @param  string $documentType
     * @return int
     */
    public function average($purchase_item)
    {
        if ($purchase_item == null) return;
        $item_id = $purchase_item->item_id;
        $purchase = $purchase_item->purchase;
        //actualizo el stock
        $this->get_init_stock($item_id);
        $last_cost_average = CostAverage::where('item_id', $item_id)->orderBy('purchase_date', 'desc')->first();
        if ($last_cost_average) {
            $purchase_id = $last_cost_average->purchase_id;
        } else {
            $purchase_id = null;
        }
        if ($purchase_id) {

            $this->updatePurchases($purchase_id, $purchase->id, $item_id);
        }
        $this->cost_average($purchase_item);
    }
    public function average_item($item_id, $purchase_id = null)
    {
        $has_all_cost_average = $this->check_average($item_id);
        if ($has_all_cost_average) {
            return;
        }
        $last_date = null;
        $last_cost_average = CostAverage::where('item_id', $item_id)->orderBy('purchase_date', 'desc')->first();
        if ($last_cost_average) {
            $last_date = $last_cost_average->purchase_date;
        }
        $purchase_items = PurchaseItem::whereHas('purchase', function ($query) use ($last_date) {
            $query->where('state_type_id', '01');
            if ($last_date) {
                $query->where('date_of_issue', '>', $last_date);
            }
        })
            ->where('item_id', $item_id)
            ->get();
    }
    function check_average($item_id)
    {
        $last_purchase_with_item = PurchaseItem::where('item_id', $item_id)->orderBy('id', 'desc')->first();
        $purchase_id = $last_purchase_with_item->purchase->id;
        $last_cost_average = CostAverage::where('item_id', $item_id)->orderBy('purchase_date', 'desc')->first();
        return $last_cost_average->purchase_id == $purchase_id;
    }
    public function updatePurchases($last_purchase, $new_purchase, $item_id)
    {
        if (!is_numeric($last_purchase) || !is_numeric($new_purchase)) {
            return;
        }
        if ($last_purchase === $new_purchase || $last_purchase >= ($new_purchase - 1)) {
            return;
        }
        PurchaseItem::whereHas('purchase', function ($query) use ($last_purchase, $new_purchase) {
            $query->where('state_type_id', '01')->whereBetween('id', [$last_purchase + 1, $new_purchase - 1]);
        })
            ->where('item_id', $item_id)
            ->chunk(50, function ($purchase_items) {
                foreach ($purchase_items as $purchase_item) {
                    $this->cost_average($purchase_item);
                }
            });
    }

    function  cost_average($purchase_item)
    {
        $purchase_item->load("purchase");
        $purchase = $purchase_item->purchase;
        $date_of_purchase = $purchase->created_at;
        $item_id = $purchase_item->item_id;
        $item = $purchase_item->item;
        $quantity = $purchase_item->quantity;
        $unit_value = $purchase_item->unit_value;
        $first_day = $this->get_first_day($date_of_purchase);
        $init_stock = InitStock::where('item_id', $item_id)->where('init_date', $first_day)->first();

        // $first_day = Carbon::parse($first_day)->addDay();
        $quantity_rest = $this->sum_quantity($item_id, $init_stock->created_at, $purchase->created_at);
        $sum_quantity = $init_stock->stock + $quantity_rest;
        $total_stock = $init_stock->stock + $quantity_rest + $quantity;

        $last_cost_average = CostAverage::where('item_id', $item_id)->orderBy('purchase_date', 'desc')->first();
        $last_average  = 0;
        if ($last_cost_average) {
            $last_average = $last_cost_average->average;
        } else {
            $last_average = $item->purchase_unit_price;
        }
        $current_cost_total = $last_average * $sum_quantity;
        $purchase_cost_total = $quantity * $unit_value;
        $cost_total = $current_cost_total + $purchase_cost_total;
        $new_average = $cost_total / $total_stock;

        $cost_average = CostAverage::create([
            'average' => $new_average,
            'item_id' => $item_id,
            'purchase_id' => $purchase->id,
            'purchase_date' => $date_of_purchase,
            'purchase_cost' => $unit_value,
            'total_purchase_cost' => $quantity * $unit_value,
            'purchase_quantity' => $quantity,
            'stock_without_purchase' => $sum_quantity,
        ]);
        $cost_average->save();
    }
    function sum_quantity($item_id, $date, $last_date)
    {

        if (gettype($last_date) == 'string') {
            $last_date = Carbon::parse($last_date);
        }
        if (gettype($date) == 'string') {
            $date = Carbon::parse($date);
        }
        //sale_notes
        $sum_sale_notes = SaleNoteItem::where('item_id', $item_id)
            ->whereHas('sale_note', function ($query) use ($date, $last_date) {
                $query->where('state_type_id', '01')
                    ->whereNull('order_note_id')
                    ->whereBetween('date_of_issue', [$date, $last_date]);
            })
            ->sum('quantity');
        //order_notes
        $sum_order_notes = OrderNoteItem::where('item_id', $item_id)
            ->whereHas('order_note', function ($query) use ($date, $last_date) {
                $query->where('state_type_id', '01')
                    ->whereBetween('date_of_issue', [$date, $last_date]);
            })
            ->sum('quantity');
        //documents
        $sum_documents = DocumentItem::where('item_id', $item_id)
            ->whereHas('document', function ($query) use ($date, $last_date) {
                $query->where('state_type_id', '01')
                    ->whereNull('order_note_id')
                    ->whereNull('sale_note_id')
                    ->whereBetween('date_of_issue', [$date, $last_date]);
            })
            ->sum('quantity');
        //purchase
        $sum_purchases = PurchaseItem::where('item_id', $item_id)
            ->whereHas('purchase', function ($query) use ($date, $last_date) {
                $query->where('state_type_id', '01')
                    ->whereBetween('date_of_issue', [$date, $last_date]);
            })
            ->sum('quantity');

        
        $sum = $sum_sale_notes + $sum_order_notes + $sum_documents - $sum_purchases;

        return $sum;
    }
    function get_initial_stock($item_id)
    {
        $inventory = Inventory::where('item_id', $item_id)
            ->where('description', 'Stock Inicial')
            ->first();
        if ($inventory) {
            return $inventory->stock;
        }
        return 0;
    }
    function get_init_stock($item_id)
    {
        $lastStock = InitStock::where('item_id', $item_id);
        $lastStock = $lastStock->whereNull("warehouse_id");
        $lastStock =   $lastStock->latest('init_date')->first();
        $lastDate = null;
        if ($lastStock) {
            $lastDate = $lastStock->init_date;
        }
        $stocks = [];
        if (!isset($lastDate)) {
            $initial_stock = $this->get_initial_stock($item_id);
            $init_date = InventoryKardex::where('item_id', $item_id);
            $init_date = $init_date->first();
            if (!isset($init_date)) {
                return;
            }
            $date = $init_date->date_of_issue;
            $init_stock = $init_date->quantity;

            $result = $this->get_all_dates($date);
            if (count($result) != 0) {
                foreach ($result as $key => $date) {
                    $last_date = $this->get_last_day($date);
                    $sum = $this->sum_quantity($item_id, $date, $last_date);
                    if ($key != 0) {
                        $stocks[$date] = $stocks[$result[$key - 1]] + $sum;
                        $initStock = new InitStock([
                            'item_id' => $item_id,
                            'warehouse_id' => null,
                            'init_date' => $date,
                            'stock' => floatval($stocks[$result[$key - 1]]),
                        ]);
                        $initStock->save();
                    } else {
                        $stocks[$date] = floatval($sum);
                        $initStock = new InitStock([
                            'item_id' => $item_id,
                            'warehouse_id' => null,
                            'init_date' => $date,
                            'stock' => floatval($init_stock),
                        ]);
                        $initStock->save();
                    }
                }
            }
        } else {
            $result = $this->get_all_dates($lastDate->format("Y-m-d"));
            if (count($result) != 0) {
                foreach ($result as $key => $date) {
                    $last_date = $this->get_last_day($date);
                    $sum = $this->sum_quantity($item_id, $date, $last_date);
                    if ($key != 0) {
                        $stocks[$date] = $stocks[$result[$key - 1]] + $sum;
                        $initStock = new InitStock([
                            'item_id' => $item_id,
                            'warehouse_id' => null,
                            'init_date' => $date,
                            'stock' => floatval($stocks[$result[$key - 1]]),
                        ]);
                        $initStock->save();
                    } else {
                        $stocks[$date] = floatval($sum) + floatval($lastStock->stock);
                    }
                }
            }
        }
    }
    function get_all_dates($init_date, $addMonth = false)
    {
        $start_date = $init_date;
        $today = now();
        $date = Carbon::createFromFormat('Y-m-d', $start_date);
        $first_days = [];
        while ($date <= $today) {

            $date = $date->firstOfMonth();
            if ($addMonth) {
                $date = $date
                    ->addMonth();
            }
            $first_days[] = $date->format('Y-m-d');
            $date->addMonthNoOverflow();
        }

        return $first_days;
    }

    function get_last_day($date)
    {

        $carbonFecha = Carbon::createFromFormat('Y-m-d', $date);

        $ultimoDiaMes = $carbonFecha->endOfMonth();

        return $ultimoDiaMes->format('Y-m-d');
    }

    function get_first_day($date)
    {

        $primerDiaMes = $date->firstOfMonth();
        return $primerDiaMes->format('Y-m-d');
    }
}
