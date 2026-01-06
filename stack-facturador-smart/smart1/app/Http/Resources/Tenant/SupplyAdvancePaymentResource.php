<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class SupplyAdvancePaymentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'supply_id' => $this->supply_id,
            'supply_name' => $this->supply->old_code . ' | ' . $this->supply->cod_route,
            'person_name' => $this->supply->person->name,
            'person_number' => $this->supply->person->number,
            'sector_name' => $this->supply->sector ? $this->supply->sector->name : null,
            'via_name' => $this->supply->supplyVia ? $this->supply->supplyVia->name : null,
            'amount' => floatval($this->amount),
            'payment_date' => $this->payment_date->format('Y-m-d'),
            'payment_date_formatted' => $this->payment_date->format('d/m/Y'),
            'year' => $this->year,
            'month' => $this->month,
            'active' => $this->active,
            'document_type_id' => $this->document_type_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}