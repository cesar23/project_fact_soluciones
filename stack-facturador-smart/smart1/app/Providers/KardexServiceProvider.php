<?php

namespace App\Providers;

use App\Models\Tenant\Configuration;
use App\Models\Tenant\Item;
use App\Models\Tenant\DocumentItem;
use App\Models\Tenant\Document;
use App\Models\Tenant\PurchaseItem;
use App\Models\Tenant\PurchaseSettlementItem;
use App\Models\Tenant\SaleNoteItem;
use App\Models\Tenant\Kardex;
use App\Traits\CostAverageTrait;
use Illuminate\Support\ServiceProvider;
use App\Traits\KardexTrait;


/**
 * Se debe tener en cuenta este provider para llevar el control de Kardex
 */
class KardexServiceProvider extends ServiceProvider
{
    use KardexTrait, CostAverageTrait;

    public function boot()
    {
        $this->save_item();
        $this->sale();
        $this->purchase();
        $this->purchase_settlement();
        $this->sale_note();
    }

    public function register()
    {
    }

    /**
     * Cuando se realiza una venta
     */
    private function sale()
    {
        DocumentItem::created(function (DocumentItem $document_item) {

            $document = Document::whereIn('document_type_id', ['01', '03'])->find($document_item->document_id);
            if ($document) {
                if($document->no_stock) return;
                $kardex = $this->saveKardex('sale', $document_item->item_id, $document_item->document_id, $document_item->quantity, 'document');

                if ($document->state_type_id != 11) {

                    $this->updateStock($document_item->item_id, $kardex->quantity, true);
                }
            }
        });
    }

    /**
     *Cuando se realiza una compra
     */
    private function purchase()
    {
        PurchaseItem::created(function (PurchaseItem $purchase_item) {
            $purchase = $purchase_item->purchase;
            $factor = 1;
            if($purchase->is_note_credit()){
                $factor = -1;
            }
            $kardex = $this->saveKardex('purchase', $purchase_item->item_id, $purchase_item->purchase_id, $purchase_item->quantity  , 'purchase');
            $this->updateStock($purchase_item->item_id, $kardex->quantity*$factor, false);
            // $this->average($purchase_item);
        });
    }

    /**
     *Cuando se realiza una compra
     */
    private function purchase_settlement()
    {
        PurchaseSettlementItem::created(function (PurchaseSettlementItem $purchase_item_settlement) {

            $kardex = $this->saveKardex('purchase', $purchase_item_settlement->item_id, $purchase_item_settlement->purchase_settlement_id, $purchase_item_settlement->quantity, 'purchase_settlement');
            /* dd($kardex->quantity); */
            $this->updateStock($purchase_item_settlement->item_id, $kardex->quantity, false);
        });
    }

    /**
     * Cuando se realiza una nota de compra
     */
    private function sale_note()
    {
        SaleNoteItem::created(function (SaleNoteItem $sale_note_item) {
            $sale_note = $sale_note_item->sale_note;
            if($sale_note->no_stock) return;
            $configuration = Configuration::first();
            if($configuration->emit_prepayment_document_from_sale_note) return;
            $kardex = $this->saveKardex('sale', $sale_note_item->item_id, $sale_note_item->sale_note_id, $sale_note_item->quantity, 'sale_note');

            $this->updateStock($sale_note_item->item_id, $kardex->quantity, true);
        });
    }

    /**
     * Cuando se guarda un item
     */
    private function save_item()
    {

        Item::created(function (Item $item) {

            $stock = ($item->stock) ? $item->stock : 0;
            $kardex = $this->saveKardex(null, $item->id, null, $stock, null);
        });
    }
}
