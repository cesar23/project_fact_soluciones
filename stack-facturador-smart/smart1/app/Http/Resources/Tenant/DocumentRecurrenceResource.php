<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class DocumentRecurrenceResource  extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $document_number = ($this->document) ? $this->document->series . '-' . $this->document->number : null;
        $id = $this->document->id;
        $customer_name = $this->document->customer->name;
        $fist_emission = $this->items->where('emitted', false)->first();
        if ($fist_emission) {
            $initialDate = $fist_emission->emission_date;
            $initialTime = $fist_emission->emission_time;
        } else {
            $initialDate = null;
            $initialTime = null;
        }
        return [
            'id' => $id,
            'recurrence_id' => $this->id,
            'document_number' => $document_number,
            'number' => $document_number,
            'customer_name' => $customer_name,
            'interval' => $this->interval,
            'interval_translated' => $this->translateInterval(),
            'next_emission' => $this->items->where('emitted', false)->first()->emission_date,
            'last_emission' => $this->items->where('emitted', true)->last()->emission_date ?? '-',
            'initial_date' => $initialDate,
            'initial_time' => $initialTime,


        ];
    }
}
