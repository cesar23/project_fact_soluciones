<?php

namespace Modules\Restaurant\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpensesResource extends JsonResource
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
            'group_id' => $this->group_id,
            'category_id' => $this->category_id,
            'subcategory_id' => $this->subcategory_id,
            'user_id' => $this->user_id,
            'establishment_id' => $this->establishment_id,
            'sale_note_payment_id' => $this->sale_note_payment_id,
            'amount' => $this->amount,
            'date' => $this->date,
            'soap_type_id' => $this->soap_type_id,
            'description' => $this->description,
            'type' => $this->type,
            'method' => $this->method,
            'close' => $this->close,
            'sale_note_id' => $this->sale_note_id,
            'document_id' => $this->document_id,
            'state' => $this->state,
            // 'more_address' =>  $this->more_address,
        ];
    }
}
