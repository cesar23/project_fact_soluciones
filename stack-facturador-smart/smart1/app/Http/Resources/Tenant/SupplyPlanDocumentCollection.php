<?php

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\SupplyPlanDocument;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SupplyPlanDocumentCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->transform(function(SupplyPlanDocument $row, $key) {
            $document = $row->document ?? $row->sale_note;
            $print_ticket = null;
            if($row->document_id){
                $print_ticket = url('')."/print/document/{$row->document->external_id}/ticket";
            }else{
                $print_ticket = url('')."/sale-notes/print/{$row->sale_note->external_id}/ticket";
            }
            $document_series = $document->series;
            $document_number = $document->number;
            $full_document_number = $document->number_full;
            return [
                'id' => $row->id,
                'year' => $row->year,
                'month' => $row->month,
                'period' => $row->period,
                'status' => $row->status,
                'status_label' => $row->status_label,
                'generation_date' => $row->generation_date ? $row->generation_date->format('d/m/Y') : null,
                'due_date' => $row->due_date ? $row->due_date->format('d/m/Y') : null,
                'amount' => $row->amount,
                'document_series' => $document_series,
                'document_number' => $document_number,
                'full_document_number' => $full_document_number,
                'observations' => $row->observations,
                'supply_plan_registered_id' => $row->supply_plan_registered_id,
                'user_id' => $row->user_id,
                'document_id' => $row->document_id,
                'sale_note_id' => $row->sale_note_id,
                'is_debt_payment' => $row->is_debt_payment,
                'is_cancelled' => $row->is_cancelled,
                'cancelled_at' => $row->cancelled_at ? $row->cancelled_at->format('Y-m-d H:i:s') : null,
                'print_ticket' => $print_ticket,
                'document' => $row->document ? [
                    'id' => $row->document->id,
                    'state_type_id' => $row->document->state_type_id,
                ] : null,
                'sale_note' => $row->sale_note ? [
                    'id' => $row->sale_note->id,
                    'state_type_id' => $row->sale_note->state_type_id,
                ] : null,
                'created_at' => $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : null,
                'updated_at' => $row->updated_at ? $row->updated_at->format('Y-m-d H:i:s') : null
            ];
        });
    }
}