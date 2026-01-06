<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DocumentRecurrenceCollection extends ResourceCollection
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
            $document_number = ($row->document) ? $row->document->series.'-'.$row->document->number : null;
            return [
                'id' => $row->id,
                'document_number' => $document_number,
                'interval' => $row->interval,
                'interval_translated' => $row->translateInterval(),
                'next_emission' => $row->items->where('emitted', false)->first() ? $row->items->where('emitted', false)->first()->emission_date : null,
                'last_emission' => $row->items->where('emitted', true)->last()->emission_date ?? '-',
                
            ];
        });
    }
}