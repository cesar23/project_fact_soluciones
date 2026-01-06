<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PlateNumberCollection extends ResourceCollection
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
            $last_document = $row->documents->last();
            $document = null;
            if($last_document){
                if($last_document->sale_note_id){
                    $document = $last_document->saleNote;
                }else if($last_document->quotation_id){
                    $document = $last_document->quotation;
                }else{
                    $document = $last_document->document;
                }
            }
            return [
                'id' => $row->id,
                'description' => $row->description,
                'brand' => $row->brand->description,
                'model' => $row->model->description,
                'color' => $row->color->description,
                'type' => $row->type->description,
                'year' => $row->year,
                'initial_km' => $row->initial_km,
                'km' => $row->kms->last()->description,
                'last_document' => $document ? $document->number_full : null,
                'last_document_total' => $document ? $document->total : null,
                'customer' => $row->person ? $row->person->number . ' - ' . $row->person->name : null,
            ];
        });
    }
}