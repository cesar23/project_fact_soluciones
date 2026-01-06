<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class SupplyProcessResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'record' => $this->record,
            'user_id' => $this->user_id,
            'supply_id' => $this->supply_id,
            'document' => $this->document,
            'document_date' => $this->document_date,
            'receive_date' => $this->receive_date,
            'assign_date' => $this->assign_date,
            'year' => $this->year,
            'subject' => $this->subject,
            'supply_office_id' => $this->supply_office_id,
            'state' => $this->state,
            'location' => $this->location,
            'contact_person' => $this->contact_person,
            'contact_phone' => $this->contact_phone,
            'observation_document' => $this->observation_document,
            'observation_finish' => $this->observation_finish,
            'n_folios' => $this->n_folios
        ];
    }
}