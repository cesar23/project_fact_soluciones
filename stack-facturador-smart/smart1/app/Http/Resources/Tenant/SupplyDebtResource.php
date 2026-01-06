<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class SupplyDebtResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'supply_contract_id' => $this->supply_contract_id,
            'person_id' => $this->person_id,
            'supply_id' => $this->supply_id,
            'serie_receipt' => $this->serie_receipt,
            'correlative_receipt' => $this->correlative_receipt,
            'amount' => $this->amount,
            'year' => $this->year,
            'month' => $this->month,
            'generation_date' => $this->generation_date,
            'due_date' => $this->due_date,
            'active' => $this->active,
            'type' => $this->type,
            'supply_type_debt_id' => $this->supply_type_debt_id,
            'supply_concept_id' => $this->supply_concept_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}