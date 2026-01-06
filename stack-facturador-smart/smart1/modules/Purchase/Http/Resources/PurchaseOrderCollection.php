<?php

namespace Modules\Purchase\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PurchaseOrderCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->transform(function ($row, $key) {
            $customer_name = "";
            $quotation_number = "";
            if ($row->quotation) {
                $customer_name = $row->quotation->customer->name;
                $quotation_number = $row->quotation->number_full;
            }
            return [
                'customer_name' => $customer_name,
                'quotation_number' => $quotation_number,
                'id' => $row->id,
                'prefix' => $row->prefix,
                'created_by_id' => $row->created_by_id,
                'approved_by_id' => $row->approved_by_id,
                'client_internal_id' => $row->client_internal_id,
                'quotation_id' => $row->quotation_id,
                'type' => $row->type,
                'observation' => $row->observation,
                'items'=> $row->items->transform(function ($row, $key) {
                    return [
                        'key'         => $key + 1,
                        'id'          => $row->id,
                        'description' => $row->item->description,
                        'quantity'    => round($row->quantity, 2),
                        'unit_price' => round($row->unit_price, 2),
                        'total' => number_format($row->total, 2,".",""),
                    ];
                }),
                // 'purchases' => $row->purchases,
                'has_purchases' => ($row->purchases->count()) ? true : false,
                'soap_type_id' => $row->soap_type_id,
                'external_id' => $row->external_id,
                'date_of_issue' => $row->date_of_issue->format('Y-m-d'),
                'date_of_due' => ($row->date_of_due) ? $row->date_of_due->format('Y-m-d') : '-',
                // Para compatibilidad: mostrar series/number solo si existen, sino null
                'series' => $row->series ?: null,
                'number' => $row->number ?: null,
                'number_full' => $row->number_full,
                // Para registros antiguos, generar el formato compatible
                'legacy_number' => $row->series ? null : ($row->prefix . '-' . str_pad($row->id, 8, '0', STR_PAD_LEFT)),
                'supplier_name' => $row->supplier->name,
                'supplier_number' => $row->supplier->number,
                'currency_type_id' => $row->currency_type_id,
                'total_exportation' => $row->total_exportation,
                'total_free' => number_format($row->total_free, 2),
                'total_unaffected' => number_format($row->total_unaffected, 2),
                'total_exonerated' => number_format($row->total_exonerated, 2),
                'total_taxed' => number_format($row->total_taxed, 2),
                'total_igv' => number_format($row->total_igv, 2),
                'total' => number_format($row->total, 2),
                'state_type_id' => $row->state_type_id,
                'state_type_description' => $row->state_type->description,
                // 'payment_method_type_description' => isset($row->purchase_payments['payment_method_type']['description'])?$row->purchase_payments['payment_method_type']['description']:'-',
                'created_at' => $row->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $row->updated_at->format('Y-m-d H:i:s'),
                'sale_opportunity_number_full' => ($row->sale_opportunity) ? $row->sale_opportunity->number_full : $row->sale_opportunity_number,
                'show_actions_row' => $row->getShowActionsRow(),

            ];
        });
    }
}
