<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class EstablishmentResource extends JsonResource
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
            'gekawa_url' => $this->gekawa_url,
            'gekawa_1' => $this->gekawa_1,
            'gekawa_2' => $this->gekawa_2,
            'active' => (bool) $this->active,
            'print_format' => $this->print_format,
            'id' => $this->id,
            'description' => $this->description,
            'country_id' => $this->country_id,
            'department_id' => $this->department_id,
            'province_id' => $this->province_id,
            'district_id' => $this->district_id,
            'address' => $this->address,
            'telephone' => $this->telephone,
            'email' => $this->email,
            'code' => $this->code,
            'yape_owner' => $this->yape_owner,
            'yape_number' => $this->yape_number,
            'yape_logo' => $this->yape_logo,
            'plin_owner' => $this->plin_owner,
            'plin_number' => $this->plin_number,
            'plin_logo' => $this->plin_logo,
            'trade_address' => $this->trade_address,
            'web_address' => $this->web_address,
            'aditional_information' => $this->aditional_information,
            'customer_id' => $this->customer_id,
            'customer_number' => optional($this->customer)->number,
            'logo' => $this->logo ? asset($this->logo) : null,
            'has_igv_31556' => $this->has_igv_31556,
            'printer' => $this->printer,
            'template_documents' => $this->template_documents,
            'template_sale_notes' => $this->template_sale_notes,
            'template_dispatches' => $this->template_dispatches,
            'template_quotations' => $this->template_quotations,
        ];
    }
}

