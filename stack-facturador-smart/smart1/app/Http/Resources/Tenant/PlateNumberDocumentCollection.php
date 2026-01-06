<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PlateNumberDocumentCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        return $this->collection->transform(function($row, $key) {
            $plate_number = $row->plateNumber;
            $document = $row->getDocument();
            $ticket_pdf = get_document_pdf_ticket($document);
            
            return [
                'id' => $row->id,
                'description' => $plate_number->description,
                'brand' => $plate_number->brand->description,
                'model' => $plate_number->model->description,
                'color' => $plate_number->color->description,
                'type' => $plate_number->type->description,
                'year' => $plate_number->year,
                'km' => $row->km,
                'document' => $document ? $document->number_full : null,
                'document_total' => $document ? $document->total : null,
                'ticket_pdf' => $ticket_pdf,
            ];
        });
    }
}