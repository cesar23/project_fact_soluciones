<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\ResourceCollection;

class WarrantyDocumentCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        return $this->collection->transform(function ($row, $key) {
            $document = $row->document ?? $row->sale_note;
            $document_type_id = $document->document_type_id;
            $document_type = "NOTA DE VENTA";
            if($document_type_id == '01') {
                $document_type = "FACTURA";
            }
            if($document_type_id == '03') {
                $document_type = "BOLETA";
            }
            $quantity = intval($row->quantity);
            return [
                'date_cancel' => $row->date_cancel ? $row->date_cancel->format('Y-m-d') : null,
                'id' => $row->id,
                'document_type' => $document_type,
                'date_of_issue' => $document->date_of_issue->format('Y-m-d'),
                'series' => $document->series,
                'number' => $document->number,
                'customer_name' => $document->customer->name,
                'quantity' => $quantity,
                'total_returned' => $row->state ? 0 : $quantity,
                'debit' =>  $row->state ? $quantity : 0,
                'total' => $row->total,
                'state' => (bool)$row->state,
            
            ];
        });
    }
}
