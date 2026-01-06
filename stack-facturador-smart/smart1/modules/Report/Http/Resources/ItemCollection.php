<?php

namespace Modules\Report\Http\Resources;

use App\Models\Tenant\PurchaseItem;
use App\Models\Tenant\QuotationItem;
use App\Models\Tenant\SaleNoteItem;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ItemCollection extends ResourceCollection
{


    public function toArray($request) {

        $type = isset($request['type']) ? $request['type'] : null;
        return $this->collection->transform(function ($row, $key)  use ($type) {
            $document_description =  null;

            $observation = null;
            $class = get_class($row);
            if ($class == PurchaseItem::class) {
                /** @var \App\Models\Tenant\PurchaseItem $row */
                $document = $row->purchase;
                $customer_name = $document->supplier->name;
                $customer_number = $document->supplier->number;
                /** @var \App\Models\Tenant\PurchaseItem $row */
                $purchase = $row->purchase;
                $observation=$purchase->observation;
                $document_description = $document->document_type->description;
            }
            else if ($class == SaleNoteItem::class) {
                /** @var \App\Models\Tenant\SaleNoteItem $row */
                $document = $row->sale_note;
                $customer_name = $document->customer->name;
                $customer_number = $document->customer->number;
                $document_description = "NOTA DE VENTA";
            } else if ($class == QuotationItem::class) {
                /** @var \App\Models\Tenant\QuotationItem $row */
                $document = $row->quotation;
                $customer_name = $document->customer->name;
                $customer_number = $document->customer->number;
                $document_description = "COTIZACIÓN";
            }
            else {
                /** @var \App\Models\Tenant\DocumentItem $row */
                $document = $row->document;
                $customer_name = $document->customer->name;
                $customer_number = $document->customer->number;
                $document_description = $document->document_type->description;
            }
            return [
                'id'                        => $row->id,
                'date_of_issue'             => $document->date_of_issue->format('Y-m-d'),
                'customer_name'             => $customer_name,
                'customer_number'           => $customer_number,
                'series'                    => $document->series,
                'alone_number'              => $document->number,
                'quantity'                  => number_format($row->quantity, 2),
                'total'                     => (in_array($document->document_type_id,
                                                         ['01', '03']) && in_array($document->state_type_id,
                                                                                   ['09', '11'])) ? number_format(0, 2)
                    : number_format($row->total, 2),
                'document_type_description' => $document_description,
                'document_type_id'          =>isset( $document->document_type->id) ?  $document->document_type->id : null,
                'web_platform_name'         => optional($row->relation_item->web_platform)->name,
                'observation'=>$observation,

            ];
        });
    }
}
