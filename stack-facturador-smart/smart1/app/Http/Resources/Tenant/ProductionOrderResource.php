<?php

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\ProductionOrder;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductionOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $production_order = ProductionOrder::find($this->id);
        $production_order->load('items');
        return [
            'id' => $this->id,
            'external_id' => $this->external_id,  
            'identifier' => $this->identifier,
            'prefix' => $this->prefix,
            'number' => $this->number,
            'date_of_issue' => $this->date_of_issue->format('Y-m-d'), 
            'production_order' => $production_order,
            'print_ticket' => url('')."/production-order/print/{$this->external_id}/ticket",
            'print_a4' => url('')."/production-order/print/{$this->external_id}/a4",
            'print_a5' => url('')."/production-order/print/{$this->external_id}/a5",
            'print_receipt' => url('')."/production-order/receipt/{$this->id}",
            'print_ticket_58' => url('')."/production-order/print/{$this->external_id}/ticket_58",
            'print_ticket_50' => url('')."/production-order/print/{$this->external_id}/ticket_50",
        ];
    }
}
