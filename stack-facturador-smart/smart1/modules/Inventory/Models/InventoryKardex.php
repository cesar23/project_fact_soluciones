<?php

namespace Modules\Inventory\Models;

use App\Models\Tenant\AverageHistory;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Dispatch;
use App\Models\Tenant\Document;
use App\Models\Tenant\Item;
use App\Models\Tenant\ItemWarehousePrice;
use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\PurchaseItem;
use App\Models\Tenant\PurchaseSettlement;
use App\Models\Tenant\SaleNote;
use Modules\BusinessTurn\Models\BusinessTurn;
use Modules\Item\Models\ItemProperty;
use Modules\Order\Models\OrderNote;

/**
 * Modules\Inventory\Models\InventoryKardex
 *
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $inventory_kardexable
 * @property-read Item $item
 * @property-read \Modules\Inventory\Models\Warehouse $warehouse
 * @method static \Illuminate\Database\Eloquent\Builder|InventoryKardex newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InventoryKardex newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InventoryKardex query()
 * @mixin ModelTenant
 */
class InventoryKardex extends ModelTenant
{
    protected $table = 'inventory_kardex';

    protected $fillable = [
        'date_of_issue',
        'item_id',
        'inventory_kardexable_id',
        'inventory_kardexable_type',
        'warehouse_id',
        'quantity',
    ];

    public function inventory_kardexable()
    {
        return $this->morphTo();
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function getCollectionData()
    {
        $data = [
            'id' => $this->id
        ];

        return $data;
    }

    /**
     * @return ItemWarehousePrice|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|mixed|object|null
     */
    public function getItemWarehousePriceModel()
    {
        return ItemWarehousePrice::where(
            [
                'warehouse_id' => $this->warehouse_id,
                'item_id' => $this->item_id,
            ]
        )->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|mixed|Warehouse|Warehouse[]|null
     */
    public function getWarehouseModel()
    {
        return Warehouse::find($this->warehouse_id);
    }

    /**
     * Obtener notas de venta asociadas a documento
     *
     * @return string
     */
    public function getSaleNoteAsoc($inventory_kardexable)
    {
        $sale_note_asoc = "-";

        if (isset($inventory_kardexable->sale_note_id)) {
            $sale_note_asoc = optional($inventory_kardexable)->sale_note->number_full;
        }

        if (isset($inventory_kardexable->sale_notes_relateds)) {
            $data = [];

            foreach ($inventory_kardexable->sale_notes_relateds as $sale_note) {
                if (isset($sale_note->items)) {

                    $exist_sale_note = collect($sale_note->items)->where('item_id', $this->item_id)->first();

                    if ($exist_sale_note) $data[] = $sale_note->number_full;
                }
            }

            // $sale_note_asoc = collect($inventory_kardexable->sale_notes_relateds)->implode('number_full', ', ');
            $sale_note_asoc = count($data) > 0 ? implode(', ', $data) : '-';
        }

        return $sale_note_asoc;
    }

    /**
     * @param $balance
     * @return array
     */
    public function getKardexReportCollection(&$balance)
    {

        $request = request()->all();
        $clothes = BusinessTurn::isClothesShoes();
        $warehouse_id_request = isset($request['warehouse_id']) ? $request['warehouse_id'] : null;
        $models = [
            Document::class,
            Purchase::class,
            SaleNote::class,
            Inventory::class,
            OrderNote::class,
            Devolution::class,
            Dispatch::class,
            PurchaseSettlement::class,
        ];
        $configuration = Configuration::first();
        $item = $this->item;
        $warehouseprice = $this->getItemWarehousePriceModel();
        $warehouse = $this->getWarehouseModel();
        $price = '-';
        $warehouseName = '';
        if (!empty($warehouseprice)) {
            $price = $warehouseprice->getPrice();
        }
        if (!empty($warehouse)) {
            $warehouseName = $warehouse->description;
        }

        $data = [
            'id' => $this->id,
            'item_name' => $item->description,
            'date_time' => $this->created_at->format('Y-m-d H:i:s'),
            'date_of_issue' => '-',
            'can_delete' => auth()->user()->type === 'superadmin',
            'number' => '-',
            'sale_note_asoc' => '-',
            'order_note_asoc' => '-',
            'doc_asoc' => '-',
            'sizes' => [],
            // 'inventory_kardexable_id' => $this->inventory_kardexable_id,
            'inventory_kardexable_type' => $this->inventory_kardexable_type,
            // 'item' => $item->getCollectionData(),
            'item_warehouse_price' => $price,
            'warehouse' => $warehouseName,
        ];
        $inventory_kardexable = $this->inventory_kardexable;
        $qty = number_format($this->quantity, 2, '.', '');
        $input_set = ($qty > 0) ? $qty : "-";
        $output_set = ($qty < 0) ? $qty : "-";
        $data['input'] = $input_set;
        $data['output'] = $output_set;
        switch ($this->inventory_kardexable_type) {

            case $models[0]: //venta
                $sale_note_discounted_stock = (isset($inventory_kardexable->sale_note_id) && !$configuration->emit_prepayment_document_from_sale_note ) || $inventory_kardexable->has_prepayment;
                
                $cpe_input = ($qty > 0) ? ($sale_note_discounted_stock || isset($inventory_kardexable->order_note_id) || isset($inventory_kardexable->sale_notes_relateds) ? "-" : $qty) : "-";
                $cpe_output = ($qty < 0) ? ($sale_note_discounted_stock || isset($inventory_kardexable->order_note_id) || isset($inventory_kardexable->sale_notes_relateds) ? "-" : $qty) : "-";

                $cpe_discounted_stock = false;
                $cpe_doc_asoc = isset($inventory_kardexable->note) && isset($inventory_kardexable->note->affected_document) ? $inventory_kardexable->note->affected_document->getNumberFullAttribute() : '-';

                if (isset($inventory_kardexable->dispatch)) {
                    if ($inventory_kardexable->dispatch->transfer_reason_type->discount_stock) {
                        $cpe_output = '-';
                        $cpe_discounted_stock = true;
                    }
                    $cpe_doc_asoc = ($cpe_doc_asoc == '-') ? $inventory_kardexable->dispatch->number_full : $cpe_doc_asoc . ' | ' . $inventory_kardexable->dispatch->number_full;
                }
                $discounted_order_note = false;
                if (isset($inventory_kardexable->order_note_id) && $inventory_kardexable->order_note_id) {
                    $order_note = OrderNote::find($inventory_kardexable->order_note_id);
                    $discounted_order_note = (bool) $order_note->discounted_stock;
                }

                $doc_balance = ($sale_note_discounted_stock || $discounted_order_note || $cpe_discounted_stock || isset($inventory_kardexable->sale_notes_relateds)) ? $balance += 0 : $balance += $qty;

                $data['input'] = $cpe_input;
                $data['output'] = $cpe_output;
                $data['balance'] = $doc_balance;
                $data['number'] = optional($inventory_kardexable)->series . '-' . optional($inventory_kardexable)->number;
                $data['type_transaction'] = ($qty < 0) ? "Venta" : "Anulación Venta";
                $data['date_of_issue'] = isset($inventory_kardexable->date_of_issue) ? $inventory_kardexable->date_of_issue->format('Y-m-d') : '';
                // $data['sale_note_asoc'] = isset($inventory_kardexable->sale_note_id) ? optional($inventory_kardexable)->sale_note->number_full : "-";
                $data['sale_note_asoc'] = $this->getSaleNoteAsoc($inventory_kardexable);
                $data['doc_asoc'] = $cpe_doc_asoc;
                $data['order_note_asoc'] = isset($inventory_kardexable->order_note_id) ? optional($inventory_kardexable)->order_note->number_full : "-";
                if($clothes){
                    foreach($inventory_kardexable->items as $item){
                        $item_inner = $item->item;
                        $sizes = isset($item_inner->sizes_selected) ? $item_inner->sizes_selected : [];
                        foreach($sizes as $size){
                            $data['sizes'][] = [
                                'size' => $size->size,
                                'qty' => $size->qty
                            ];
                        }
                    }
                }
                if (!isset($this->item_id) || empty($this->item_id)) {
                    $data['chassis'] = null;
                    $data['marca'] = null;
                    $data['modelo'] = null;
                    $data['color'] = null;
                    $data['motor'] = null;
                    $data['anio'] = null;
                } else {
                    $item_id = $this->item_id;
                    $has_sale = 1;
                    $warehouse_id = "all";
                    $atributos = ItemProperty::where('has_sale', $has_sale)->where('item_id', $item_id)->orderBy('id')->first();
                    if ($atributos) {
                        $data['chassis'] = $atributos->chassis;
                        $data['marca'] = $atributos->attribute;
                        $data['modelo'] = $atributos->attribute2;
                        $data['color'] = $atributos->attribute3;
                        $data['motor'] = $atributos->attribute4;
                        $data['anio'] = $atributos->attribute5;
                    } else {
                        $data['chassis'] = null;
                        $data['marca'] = null;
                        $data['modelo'] = null;
                        $data['color'] = null;
                        $data['motor'] = null;
                        $data['anio'] = null;
                    }
                }
                break;

            case $models[1]:
                $license = optional($inventory_kardexable->purchase_license)->license;
                $responsible = optional($inventory_kardexable->purchase_responsible)->name;
                $unit_price = 0;
                $item_purchase = $inventory_kardexable->items()->where('item_id', $this->item_id)->first();
                if ($item_purchase) {
                    $unit_price = $item_purchase->unit_price;
                }
                $data['balance'] = $balance += $qty;
                $data['unit_price'] = $unit_price;
                $data['license'] = $license;
                $data['responsible'] = $responsible;
                $data['number'] = optional($inventory_kardexable)->series . '-' . optional($inventory_kardexable)->number;
                $data['type_transaction'] = ($qty < 0) ? "Anulación Compra" : "Compra";
                $data['date_of_issue'] = isset($inventory_kardexable->date_of_issue) ? $inventory_kardexable->date_of_issue->format('Y-m-d') : '';
                break;
            case $models[2]: // Nota de venta
                $discounted_order_note = false;
                if (isset($inventory_kardexable->order_note_id) && $inventory_kardexable->order_note_id) {
                    $order_note = OrderNote::find($inventory_kardexable->order_note_id);
                    $discounted_order_note = (bool) $order_note->discounted_stock;
                }
                if ($discounted_order_note) {
                    $nv_balance = $balance += 0;
                    $data['output'] = '-';
                    $data['order_note_asoc'] = optional($inventory_kardexable)->order_note->number_full;
                } else {
                    $nv_balance = $balance += $qty;
                }

                $data['balance'] = $nv_balance;
                // $data['balance'] = $balance += $qty;
                $data['number'] = optional($inventory_kardexable)->number_full;
                $data['type_transaction'] = ($qty < 0) ? "Nota de venta" : "Anulación Nota de venta";
                // $data['type_transaction'] = "Nota de venta";
                $data['date_of_issue'] = isset($inventory_kardexable->date_of_issue) ? $inventory_kardexable->date_of_issue->format('Y-m-d') : '';
                if($clothes){
                    foreach($inventory_kardexable->items as $item){
                        $item_inner = $item->item;
                        $sizes = isset($item_inner->sizes_selected) ? $item_inner->sizes_selected : [];
                        foreach($sizes as $size){
                            $data['sizes'][] = [
                                'size' => $size->size,
                                'qty' => $size->qty
                            ];
                        }
                    }
                }
                break;
            case $models[3]: {
                    $transaction = '';
                    $input = '';
                    $output = '';
                
                    if (!$inventory_kardexable->type) {
                        $transaction = InventoryTransaction::findOrFail($inventory_kardexable->inventory_transaction_id);
                    }

                    if ($inventory_kardexable->type != null) {
                        $input = ($inventory_kardexable->type == 1) ? $qty : "-";
                    } else {
                        $input = ($transaction->type == 'input') ? $qty : "-";
                    }
                    if ($inventory_kardexable->type != null) {
                        $output = ($inventory_kardexable->type == 2 || $inventory_kardexable->type == 3) ? $qty : "-";
                    } else {
                        $output = ($transaction->type == 'output') ? $qty : "-";
                    }

                    $user = auth()->user();
                    $data['balance'] = $balance += $qty;
                    $data['type_transaction'] = $inventory_kardexable->description;
                    $data['date_of_issue'] = isset($inventory_kardexable->date_of_issue) ? $inventory_kardexable->date_of_issue->format('Y-m-d') : '';
                    $data['guide_id'] = null;
                    if ($inventory_kardexable->inventory_reference) {
                        $data['reference'] = $inventory_kardexable->inventory_reference->description;
                    }

                    $guide = Guide::query()->where('id', $inventory_kardexable->guide_id)->first();
                    if ($guide) {
                        $data['number'] = $guide->series . '-' . $guide->number;
                        $data['date_of_issue'] = $guide->date_of_issue->format('Y-m-d');
                        $data['guide_id'] = $guide->id;
                    }

                    $inventory_transfer = InventoryTransfer::query()->where('id', $inventory_kardexable->inventories_transfer_id)->first();
                    if ($inventory_transfer) {
                        $data['number'] = $inventory_transfer->series . '-' . $inventory_transfer->number;
                        $data['date_of_issue'] = $inventory_transfer->created_at->format('Y-m-d');
                    }
                    if ($warehouse_id_request && $warehouse_id_request != 'all') {
                        if ($warehouse_id_request == $inventory_kardexable->warehouse_destination_id) {
                            $data['input'] =  $output;
                            $data['output'] = $input;
                        } else {
                            $data['input'] = $input;
                            $data['output'] = $output;
                        }
                    } else {
                        if($output != "-"){
                            if($output > 0){
                                $data['input'] = $output;
                                $data['output'] = $input;
                            }else{
                                $data['input'] = $input;
                                $data['output'] = $output;
                            }
                        }
                        if($input != "-"){
                            if($input > 0){
                                $data['input'] = $output;
                                $data['output'] = $input;
                            }else{
                                $data['input'] = $input;
                                $data['output'] = $output;
                            }
                        }
                        // if ($inventory_kardexable->warehouse_destination_id == $user->establishment_id) {
                        //     $data['input'] =  $output;
                        //     $data['output'] = $input;
                        // } else {
                        //     $data['input'] = $input;
                        //     $data['output'] = $output;
                        // }
                    }


                    break;
                }
            case $models[4]:
                $data['balance'] = $balance += $qty;
                $data['number'] = optional($inventory_kardexable)->prefix . '-' . optional($inventory_kardexable)->id;
                $data['type_transaction'] = ($qty < 0) ? "Pedido" : "Anulación Pedido";
                $data['date_of_issue'] = isset($inventory_kardexable->date_of_issue) ? $inventory_kardexable->date_of_issue->format('Y-m-d') : '';
                break;
            case $models[5]: // Devolution
                $data['balance'] = $balance += $qty;
                $data['number'] = optional($inventory_kardexable)->number_full;
                $data['type_transaction'] = "Devolución";
                $data['date_of_issue'] = isset($inventory_kardexable->date_of_issue) ? $inventory_kardexable->date_of_issue->format('Y-m-d') : '';
                break;
            case $models[6]: // Dispatch
                $move_stock = $inventory_kardexable->state_type_id == "55" || $inventory_kardexable->state_type_id == "56";
                $document = $inventory_kardexable->reference_document;
                if ($document) {
                    $move_stock = $document->no_stock;
                }
                $sale_note = $inventory_kardexable->sale_note;  
                if ($sale_note) {
                    $move_stock = $sale_note->no_stock;
                }
                $dont_move_stock = (isset($inventory_kardexable->reference_sale_note_id) || isset($inventory_kardexable->reference_order_note_id) || isset($inventory_kardexable->reference_document_id)) && !$move_stock;
                $data['input'] = ($qty > 0) ? ($dont_move_stock ? "-" : $qty) : "-";
                $data['output'] = ($qty < 0) ? ($dont_move_stock ? "-" : $qty) : "-";
                $data['balance'] =$dont_move_stock ? $balance += 0 : $balance += $qty;
                $data['number'] = optional($inventory_kardexable)->number_full;
                $data['type_transaction'] = isset($inventory_kardexable->transfer_reason_type->description) ? $inventory_kardexable->transfer_reason_type->description : '';
                $data['date_of_issue'] = isset($inventory_kardexable->date_of_issue) ? $inventory_kardexable->date_of_issue->format('Y-m-d') : '';
                $data['sale_note_asoc'] = isset($inventory_kardexable->reference_sale_note_id) ? optional($inventory_kardexable)->sale_note->number_full : "-";
                $data['order_note_asoc'] = isset($inventory_kardexable->reference_order_note_id) ? optional($inventory_kardexable)->order_note->number_full : "-";
                $data['doc_asoc'] = isset($inventory_kardexable->reference_document_id) ? $inventory_kardexable->reference_document->getNumberFullAttribute() : '-';
                break;
            case $models[7]: // liquidacion de compra

                $data['balance'] = $balance += $qty;
                $data['number'] = optional($inventory_kardexable)->series . '-' . optional($inventory_kardexable)->number;
                $data['type_transaction'] = ($qty < 0) ? "Anulación Liquidacion Compra" : "Liquidacion Compra";
                $data['date_of_issue'] = isset($inventory_kardexable->date_of_issue) ? $inventory_kardexable->date_of_issue->format('Y-m-d') : '';
                break;
        }
        if (isset($inventory_kardexable->customer)) {
            $data['person_name'] = $inventory_kardexable->customer->name;
            $data['person_number'] = $inventory_kardexable->customer->number;
        }
        if (isset($inventory_kardexable->supplier)) {
            $data['person_name'] = $inventory_kardexable->supplier->name;
            $data['person_number'] = $inventory_kardexable->supplier->number;
        }
        $data['date_of_register'] = isset($inventory_kardexable->date_of_issue) ? $inventory_kardexable->date_of_issue->format('Y-m-d') : '';
        $decimalRound = 6; // Cantidad de decimales a aproximar
        $data['balance'] = $data['balance'] ? round($data['balance'], $decimalRound) : 0;
        return $data;
    }

    public function getKardexReportCollection2(&$balance)
    {

        $models = [
            Purchase::class,
            Document::class,
            SaleNote::class,
            Inventory::class,
            OrderNote::class,
            Devolution::class,
            Dispatch::class
        ];

        $item = $this->item;

        $warehouseprice = $this->getItemWarehousePriceModel();

        $warehouse = $this->getWarehouseModel();
        $price = '-';
        $doc_balance = "";
        $warehouseName = '';
        if (!empty($warehouseprice)) {
            $price = $warehouseprice->getPrice();
        }
        if (!empty($warehouse)) {
            $warehouseName = $warehouse->description;
        }
        $data = [
            'id' => $this->id,
            'item_name' => $item->description,
            'item_id' => $item->id,
            'date_time' => $this->created_at->format('Y-m-d H:i:s'),
            'purchase_id' => null,
            'document_id' => null,
            'date_of_issue' => '-',
            'number' => '-',
            'sale_note_asoc' => '-',
            'order_note_asoc' => '-',
            'doc_asoc' => '-',
            'purchase_cost' => 0,
            'total_purchase_cost' => 0,
            'sales_cost' => 0,
            'total_sales' => 0,
            'quantity_balance' => 0,
            'price_balance' => 0,
            'total_balance' => 0,
            'sale_note_id' => null,
            'inventory_kardexable_type' => $this->inventory_kardexable_type,
            'item_warehouse_price' => $price,
            'warehouse' => $warehouseName,
        ];
        $inventory_kardexable = $this->inventory_kardexable;
        if ($inventory_kardexable != null) {


            $qty = $this->quantity;
            $input_set = ($qty > 0) ? $qty : "-";
            $output_set = ($qty < 0) ? $qty : "-";
            $data['input'] = $input_set;
            $data['output'] = $output_set;

            switch ($this->inventory_kardexable_type) {
                case $models[0]: //compra
                    // session(['balance_item' => $data['balance']]);
                    // return [session('balance_item'),$this->quantity];
                    session(['balance_item' => session('balance_item') + $this->quantity]);

                    $data['balance'] = session('balance_item');
                    $data['number'] = optional($inventory_kardexable)->series . '-' . optional($inventory_kardexable)->number;
                    $data['type_transaction'] = ($qty < 0) ? "Anulación Compra" : "Compra";
                    $data['date_of_issue'] = isset($inventory_kardexable->date_of_issue) ? $inventory_kardexable->date_of_issue->format('Y-m-d') : '';
                    $data['purchase_cost'] = round($this->getPricePurchase($inventory_kardexable->id, $item->id)->unit_price, 2);
                    $data['total_purchase_cost'] = round($this->getPricePurchase($inventory_kardexable->id, $item->id)->unit_price * $this->quantity, 2);
                    $data['quantity_balance'] = session('balance_item');
                    if (session('cost_purchase') == 0) {
                        $data['price_balance'] = round($this->getPricePurchase($inventory_kardexable->id, $item->id)->unit_price, 2);
                    } else {
                        $data['price_balance'] = (session('balance_item') > 0) ? round((($this->getPricePurchase($inventory_kardexable->id, $item->id)->unit_price * $this->quantity) + session('total_saldo')) / session('balance_item'), 2) : 0;
                    }
                    if (session('cost_purchase') == 0) {
                        session(['cost_purchase' => round($this->getPricePurchase($inventory_kardexable->id, $item->id)->unit_price, 2)]);
                        $data['total_balance'] = round($this->getPricePurchase($inventory_kardexable->id, $item->id)->unit_price * $this->quantity, 2);
                    } else {
                        $data['total_balance'] = round(($this->getPricePurchase($inventory_kardexable->id, $item->id)->unit_price * $this->quantity) + session('total_saldo'), 2);
                        session(['cost_purchase' => ($balance > 0) ? round((($this->getPricePurchase($inventory_kardexable->id, $item->id)->unit_price * $this->quantity) + session('total_saldo')) / session('balance_item'), 2) : 0]);
                    }
                    session(['total_saldo' => $data['total_balance']]);
                    session(['cost_purchase' =>  $data['price_balance']]);
                    $this->save_average_history(null, null, $inventory_kardexable->id, $data['purchase_cost'], $data['total_purchase_cost'], $data['price_balance'], $data['input'], $data['output'], $data['balance'], 'Compra', $data['total_balance'], $data['total_sales'], $data['sales_cost'], $data['number']);

                    break;
                case $models[1]: //venta

                    $total_sales = round(abs(session('cost_purchase') * $this->quantity), 2);
                    $total_saldo =  round(session('total_saldo') - $total_sales, 2);
                    $cpe_input = ($qty > 0) ? (isset($inventory_kardexable->sale_note_id) || isset($inventory_kardexable->order_note_id) ? "-" : $qty) : "-";
                    $cpe_output = ($qty < 0) ? (isset($inventory_kardexable->sale_note_id) || isset($inventory_kardexable->order_note_id) ? "-" : $qty) : "-";
                    $cpe_discounted_stock = false;
                    $cpe_doc_asoc = isset($inventory_kardexable->note) && isset($inventory_kardexable->note->affected_document) ? $inventory_kardexable->note->affected_document->getNumberFullAttribute() : '-';
                    if (isset($inventory_kardexable->dispatch)) {
                        if ($inventory_kardexable->dispatch->transfer_reason_type->discount_stock) {
                            $cpe_output = '-';
                            $cpe_discounted_stock = true;
                        }
                        $cpe_doc_asoc = ($cpe_doc_asoc == '-') ? $inventory_kardexable->dispatch->number_full : $cpe_doc_asoc . ' | ' . $inventory_kardexable->dispatch->number_full;
                    }
                    $doc_balance = (isset($inventory_kardexable->sale_note_id) || isset($inventory_kardexable->order_note_id) || $cpe_discounted_stock) ? $balance += 0 : $balance += $qty;
                    $data['input'] = $cpe_input;
                    $data['output'] = $cpe_output;
                    $saldo_anterior = session('balance_item');
                    //return [$saldo_anterior,$data['output']];
                    if ($qty < 0) {
                        session(['balance_item' => session('balance_item') - abs($data['output'])]);
                        $data['balance'] = session('balance_item');
                    } else if ($qty > 0) {
                        session(['balance_item' => session('balance_item') + $data['input']]);
                        $data['balance'] = session('balance_item');
                    }
                    //session(['balance_item' => $data['balance']]);
                    $data['number'] = optional($inventory_kardexable)->series . '-' . optional($inventory_kardexable)->number;
                    $data['type_transaction'] = ($qty < 0) ? "Venta" : "Anulación Venta";
                    $data['date_of_issue'] = isset($inventory_kardexable->date_of_issue) ? $inventory_kardexable->date_of_issue->format('Y-m-d') : '';
                    $data['sale_note_asoc'] = isset($inventory_kardexable->sale_note_id) ? optional($inventory_kardexable)->sale_note->number_full : "-";
                    $data['doc_asoc'] = $cpe_doc_asoc;
                    $data['order_note_asoc'] = isset($inventory_kardexable->order_note_id) ? optional($inventory_kardexable)->order_note->number_full : "-";
                    $data['sales_cost'] =  session('cost_purchase');
                    $data['total_sales'] = round(session('cost_purchase') * abs($this->quantity), 2);
                    $data['quantity_balance'] = $data['balance'];
                    //if($this->id==173){
                    $total_sales = round(abs(session('cost_purchase') * $this->quantity), 2);
                    $total_saldo =  round(session('total_saldo') - $total_sales, 2);
                    //  return [$cpe_output,session('balance'),$total_saldo,$data['quantity_balance'],$total_saldo/$data['quantity_balance']];
                    //}
                    $data['price_balance'] = (session('balance_item') > 0) ? round($total_saldo / $data['quantity_balance'], 2) : 0;
                    $data['total_balance'] = round(session('total_saldo') - session('cost_purchase') * abs($this->quantity), 2);
                    session(['total_saldo' => round(session('total_saldo') - session('cost_purchase') * abs($this->quantity), 2)]);
                    // if($data['number']=="F001-451665"){
                    //}
                    $this->save_average_history($inventory_kardexable->id, null, null, $data['purchase_cost'], $data['total_purchase_cost'], $data['price_balance'], $data['input'], $data['output'], $data['balance'], 'Venta', $data['total_balance'], $data['total_sales'], $data['sales_cost'], $data['number']);
                    break;

                case $models[2]: // Nota de venta
                    $total_sales = round(abs(session('cost_purchase') * $this->quantity), 2);
                    $total_saldo =  round(session('total_saldo') - $total_sales, 2);
                    session(['balance_item' => session('balance_item') - abs($data['output'])]);
                    $data['sale_note_id'] = null;
                    $data['balance'] = session('balance_item');
                    //session(['balance_item' => $data['balance']]);
                    $data['number'] = optional($inventory_kardexable)->number_full;
                    $data['type_transaction'] = "Nota de venta";
                    $data['date_of_issue'] = isset($inventory_kardexable->date_of_issue) ? $inventory_kardexable->date_of_issue->format('Y-m-d') : '';
                    $data['quantity_balance'] = session('balance_item');
                    $data['sales_cost'] =  session('cost_purchase');
                    $data['total_sales'] = round(session('cost_purchase') * abs($this->quantity), 2);
                    $data['price_balance'] = (session('balance_item') > 0) ? round($total_saldo / session('balance_item'), 2) : 0;;
                    $data['total_balance'] = round(session('total_saldo') - session('cost_purchase') * abs($this->quantity), 2);
                    session(['total_saldo' => round(session('total_saldo') - session('cost_purchase') * abs($this->quantity), 2)]);
                    $this->save_average_history(null, $inventory_kardexable->id, null, $data['purchase_cost'], $data['total_purchase_cost'], $data['price_balance'], $data['input'], $data['output'], $data['balance'], 'Venta', $data['total_balance'], $data['total_sales'], $data['sales_cost'], $data['number']);
                    break;
                case $models[3]: {
                        $transaction = '';
                        $input = '';
                        $output = '';
                        if (!$inventory_kardexable->type) {
                            $transaction = InventoryTransaction::findOrFail($inventory_kardexable->inventory_transaction_id);
                        }
                        if ($inventory_kardexable->type != null) {
                            $input = ($inventory_kardexable->type == 1) ? $qty : "-";
                        } else {
                            $input = ($transaction->type == 'input') ? $qty : "-";
                        }
                        if ($inventory_kardexable->type != null) {
                            $output = ($inventory_kardexable->type == 2 || $inventory_kardexable->type == 3) ? $qty : "-";
                        } else {
                            $output = ($transaction->type == 'output') ? $qty : "-";
                        }
                        $user = auth()->user();
                        $data['quantity_balance'] = 0;
                        session(['balance_item' => $data['quantity_balance']]);

                        $data['balance'] = session('balance_item');

                        $data['total_cost'] = 0;
                        $data['type_transaction'] = $inventory_kardexable->description;
                        if ($inventory_kardexable->warehouse_destination_id === $user->establishment_id) {
                            $data['input'] = $output;
                            $data['output'] = $input;
                        } else {
                            $data['input'] = $input;
                            $data['output'] = $output;
                        }
                        break;
                    }
                case $models[4]:
                    $data['balance'] = session('balance_item');
                    session(['balance_item' => $data['balance']]);
                    $data['number'] = optional($inventory_kardexable)->prefix . '-' . optional($inventory_kardexable)->id;
                    $data['type_transaction'] = ($qty < 0) ? "Pedido" : "Anulación Pedido";
                    $data['date_of_issue'] = isset($inventory_kardexable->date_of_issue) ? $inventory_kardexable->date_of_issue->format('Y-m-d') : '';

                    break;
                case $models[5]: // Devolution
                    session(['balance_item' => session('balance_item') + $data['input']]);
                    $data['balance'] = session('balance_item');
                    $data['number'] = optional($inventory_kardexable)->number_full;
                    $data['type_transaction'] = "Devolución";
                    $data['date_of_issue'] = isset($inventory_kardexable->date_of_issue) ? $inventory_kardexable->date_of_issue->format('Y-m-d') : '';
                    break;
                case $models[6]: // Dispatch
                    $data['input'] = ($qty > 0) ? (isset($inventory_kardexable->reference_sale_note_id) || isset($inventory_kardexable->reference_order_note_id) || isset($inventory_kardexable->reference_document_id) ? "-" : $qty) : "-";
                    $data['output'] = ($qty < 0) ? (isset($inventory_kardexable->reference_sale_note_id) || isset($inventory_kardexable->reference_order_note_id) || isset($inventory_kardexable->reference_document_id) ? "-" : $qty) : "-";
                    $data['balance'] = (isset($inventory_kardexable->reference_sale_note_id) || isset($inventory_kardexable->reference_order_note_id) || isset($inventory_kardexable->reference_document_id)) ? $balance += 0 : $balance += $qty;
                    // session(['balance_item' => $data['balance']]);
                    $data['number'] = optional($inventory_kardexable)->number_full;
                    $data['type_transaction'] = isset($inventory_kardexable->transfer_reason_type->description) ? $inventory_kardexable->transfer_reason_type->description : '';
                    $data['date_of_issue'] = isset($inventory_kardexable->date_of_issue) ? $inventory_kardexable->date_of_issue->format('Y-m-d') : '';
                    $data['sale_note_asoc'] = isset($inventory_kardexable->reference_sale_note_id) ? optional($inventory_kardexable)->sale_note->number_full : "-";
                    $data['order_note_asoc'] = isset($inventory_kardexable->reference_order_note_id) ? optional($inventory_kardexable)->order_note->number_full : "-";
                    $data['doc_asoc'] = isset($inventory_kardexable->reference_document_id) ? $inventory_kardexable->reference_document->getNumberFullAttribute() : '-';
                    break;
            }
        }
        return $data;
    }

    public function save_average_history($id_document = null, $sale_note_id = null, $id_purchase = null, $purchase_cost, $total_purchase_cost, $price_balance, $input, $output, $balance, $type_transaction, $total_balance, $total_sales, $sales_cost, $number_full = null)
    {
        if ($number_full != null) {
            $number_full = explode("-", $number_full);
            $serie = $number_full[0];
            $number = $number_full[1];
        } else {
            $serie = null;
            $number = null;
        }

        if ($id_document != null) {

            $rowss = AverageHistory::updateOrCreate(['id_document' => $id_document], [
                'id_document' => $id_document,
                'sale_note_id' => null,
                'id_purchase' => null,
                'purchase_cost' => $purchase_cost,
                'total_purchase_cost' => $total_purchase_cost,
                'price_balance' => $price_balance,
                'input' => ($input > 0) ? $input : 0,
                'output' => ($output > 0) ? $output : 0,
                'balance' => ($balance > 0) ? $balance : 0,
                'total_sales' => $total_sales,
                'sales_cost' => $sales_cost,
                'total_balance' => $total_balance,
                'type_transaction' => $type_transaction,
                'serie' => $serie,
                'number' => $number
            ]);
        }
        if ($sale_note_id != null) {
            AverageHistory::updateOrCreate(['sale_note_id' => $sale_note_id], [
                'id_document' => null,
                'sale_note_id' => $sale_note_id,
                'id_purchase' => null,
                'purchase_cost' => $purchase_cost,
                'total_purchase_cost' => $total_purchase_cost,
                'price_balance' => $price_balance,
                'input' => ($input > 0) ? $input : 0,
                'output' => ($output > 0) ? $output : 0,
                'balance' => ($balance > 0) ? $balance : 0,
                'total_sales' => $total_sales,
                'sales_cost' => $sales_cost,
                'total_balance' => $total_balance,
                'type_transaction' => $type_transaction,
                'series' => $serie,
                'number' => $number

            ]);
        }

        if ($id_purchase != null) {
            if ($number_full != null) {
                $serie_purchase = $number_full[0];
                $number_purchase = $number_full[1];
            }
            AverageHistory::updateOrCreate(['id_purchase' => $id_purchase], [
                'id_document' => null,
                'sale_note_id' => null,
                'id_purchase' => $id_purchase,
                'purchase_cost' => $purchase_cost,
                'total_purchase_cost' => $total_purchase_cost,
                'price_balance' => $price_balance,
                'input' => ($input > 0) ? $input : 0,
                'output' => ($output > 0) ? $output : 0,
                'balance' => ($balance > 0) ? $balance : 0,
                'total_balance' => $total_balance,
                'total_sales' => $total_sales,
                'sales_cost' => $sales_cost,
                'type_transaction' => $type_transaction,
                'serie' => $serie_purchase,
                'number' => $number_purchase

            ]);
        }
    }

    public function getPricePurchase($purchase_id, $item_id)
    {
        $row = PurchaseItem::where(
            [
                'purchase_id' => $purchase_id,
                'item_id' => $item_id,
            ]
        )->first();
        return  $row;
    }
}
