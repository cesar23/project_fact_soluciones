<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class WarrantyDocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'sale_note_id' => $this->sale_note_id,
            'document_id' => $this->document_id,
            'quantity' => $this->quantity,
            'total' => $this->total,
            'comment' => $this->comment,
        ];
        
    }
}