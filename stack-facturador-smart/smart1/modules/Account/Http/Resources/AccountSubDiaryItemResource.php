<?php

namespace Modules\Account\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountSubDiaryItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request) {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'description' => $this->description,
            'general_description' => $this->general_description,
            'document_number' => $this->document_number,
            'correlative_number' => $this->correlative_number,
            'debit' => (bool) $this->debit,
            'credit' => (bool) $this->credit,
            'debit_amount' => $this->debit_amount,
            'credit_amount' => $this->credit_amount,
        ];
    }
}