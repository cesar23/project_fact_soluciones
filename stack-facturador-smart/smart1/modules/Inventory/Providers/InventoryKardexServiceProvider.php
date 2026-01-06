<?php

namespace Modules\Inventory\Providers;

use App\Models\Tenant\ApportionmentItemsStock;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\DocumentItem;
use App\Models\Tenant\Document;
use App\Models\Tenant\Item;
use App\Models\Tenant\PurchaseItem;
use App\Models\Tenant\PurchaseSettlementItem;
use App\Models\Tenant\SaleNoteItem;
use Exception;
use Illuminate\Support\ServiceProvider;
use Modules\Inventory\Traits\InventoryTrait;
use Modules\Order\Models\OrderNote;
use Modules\Order\Models\OrderNoteItem;
use Modules\Item\Models\ItemLotsGroup;
use Modules\Item\Models\ItemLot;
use Modules\Inventory\Models\DevolutionItem;
use App\Models\Tenant\DispatchItem;
use App\Models\Tenant\ItemSizeStock;
use App\Models\Tenant\ItemWarehouse;
use App\Models\Tenant\NoStockDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Models\InventoryConfiguration;
use Modules\Inventory\Services\ItemCostHistoryService;
use Modules\Item\Models\LimitItemByDate;

/**
 * Se debe tener en cuenta este provider para llevar el control de Kardex
 */
class InventoryKardexServiceProvider extends ServiceProvider
{
    use InventoryTrait;

    public function register()
    {
        //
    }

    public function boot()
    {
        $this->purchase();
        $this->purchase_settlement();
        $this->sale();
        $this->sale_note();
        $this->sale_note_item_delete();
        $this->sale_document_type_03_delete();
        $this->order_note();
        $this->order_note_item_delete();
        $this->purchase_item_delete();
        $this->purchase_item_settlement_delete();
        $this->item_lot_delete();

        $this->devolution();

        $this->dispatch();
        $this->document_item_delete();
    }

    /**
     *Se dispara luego de crear la compra.
     */
    private function purchase()
    {
        PurchaseItem::created(function (PurchaseItem $purchase_item) {
            $purchase = $purchase_item->purchase;
            $factor = 1;
            if ($purchase->is_note_credit()) {
                $factor = -1;
            }

            $presentationQuantity = (!empty($purchase_item->item->presentation)) ? $purchase_item->item->presentation->quantity_unit : 1;

            $warehouse = ($purchase_item->warehouse_id) ? $this->findWarehouse($this->findWarehouseById($purchase_item->warehouse_id)->establishment_id) : $this->findWarehouse();
            // $warehouse = $this->findWarehouse($this->findWarehouseById($purchase_item->warehouse_id)->establishment_id);
            // $warehouse = $this->findWarehouse();
            //$this->createInventory($purchase_item->item_id, $purchase_item->quantity, $warehouse->id);
            $this->createInventoryKardex($purchase_item->purchase, $purchase_item->item_id, /*$purchase_item->quantity*/ ($purchase_item->quantity * $presentationQuantity * $factor), $warehouse->id);
            $this->updateStock($purchase_item->item_id, ($purchase_item->quantity * $presentationQuantity * $factor), $warehouse->id);
            (new ItemCostHistoryService)->insertItemMovement($purchase_item->item_id, $warehouse->id, ($purchase_item->quantity * $presentationQuantity * $factor), $purchase_item->unit_price, $purchase_item->purchase->id, get_class($purchase_item->purchase));
        });
    }

    /**
     *Se dispara luego de crear la compra.
     */
    private function purchase_settlement()
    {
        PurchaseSettlementItem::created(function (PurchaseSettlementItem $purchase_item) {
            $presentationQuantity = (!empty($purchase_item->item->presentation)) ? $purchase_item->item->presentation->quantity_unit : 1;

            $warehouse = ($purchase_item->warehouse_id) ? $this->findWarehouse($this->findWarehouseById($purchase_item->warehouse_id)->establishment_id) : $this->findWarehouse();
            // $warehouse = $this->findWarehouse($this->findWarehouseById($purchase_item->warehouse_id)->establishment_id);
            // $warehouse = $this->findWarehouse();
            //$this->createInventory($purchase_item->item_id, $purchase_item->quantity, $warehouse->id);
            $inve = $this->createInventoryKardex($purchase_item->purchase_settlement, $purchase_item->item_id, /*$purchase_item->quantity*/ ($purchase_item->quantity * $presentationQuantity), $warehouse->id);
            $this->updateStock($purchase_item->item_id, ($purchase_item->quantity * $presentationQuantity), $warehouse->id);
        });
    }



    /**
     * Se dispara cuando se realiza una venta
     */
    private function sale()
    {

        DocumentItem::created(function (DocumentItem $document_item) {
            $configuration = Configuration::first();
            // si es nota credito tipo 13, no se asocia a inventario
            if ($document_item->document->isCreditNoteAndType13()) return;
            if ($document_item->document->isNoAffectedStock()) return;
            if ($document_item->document->no_stock) return;
            // if ($document_item->document->has_prepayment) return;
            if($configuration->purchase_apportionment){
                ApportionmentItemsStock::updateStockRemaining($document_item->item_id, $document_item->quantity);
            }

            if (!$document_item->item->is_set) {
                $presentationQuantity = (!empty($document_item->item->presentation)) ? $document_item->item->presentation->quantity_unit : 1;
                $document = $document_item->document;
                $factor = ($document->document_type_id === '07') ? 1 : -1;
                $warehouse = ($document_item->warehouse_id) ? $this->findWarehouse($this->findWarehouseById($document_item->warehouse_id)->establishment_id) : $this->findWarehouse();
                //$this->createInventory($document_item->item_id, $factor * $document_item->quantity, $warehouse->id);
                $this->createInventoryKardex($document_item->document, $document_item->item_id, ($factor * ($document_item->quantity * $presentationQuantity)), $warehouse->id);
                $discounted_order_note = false;
                //$document_item->document->order_note_id
                if ($document_item->document->order_note_id) {
                    $order_note = OrderNote::find($document_item->document->order_note_id);
                    $discounted_order_note = (bool) $order_note->discounted_stock;
                }
                $emit_prepayment_document_from_sale_note = $configuration->emit_prepayment_document_from_sale_note;
                $sale_note_discounted_stock = (!$document_item->document->sale_note_id || $emit_prepayment_document_from_sale_note) && !$document_item->document->has_prepayment;
                if ($sale_note_discounted_stock && !$discounted_order_note && !$document_item->document->dispatch_id && !$document_item->document->sale_notes_relateds) {
                    $quantity = ($factor * ($document_item->quantity * $presentationQuantity)) * -1;
                    if ($configuration->limit_sale_daily_item) {
                        $limit_sale_daily = $document_item->m_item->limit_sale_daily;

                        if ($limit_sale_daily && $limit_sale_daily > 0) {
                            if ($quantity > $limit_sale_daily) {
                                throw new Exception($this->getLimitExceptionMessage($quantity, $document_item->item->description, $limit_sale_daily, $document_item->document->customer->name));
                            }

                            $limit_item_by_date = LimitItemByDate::firstOrNew([
                                'item_id' => $document_item->item_id,
                                'customer_id' => $document_item->document->customer_id,
                                'date' => $document_item->document->date_of_issue,
                            ]);

                            $new_quantity = ($limit_item_by_date->limit ?? 0) + $quantity;

                            if ($new_quantity > $limit_sale_daily) {
                                throw new Exception($this->getLimitExceptionMessage($quantity, $document_item->item->description, $limit_sale_daily, $document_item->document->customer->name));
                            }

                            $limit_item_by_date->limit = $new_quantity;
                            $limit_item_by_date->save();
                        }
                    }
                    $this->updateStock($document_item->item_id, ($factor * ($document_item->quantity * $presentationQuantity)), $warehouse->id);
                    (new ItemCostHistoryService)->insertItemMovement($document_item->item_id, $warehouse->id, ($factor * ($document_item->quantity * $presentationQuantity)), $document_item->unit_price, $document_item->document->id, get_class($document_item->document));
                } else {
                    if ($document_item->document->dispatch) {
                        if (!$document_item->document->dispatch->transfer_reason_type->discount_stock) {
                            $this->updateStock($document_item->item_id, ($factor * ($document_item->quantity * $presentationQuantity)), $warehouse->id);
                            (new ItemCostHistoryService)->insertItemMovement($document_item->item_id, $warehouse->id, ($factor * ($document_item->quantity * $presentationQuantity)), $document_item->unit_price, $document_item->document->id, get_class($document_item->document));
                        }
                    }
                }
            } else {

                $item = Item::findOrFail($document_item->item_id);

                foreach ($item->sets as $it) {
                    /** @var Item $ind_item */
                    $ind_item = $it->individual_item;
                    $item_set_quantity = ($it->quantity) ? $it->quantity : 1;
                    $presentationQuantity = 1;
                    $document = $document_item->document;
                    $factor = ($document->document_type_id === '07') ? 1 : -1;
                    $warehouse = $this->findWarehouse();
                    $this->createInventoryKardex($document_item->document, $ind_item->id, ($factor * ($document_item->quantity * $presentationQuantity * $item_set_quantity)), $warehouse->id);
                    $discounted_order_note = false;
                    //$document_item->document->order_note_id
                    if ($document_item->document->order_note_id) {
                        $order_note = OrderNote::find($document_item->document->order_note_id);
                        $discounted_order_note = (bool) $order_note->discounted_stock;
                    }
                    if (!$document_item->document->sale_note_id && !$discounted_order_note && !$document_item->document->dispatch_id && !$document_item->document->sale_notes_relateds) {
                        $this->updateStock($ind_item->id, ($factor * ($document_item->quantity * $presentationQuantity * $item_set_quantity)), $warehouse->id);
                        (new ItemCostHistoryService)->insertItemMovement($ind_item->id, $warehouse->id, ($factor * ($document_item->quantity * $presentationQuantity * $item_set_quantity)), $ind_item->unit_price, $document_item->document->id, get_class($document_item->document));
                    } else {
                        if ($document_item->document->dispatch) {
                            if (!$document_item->document->dispatch->transfer_reason_type->discount_stock) {
                                $this->updateStock($ind_item->id, ($factor * ($document_item->quantity * $presentationQuantity * $item_set_quantity)), $warehouse->id);
                                (new ItemCostHistoryService)->insertItemMovement($ind_item->id, $warehouse->id, ($factor * ($document_item->quantity * $presentationQuantity * $item_set_quantity)), $ind_item->unit_price, $document_item->document->id, get_class($document_item->document));
                            }
                        }
                    }
                }
            }

            /*
             * Calculando el stock por lote por factor según la unidad
             */
            if (!$document->isGeneratedFromExternalRecord()) {
                if (isset($document_item->item->IdLoteSelected)) {
                    if ($document_item->item->IdLoteSelected != null) {
                        if (is_array($document_item->item->IdLoteSelected)) {
                            // presentacion - factor de lista de precios
                            $quantity_unit = isset($document_item->item->presentation->quantity_unit) ? $document_item->item->presentation->quantity_unit : 1;
                            $lotesSelecteds = $document_item->item->IdLoteSelected;
                            $document_factor = ($document->document_type_id === '07') ? 1 : -1;
                            $inventory_configuration = InventoryConfiguration::first();
                            $inventory_configuration->stock_control;
                            foreach ($lotesSelecteds as $item) {
                                $lot = ItemLotsGroup::query()->find($item->id);
                                $compromise_quantity = isset($item->compromise_quantity) ? $item->compromise_quantity : 1;
                                $lot->quantity = $lot->quantity + ($quantity_unit * $compromise_quantity * $document_factor);
                                if ($inventory_configuration->stock_control) {
                                    $this->validateStockLotGroup($lot, $document_item);
                                }
                                $lot->save();
                            }
                        } else {
                            $lot = ItemLotsGroup::query()->find($document_item->item->IdLoteSelected);
                            try {
                                $quantity_unit = $document_item->item->presentation->quantity_unit;
                            } catch (Exception $e) {
                                Log::info(json_encode([
                                    'Error al obtener la presentación del item',
                                    'item_id' => $document_item->item_id,
                                    'error' => $e->getMessage()
                                ]));
                                $quantity_unit = 1;
                            }
                            if ($document->document_type_id === '07') {
                                $quantity = $lot->quantity + ($quantity_unit * $document_item->quantity);
                            } else {
                                $quantity = $lot->quantity - ($quantity_unit * $document_item->quantity);
                            }
                            $lot->quantity = $quantity;
                            $lot->save();
                        }
                    }
                }
            }
            if (isset($document_item->item->sizes_selected)) {
                foreach ($document_item->item->sizes_selected as $size) {

                    $item_size = ItemSizeStock::where('item_id', $document_item->item_id)->where('size', $size->size)->first();
                    if ($item_size) {
                        $item_size->stock = $item_size->stock - $size->qty;
                        $item_size->save();
                    }
                }
            }
            if (isset($document_item->item->lots)) {
                foreach ($document_item->item->lots as $it) {

                    if ($it->has_sale == true) {
                        $r = ItemLot::find($it->id);
                        // $r->has_sale = true;
                        $r->has_sale = ($document->document_type_id == '07') ? false : true;
                        $r->save();
                    }
                }
                /*if($document_item->item->IdLoteSelected != null)
                {
                    $lot = ItemLotsGroup::find($document_item->item->IdLoteSelected);
                    $lot->quantity = ($lot->quantity - $document_item->quantity);
                    $lot->save();
                }*/
            }
        });
    }
    private function getLimitExceptionMessage($quantity, $description, $limit, $customer_name): string
    {
        return "No se puede registrar la venta de {$quantity} del item {$description} porque excede el límite de venta diaria de {$limit} para el cliente {$customer_name}.";
    }

    /**
     * Se dispara  al generar una nota de venta
     */
    private function sale_note()
    {
        SaleNoteItem::created(function (SaleNoteItem $sale_note_item) {

            $configuration = Configuration::first();
            $sale_note = $sale_note_item->sale_note;    
            if ($sale_note->no_stock) {
                return;
            }
        
            if ($configuration->emit_prepayment_document_from_sale_note) return;
            if($configuration->purchase_apportionment){
                ApportionmentItemsStock::updateStockRemaining($sale_note_item->item_id, $sale_note_item->quantity);
            }
            if (!$sale_note_item->item->is_set) {

                $presentationQuantity = (!empty($sale_note_item->item->presentation)) ? $sale_note_item->item->presentation->quantity_unit : 1;

                // $warehouse = $this->findWarehouse($sale_note_item->sale_note->establishment_id);
                $warehouse = ($sale_note_item->warehouse_id) ? $this->findWarehouse($this->findWarehouseById($sale_note_item->warehouse_id)->establishment_id) : $this->findWarehouse();
                // $this->createInventoryKardex($sale_note_item->sale_note, $sale_note_item->item_id, (-1 * ($sale_note_item->quantity * $presentationQuantity)), $warehouse->id);
                $this->createInventoryKardexSaleNote($sale_note_item->sale_note, $sale_note_item->item_id, (-1 * ($sale_note_item->quantity * $presentationQuantity)), $warehouse->id, $sale_note_item->id);
                // if (!$sale_note_item->sale_note->order_note_id)
                $discounted_order_note = false;
                if ($sale_note_item->sale_note->order_note_id) {
                    $order_note = OrderNote::find($sale_note_item->sale_note->order_note_id);
                    $discounted_order_note = (bool) $order_note->discounted_stock;
                }
                if (!$discounted_order_note) {
                    if ($configuration->limit_sale_daily_item) {
                        $limit_sale_daily = $sale_note_item->relation_item->limit_sale_daily;
                        if ($limit_sale_daily && $limit_sale_daily > 0) {
                            $quantity = ($sale_note_item->quantity * $presentationQuantity);
                            if ($quantity > $limit_sale_daily) {
                                throw new Exception($this->getLimitExceptionMessage($quantity, $sale_note_item->item->description, $limit_sale_daily, $sale_note_item->sale_note->customer->name));
                            }

                            $limit_item_by_date = LimitItemByDate::firstOrNew([
                                'item_id' => $sale_note_item->item_id,
                                'customer_id' => $sale_note_item->sale_note->customer_id,
                                'date' => $sale_note_item->sale_note->date_of_issue,
                            ]);

                            $new_quantity = ($limit_item_by_date->limit ?? 0) + $quantity;

                            if ($new_quantity > $limit_sale_daily) {
                                throw new Exception($this->getLimitExceptionMessage($quantity, $sale_note_item->item->description, $limit_sale_daily, $sale_note_item->sale_note->customer->name));
                            }
                            $limit_item_by_date->limit = $new_quantity;
                            $limit_item_by_date->save();
                        }
                    }
                    $this->updateStock($sale_note_item->item_id, (-1 * ($sale_note_item->quantity * $presentationQuantity)), $warehouse->id);
                    (new ItemCostHistoryService)->insertItemMovement($sale_note_item->item_id, $warehouse->id, (-1 * ($sale_note_item->quantity * $presentationQuantity)), $sale_note_item->unit_price, $sale_note_item->sale_note->id, get_class($sale_note_item->sale_note));
                }
                // $this->updateStock($sale_note_item->item_id, (-1 * ($sale_note_item->quantity * $presentationQuantity)), $warehouse->id);
            } else {

                $item = Item::findOrFail($sale_note_item->item_id);

                foreach ($item->sets as $it) {

                    $ind_item  = $it->individual_item;
                    $item_set_quantity  = ($it->quantity) ? $it->quantity : 1;
                    $presentationQuantity = 1;
                    $warehouse = $this->findWarehouse($sale_note_item->sale_note->establishment_id);
                    // $this->createInventoryKardex($sale_note_item->sale_note, $ind_item->id , (-1 * ($sale_note_item->quantity * $presentationQuantity)), $warehouse->id);
                    $this->createInventoryKardexSaleNote($sale_note_item->sale_note, $ind_item->id, (-1 * ($sale_note_item->quantity * $presentationQuantity * $item_set_quantity)), $warehouse->id, $sale_note_item->id);
                    // if (!$sale_note_item->sale_note->order_note_id)
                    $discounted_order_note = false;
                    if ($sale_note_item->sale_note->order_note_id) {
                        $order_note = OrderNote::find($sale_note_item->sale_note->order_note_id);
                        $discounted_order_note = (bool) $order_note->discounted_stock;
                    }
                    if (!$discounted_order_note)
                        $this->updateStock($ind_item->id, (-1 * ($sale_note_item->quantity * $presentationQuantity * $item_set_quantity)), $warehouse->id);
                    (new ItemCostHistoryService)->insertItemMovement($ind_item->id, $warehouse->id, (-1 * ($sale_note_item->quantity * $presentationQuantity * $item_set_quantity)), $ind_item->unit_price, $sale_note_item->sale_note->id, get_class($sale_note_item->sale_note));
                }
            }

            // series
            if (isset($sale_note_item->item->lots)) {
                foreach ($sale_note_item->item->lots as $it) {
                    if ($it->has_sale == true) {
                        $r = ItemLot::find($it->id);
                        $r->has_sale =  true;
                        $r->save();
                    }
                }
            }
            if (isset($sale_note_item->item->sizes_selected)) {
                foreach ($sale_note_item->item->sizes_selected as $size) {
                    $item_size = ItemSizeStock::where('item_id', $sale_note_item->item_id)->where('size', $size->size)->first();
                    if ($item_size) {
                        $item_size->stock = $item_size->stock - $size->qty;
                        $item_size->save();
                    }
                }
            }
        });
    }


    /**
     * @param $item_id
     * @param $quantity
     * @param $warehouse_id
     */
    private function createInventory($item_id, $quantity, $warehouse_id)
    {
        if (!$this->checkInventory($item_id, $warehouse_id)) {
            $item = $this->findItem($item_id);
            $this->createInitialInventory($item_id, $item->stock + (-1 * $quantity), $warehouse_id);
        }
    }


    /**
     * Se dispara al borrar un item de nota de venta
     */
    private function sale_note_item_delete()
    {

        SaleNoteItem::deleted(function (SaleNoteItem $sale_note_item) {


            if (!$sale_note_item->item->is_set) {

                $presentationQuantity = (!empty($sale_note_item->item->presentation)) ? $sale_note_item->item->presentation->quantity_unit : 1;

                // $warehouse = $this->findWarehouse();
                $warehouse = ($sale_note_item->warehouse_id) ? $this->findWarehouse($this->findWarehouseById($sale_note_item->warehouse_id)->establishment_id) : $this->findWarehouse($sale_note_item->sale_note->establishment_id);

                $this->createInventoryKardex($sale_note_item->sale_note, $sale_note_item->item_id, ($sale_note_item->quantity * $presentationQuantity), $warehouse->id);
                // $this->deleteInventoryKardex($sale_note_item->sale_note, $sale_note_item->inventory_kardex_id);
                $this->updateStock($sale_note_item->item_id, (1 * ($sale_note_item->quantity * $presentationQuantity)), $warehouse->id);
            } else {

                $item = Item::findOrFail($sale_note_item->item_id);

                foreach ($item->sets as $it) {

                    $ind_item  = $it->individual_item;
                    $item_set_quantity  = ($it->quantity) ? $it->quantity : 1;
                    $presentationQuantity = 1;
                    $warehouse = $this->findWarehouse();

                    $this->createInventoryKardex($sale_note_item->sale_note, $ind_item->id, ($sale_note_item->quantity * $presentationQuantity * $item_set_quantity), $warehouse->id);
                    // $this->deleteInventoryKardex($sale_note_item->sale_note, $sale_note_item->inventory_kardex_id);

                    $this->updateStock($ind_item->id, (1 * ($sale_note_item->quantity * $presentationQuantity * $item_set_quantity)), $warehouse->id);
                }
            }

            $this->deleteItemLots($sale_note_item);
        });
    }


    /**
     * Se dispara al borrar un documento tipo 3
     */
    private function sale_document_type_03_delete()
    {

        Document::deleted(function (Document $document) {

            if ($document->document_type_id === '03' && $document->state_type_id === '01') {

                foreach ($document->items as $document_item) {


                    if (!$document_item->item->is_set) {

                        $presentationQuantity = (!empty($document_item->item->presentation)) ? $document_item->item->presentation->quantity_unit : 1;

                        $factor = 1;
                        $warehouse = $this->findWarehouse();

                        $this->deleteAllInventoryKardexByModel($document);

                        if (!$document->sale_note_id) $this->updateStock($document_item->item_id, ($factor * ($document_item->quantity * $presentationQuantity)), $warehouse->id);
                    } else {

                        $item = Item::findOrFail($document_item->item_id);

                        foreach ($item->sets as $it) {

                            $ind_item  = $it->individual_item;
                            $item_set_quantity  = ($it->quantity) ? $it->quantity : 1;
                            $presentationQuantity = 1;
                            $factor = 1;
                            $warehouse = $this->findWarehouse();

                            $this->deleteAllInventoryKardexByModel($document);
                            if (!$document->sale_note_id) $this->updateStock($ind_item->id, ($factor * ($document_item->quantity * $presentationQuantity * $item_set_quantity)), $warehouse->id);
                        }
                    }
                }
            }
        });
    }

    /**
     * Se dispara al generar un pedido
     */
    private function order_note()
    {

        OrderNoteItem::created(function (OrderNoteItem $order_note_item) {
            /** @todo bloque repetido, buscar colocar en funcion */
            $item = $order_note_item->item;
            $configuration = Configuration::first();
            $configuration_inventory = InventoryConfiguration::first();
            $warehouse_id = $order_note_item->warehouse_id;
            $warehouse = ($warehouse_id) ?
                $this->findWarehouse($this->findWarehouseById($warehouse_id)->establishment_id) :
                $this->findWarehouse();
            if ($configuration_inventory->order_note_with_stock) {
                $itemW = ItemWarehouse::where('item_id', $item->id)->where('warehouse_id', $warehouse->id)->first();
                if ($itemW) {
                    if ($itemW->stock < $order_note_item->quantity) {
                        throw new Exception("El stock del item {$item->description} es insuficiente.");
                    }
                }
            }
            $document = $order_note_item->order_note;

            $presentationQuantity = $item->presentation->quantity_unit ?? 1;
            // $warehouse = $this->findWarehouse($order_note_item->order_note->establishment_id);
            // $warehouse = ($warehouse_id) ? $this->findWarehouse($this->findWarehouseById($warehouse_id)->establishment_id) : $this->findWarehouse($order_note_item->order_note->establishment_id);
            $item_id = $order_note_item->item_id;
            // $factor = 1;
            // Factor proviende de Document.
            $factor = ($document->document_type_id  && $document->document_type_id === '07') ? 1 : -1;

            if (!$item->is_set) {
                $presentationQuantity = $item->presentation->quantity_unit ?? 1;
                $quanty = ($factor * ($order_note_item->quantity * $presentationQuantity));

                $warehouse = ($warehouse_id) ?
                    $this->findWarehouse($this->findWarehouseById($warehouse_id)->establishment_id) :
                    $this->findWarehouse();
                //$this->createInventory($item_id, $factor * $order_note_item->quantity, $warehouse->id);
                if ($configuration->discount_order_note) {
                    $this->createInventoryKardex($document, $item_id, $quanty, $warehouse->id);
                    if (!$document->sale_note_id && !$document->order_note_id && !$document->dispatch_id) {
                        $this->updateStock($item_id, ($quanty), $warehouse->id);
                    } else {
                        if ($document->dispatch) {
                            if (!$document->dispatch->transfer_reason_type->discount_stock) {
                                $this->updateStock($item_id, ($quanty), $warehouse->id);
                            }
                        }
                    }
                }
            } else {

                $item = Item::findOrFail($item_id);
                foreach ($item->sets as $it) {
                    /** @var Item $ind_item */

                    $ind_item = $it->individual_item;
                    $item_id = $ind_item->id;
                    $item_set_quantity = ($it->quantity) ?: 1;
                    $presentationQuantity = 1;

                    $warehouse = $this->findWarehouse();
                    $quanty = $factor * ($order_note_item->quantity * $presentationQuantity * $item_set_quantity);


                    if ($configuration->discount_order_note) {
                        $this->createInventoryKardex($document, $item_id, ($quanty), $warehouse->id);
                        if (!$document->sale_note_id && !$document->order_note_id && !$document->dispatch_id) {
                            $this->updateStock($item_id, ($quanty), $warehouse->id);
                        } else {
                            if ($document->dispatch) {
                                if (!$document->dispatch->transfer_reason_type->discount_stock) {
                                    $this->updateStock($item_id, ($quanty), $warehouse->id);
                                }
                            }
                        }
                    }
                }
            }


            // $this->createInventoryKardex($order_note_item->order_note, $order_note_item->item_id, (-1 * ($order_note_item->quantity * $presentationQuantity)), $warehouse->id);
            // $this->updateStock($order_note_item->item_id, (-1 * ($order_note_item->quantity * $presentationQuantity)), $warehouse->id);

            // control de lotes
            if ($configuration->discount_order_note) {
                if (isset($order_note_item->item->IdLoteSelected)) {
                    $IdLoteSelected = $order_note_item->item->IdLoteSelected;

                    if (is_array($IdLoteSelected)) {
                        foreach ($IdLoteSelected as $lot_selected) {
                            $lot = ItemLotsGroup::find($lot_selected->id);
                            $lot->quantity = $lot->quantity - ($lot_selected->compromise_quantity * $presentationQuantity ?? 1);
                            $lot->save();
                        }
                    }
                } else {

                    if (isset($order_note_item->item->lots_group)) {
                        if (is_array($order_note_item->item->lots_group) && count($order_note_item->item->lots_group) > 0) {
                            $lots_group = $order_note_item->item->lots_group;

                            foreach ($lots_group as $item) {
                                $lot = ItemLotsGroup::query()->find($item->id);
                                $lot->quantity = $lot->quantity - $item->compromise_quantity;
                                $lot->save();
                            }
                        }
                    }
                }
                // control de lotes



                if (isset($item->lots)) {
                    foreach ($item->lots as $it) {

                        if ($it->has_sale == true) {
                            $r = ItemLot::find($it->id);
                            $r->has_sale = true;
                            $r->save();
                        }
                    }
                }
            }
        });
    }


    /**
     * Se dispara cuando se borra un item de pedido
     */
    private function order_note_item_delete()
    {

        OrderNoteItem::deleted(function (OrderNoteItem $order_note_item) {
            $configuration = Configuration::first();
            if ($configuration->discount_order_note) {
                $presentationQuantity = (!empty($order_note_item->item->presentation)) ? $order_note_item->item->presentation->quantity_unit : 1;

                $warehouse = ($order_note_item->warehouse_id) ? $this->findWarehouse($this->findWarehouseById($order_note_item->warehouse_id)->establishment_id) : $this->findWarehouse($order_note_item->order_note->establishment_id);

                $this->createInventoryKardex($order_note_item->order_note, $order_note_item->item_id, (1 * ($order_note_item->quantity * $presentationQuantity)), $warehouse->id);

                $this->updateStock($order_note_item->item_id, (1 * ($order_note_item->quantity * $presentationQuantity)), $warehouse->id);


                if (isset($order_note_item->item->lots_group)) {

                    if (is_array($order_note_item->item->lots_group) && count($order_note_item->item->lots_group) > 0) {
                        $lots_group = $order_note_item->item->lots_group;

                        foreach ($lots_group as $item) {
                            $lot = ItemLotsGroup::query()->find($item->id);
                            $lot->quantity = $lot->quantity + $item->compromise_quantity;
                            $lot->save();
                        }
                    }
                }
            }
        });
    }

    /**
     * Se dispara cuando se borra un item de compra
     */
    private function purchase_item_delete()
    {
        PurchaseItem::deleted(function (PurchaseItem $purchase_item) {


            $presentationQuantity = (!empty($purchase_item->item->presentation)) ? $purchase_item->item->presentation->quantity_unit : 1;

            $warehouse = ($purchase_item->warehouse_id) ? $this->findWarehouse($this->findWarehouseById($purchase_item->warehouse_id)->establishment_id) : $this->findWarehouse();

            $this->verifyHasSaleLots($purchase_item);
            $this->verifyHasSaleLotsGroup($purchase_item);

            $this->deleteItemSeriesAndGroup($purchase_item);

            $this->createInventoryKardex($purchase_item->purchase, $purchase_item->item_id, (-1 * ($purchase_item->quantity * $presentationQuantity)), $warehouse->id);
            // $this->updateStock($purchase_item->item_id, (-1 *($purchase_item->quantity * $presentationQuantity)), $warehouse->id);
            $this->updateStockPurchase($purchase_item->item_id, (-1 * ($purchase_item->quantity * $presentationQuantity)), $warehouse->id);
        });
    }

    /**
     * Se dispara cuando se borra un item de compra
     */
    private function purchase_item_settlement_delete()
    {
        PurchaseSettlementItem::deleted(function (PurchaseSettlementItem $purchase_item) {


            $presentationQuantity = (!empty($purchase_item->item->presentation)) ? $purchase_item->item->presentation->quantity_unit : 1;

            $warehouse = ($purchase_item->warehouse_id) ? $this->findWarehouse($this->findWarehouseById($purchase_item->warehouse_id)->establishment_id) : $this->findWarehouse();

            $this->verifyHasSaleLots($purchase_item);
            $this->verifyHasSaleLotsGroup($purchase_item);

            $this->deleteItemSeriesAndGroup($purchase_item);

            $this->createInventoryKardex($purchase_item->purchase_settlement, $purchase_item->item_id, (-1 * ($purchase_item->quantity * $presentationQuantity)), $warehouse->id);
            // $this->updateStock($purchase_item->item_id, (-1 *($purchase_item->quantity * $presentationQuantity)), $warehouse->id);
            $this->updateStockPurchase($purchase_item->item_id, (-1 * ($purchase_item->quantity * $presentationQuantity)), $warehouse->id);
        });
    }

    /**
     * Se dispara cuando se borra un item de lote de item
     */
    private function item_lot_delete()
    {
        /*ItemLot::deleted(function(ItemLot $item_lot) {

            if((bool)$item_lot->has_sale)
            {
                throw new Exception("La serie {$item_lot->series} ha sido vendida!");
            }
        });*/
    }


    /**
     * Se dispara cuando se genera una devolucion
     */
    private function devolution()
    {

        DevolutionItem::created(function (DevolutionItem $devolution_item) {

            $devolution = $devolution_item->devolution;

            $warehouse = $this->findWarehouse($devolution_item->devolution->establishment_id);

            //$this->createInventory($devolution_item->item_id, $factor * $devolution_item->quantity, $warehouse->id);
            $this->createInventoryKardex($devolution_item->devolution, $devolution_item->item_id, -$devolution_item->quantity, $warehouse->id);

            $this->updateStock($devolution_item->item_id, -$devolution_item->quantity, $warehouse->id);
            (new ItemCostHistoryService)->insertItemMovement($devolution_item->item_id, $warehouse->id, (-1 * ($devolution_item->quantity)), 0, $devolution_item->devolution->id, get_class($devolution_item->devolution));

            if (isset($devolution_item->item->IdLoteSelected)) {
                if ($devolution_item->item->IdLoteSelected != null) {
                    $lot = ItemLotsGroup::find($devolution_item->item->IdLoteSelected);
                    $lot->quantity = $lot->quantity - $devolution_item->quantity;
                    $lot->save();
                }
            }

            if (isset($devolution_item->item->lots)) {
                foreach ($devolution_item->item->lots as $it) {

                    if ($it->has_sale == true) {
                        $r = ItemLot::find($it->id);
                        $r->has_sale = true;
                        $r->state = 'Inactivo';
                        $r->save();
                    }
                }
            }
        });
    }


    /**
     * Se dispara cuando se genera un despacho
     */
    private function dispatch()
    {

        DispatchItem::created(function (DispatchItem $dispatch_item) {
            $dispatch = $dispatch_item->dispatch;
            $move_stock = $dispatch->state_type_id == "55";
            $document = $dispatch->reference_document;
            if ($document) {
                $move_stock = $document->no_stock;
            }
            $sale_note = $dispatch->sale_note;
            if ($sale_note) {
                $move_stock = $sale_note->no_stock;
            }
            if ($dispatch->document_type_id === '09') {
                if ($dispatch->transfer_reason_type->discount_stock || $move_stock) {

                    $warehouse = $this->findWarehouse();

                    $this->createInventoryKardex($dispatch, $dispatch_item->item_id, -$dispatch_item->quantity, $warehouse->id);

                    if (!$dispatch->reference_sale_note_id && !$dispatch->reference_order_note_id && !$dispatch->reference_document_id || $move_stock) {

                        $this->updateStock($dispatch_item->item_id, -$dispatch_item->quantity, $warehouse->id);

                        if (isset($dispatch_item->item->IdLoteSelected)) {

                            if ($dispatch_item->item->IdLoteSelected != null) {
                                $lotesSelecteds = $dispatch_item->item->IdLoteSelected;
                                foreach ($lotesSelecteds as $item) {
                                    $lot = ItemLotsGroup::query()->find($item->id);
                                    $lot->quantity = $lot->quantity - $dispatch_item->quantity;
                                    $lot->save();
                                }
                            }
                        }
                        if ($move_stock) {
                            if ($dispatch->reference_document_id) {
                                $complete = $this->check_dispatch_document($dispatch->reference_document_id);
                                if ($complete) {
                                    $no_stock_document = NoStockDocument::where('document_id', $dispatch->reference_document_id)->first();
                                    if ($no_stock_document) {
                                        $no_stock_document->completed = true;
                                        $no_stock_document->save();
                                    }
                                }
                            } else if ($dispatch->reference_sale_note_id) {
                                $complete = $this->check_dispatch_document($dispatch->reference_sale_note_id, 'sale_note');
                                if ($complete) {
                                    $no_stock_document = NoStockDocument::where('sale_note_id', $dispatch->reference_sale_note_id)->first();
                                    if ($no_stock_document) {
                                        $no_stock_document->completed = true;
                                        $no_stock_document->save();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        });
    }
    private function check_dispatch_document($document_id, $type = 'document')
    {
        $complete = true;
        if ($type === 'document') {
            $items = DocumentItem::where('document_id', $document_id)->get();
            foreach ($items as $item) {
                $quantity = $item->quantity;
                $item_id = $item->item_id;
                $quantity_dispatch = DispatchItem::where('item_id', $item_id)
                    ->whereHas('dispatch', function ($query) use ($document_id) {
                        $query->where('reference_document_id', $document_id);
                    })
                    ->sum('quantity');
                if ($quantity != $quantity_dispatch) {
                    $complete = false;
                    break;
                }
            }
        } else if ($type === 'sale_note') {
            $items = SaleNoteItem::where('sale_note_id', $document_id)->get();
            foreach ($items as $item) {
                $quantity = $item->quantity;
                $item_id = $item->item_id;
                $quantity_dispatch = DispatchItem::where('item_id', $item_id)
                    ->whereHas('dispatch', function ($query) use ($document_id) {
                        $query->where('reference_sale_note_id', $document_id);
                    })
                    ->sum('quantity');
                if ($quantity != $quantity_dispatch) {
                    $complete = false;
                    break;
                }
            }
        }
        return $complete;
    }
    /**
     * Se dispara cuando se borra un document item
     */
    private function document_item_delete()
    {

        DocumentItem::deleted(function (DocumentItem $document_item) {

            if (!$document_item->item->is_set) {

                $this->processIndividualDocumentItem($document_item);
            } else {

                $this->voidedDocumentItemSet($document_item);
            }
        });
    }
}
