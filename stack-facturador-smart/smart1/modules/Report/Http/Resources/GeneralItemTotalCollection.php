<?php

namespace Modules\Report\Http\Resources;

use App\Models\Tenant\DocumentItem;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\PurchaseItem;
use App\Models\Tenant\QuotationItem;
use App\Models\Tenant\SaleNoteItem;
use Illuminate\Http\Resources\Json\ResourceCollection;

class GeneralItemTotalCollection extends ResourceCollection
{

    public function toArray($request)
    {

        // $apply_conversion_to_pen = $request->apply_conversion_to_pen == 'true';
        $apply_conversion_to_pen = true;

        return $this->collection->transform(function ($row, $key) use ($apply_conversion_to_pen) {

            /** @var \App\Models\Tenant\DocumentItem|\App\Models\Tenant\PurchaseItem|mixed|\App\Models\Tenant\SaleNoteItem|mixed $row */
            $resource = self::getDocument($row);
            $purchase_item = null;
            $total_item_purchase = self::getPurchaseUnitPrice($row, $resource, $purchase_item);
            $quantity_unit = 0;
            if (isset($row->item->presentation)) {
                if (is_object($row->item->presentation)) {
                    $quantity_unit = $row->item->presentation->quantity_unit;
                    $total_item_purchase *= $quantity_unit * $row->quantity;
                }
            }


            $row_total = $row->total;


            if ($apply_conversion_to_pen && $row->isCurrencyTypeUsd()) {
                
                $row_total = $row->getConvertTotalToPen();
            }
            $utility_item = $row_total - $total_item_purchase;
            // $utility_item = $row->total - $total_item_purchase;

            $item = $row->getModelItem();
            $platform = $item->getWebPlatformModel();
            if ($platform !== null) {
                $platform = $platform->name;
            }



            return [

                'quantity' => round(floatval($row->quantity ?? "0"), 2),
                'total' => round(floatval($row_total ?? "0"), 2),
                'total_item_purchase' => round(floatval($total_item_purchase ?? "0"), 2),
                'utility_item' => round(floatval($utility_item ?? "0"), 2),


            ];
        });
    }

    public static function getPurchaseUnitPrice($record, $resource = null, &$purchase_item = null)
    {
        if ($resource === null) {
            $resource = self::getDocument($record);
        }
        $purchase_unit_price = self::getIndividualPurchaseUnitPrice($record, $resource, $purchase_item) * $record->quantity;
        if ($record->relation_item->is_set) {
            $purchase_unit_price = 0;
            foreach ($record->relation_item->sets as $item_set) {
                $purchase_unit_price += (self::getIndividualPurchaseUnitPrice($item_set, $resource, $purchase_item) * $item_set->quantity) * $record->quantity;
            }
        } /*elseif() {
        }*/

        return $purchase_unit_price;
    }

    public static function getIndividualPurchaseUnitPrice($record, $resource, &$purchase_item = null)
    {
        $purchase_unit_price = 0;
        $currency_type_id = $resource['currency_type_id'];
        // Se busca la compra del producto en el dia o antes de su venta,
        // para sacar la ganancia correctamente

        // La tabla purchase items parece eliminar due of date
        $purchase_item = PurchaseItem::where('item_id', $record->item_id)
            ->latest('id')->get()->pluck('purchase_id');
        // para ello se busca las compras
        $purchase = Purchase::wherein('id', $purchase_item)
            ->where('date_of_issue', '<=', $resource['date_of_issue'])
            ->latest('id')->first();

        if ($purchase) {
            $purchase_item = PurchaseItem::where([
                'purchase_id' => $purchase->id,
                'item_id' => $record->item_id
            ])
                ->latest('id')
                ->first();

            $purchase_unit_price = $purchase_item->unit_price;
            $purchase = Purchase::find($purchase_item->purchase_id);
            $exchange_rate_sale = $purchase->exchange_rate_sale * 1;
            // Si la venta es en soles, y la compra del producto es en dolares, se hace la transformcaion
            if ($currency_type_id === 'PEN') {
                if ($purchase->currency_type_id !== $currency_type_id) {
                    $purchase_unit_price = $purchase_unit_price * $exchange_rate_sale;
                }
            } else {
                // Si la venta es en dolares, y la compra del producto es en soles, se hace la transformcaion
                if ($purchase->currency_type_id !== $currency_type_id && $exchange_rate_sale !== 0) {
                    try{
                        $purchase_unit_price = $purchase_unit_price / $exchange_rate_sale;
                    }catch(\Exception $e){
                        $purchase_unit_price = 0;
                    }
                }
            }
        }
        // TODO: revisar esta linea: Eliminando esta linea porque el precio de compra no puede ser igual al precio de venta,
        // en conculusión esta condición nunca será 0, para los productos que no tienen una compra luego de registrarse
        // $purchase_unit_price = ($purchase_item) ? $purchase_item->unit_price : $record->unit_price;

        if ($purchase_unit_price == 0 && $record->relation_item->purchase_unit_price > 0) {
            $purchase_unit_price = $record->relation_item->purchase_unit_price;
        }


        // if ($record->relation_item->purchase_unit_price > 0) {
        //     $purchase_unit_price = $record->relation_item->purchase_unit_price;
        // } else {
        //     $purchase_item = PurchaseItem::select('unit_price')->where('item_id', $record->item_id)->latest('id')->first();
        //     $purchase_unit_price = ($purchase_item) ? $purchase_item->unit_price : $record->unit_price;
        // }
        return $purchase_unit_price;
    }

    public static function getLotsHasSale($row)
    {
        if (isset($row->item->lots)) {
            $class = get_class($row);
            if ($class == 'App\Models\Tenant\PurchaseItem') {
                // para compras
                return collect($row->item->lots);
            }
            return collect($row->item->lots)->where('has_sale', 1);
        } else {
            return [];
        }
    }

    public static function getDocument(&$row)
    {

        $data = [];
        /*$data['quantity'] = number_format($row->quantity,2);
        $data['total'] = number_format($row->total,2);
        $data['unit_type_id'] = $row->item->unit_type_id;
        $data['description'] = $row->item->description;*/
        $data['purchase_order'] = null;

        if ($row->document && $row->document->date_of_issue) {
            
            /** @var \App\Models\Tenant\Document $document */
            $document = $row->document;
            $row = DocumentItem::find($row->id);
            $data['date_of_issue'] = $document->date_of_issue->format('Y-m-d');
            $data['customer_name'] = $document->customer->name;
            $data['customer_number'] = $document->customer->number;
            $data['series'] = $document->series;
            $data['alone_number'] = $document->number;
            $data['document_type_description'] = $document->document_type->description;
            $data['document_type_id'] = $document->document_type->id;
            $data['currency_type_id'] = $document->currency_type_id;
            $data['purchase_order'] = $document->purchase_order;
            $data['additional_information'] = $document->additional_information;
        } else if ($row->purchase) {
            /** @var \App\Models\Tenant\Purchase $document */
            $document = $row->purchase;
            $data['date_of_issue'] = $document->date_of_issue->format('Y-m-d');
            $data['customer_name'] = $document->supplier->name;
            $data['customer_number'] = $document->supplier->number;
            $data['series'] = $document->series;
            $data['alone_number'] = $document->number;
            $data['document_type_description'] = $document->document_type->description;
            $data['document_type_id'] = $document->document_type->id;
            $data['currency_type_id'] = $document->currency_type_id;
        } else if ($row->sale_note && $row->sale_note->date_of_issue) {
            /** @var \App\Models\Tenant\SaleNote $document */
            $row = SaleNoteItem::find($row->id);
            $document = $row->sale_note;
            $data['date_of_issue'] = $document->date_of_issue->format('Y-m-d');
            $data['customer_name'] = $document->customer->name;
            $data['customer_number'] = $document->customer->number;
            $data['series'] = $document->series;
            $data['alone_number'] = $document->number;
            $data['document_type_description'] = 'NOTA DE VENTA';
            $data['document_type_id'] = 80;
            $data['currency_type_id'] = $document->currency_type_id;
            $data['purchase_order'] = $document->purchase_order;
            $data['observation'] = $document->observation;
        } else if ($row->quotation && $row->quotation->date_of_issue) {
            
            /** @var \App\Models\Tenant\Quotation $document */
            $row = QuotationItem::find($row->id);
            $document = $row->quotation;
            $data['date_of_issue'] = $document->date_of_issue->format('Y-m-d');
            $data['customer_name'] = $document->customer->name;
            $data['customer_number'] = $document->customer->number;
            $data['series'] = $document->prefix;
            $data['alone_number'] = $document->id;
            $data['document_type_description'] = 'COTIZACIÓN';
            $data['document_type_id'] = 'COT';
            $data['currency_type_id'] = $document->currency_type_id;
            $data['purchase_order'] = $document->purchase_order;
            $data['observation'] = $document->observation;
        }

        return $data;
    }
}
