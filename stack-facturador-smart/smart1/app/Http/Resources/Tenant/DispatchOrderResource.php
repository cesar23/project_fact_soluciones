<?php

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\Dispatch;
use App\Models\Tenant\DispatchOrder;
use Illuminate\Http\Resources\Json\JsonResource;

class DispatchOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $dispatch_order = DispatchOrder::find($this->id);
        $dispatches =Dispatch::select(['series','number'])->where('reference_dispatch_order_id',$this->id)->get();
        return [
            'dispatches' => $dispatches,
            'id' => $this->id,
            'external_id' => $this->external_id,  
            'identifier' => $this->identifier,
            'prefix' => $this->prefix,
            'number' => $this->number,
            'date_of_issue' => $this->date_of_issue->format('Y-m-d'), 
            'dispatch_order' => $dispatch_order,
            'print_ticket' => url('')."/dispatch-order/print/{$this->external_id}/ticket",
            'print_a4' => url('')."/dispatch-order/print/{$this->external_id}/a4",
            'print_a5' => url('')."/dispatch-order/print/{$this->external_id}/a5",
            'print_receipt' => url('')."/dispatch-order/receipt/{$this->id}",
            'print_ticket_58' => url('')."/dispatch-order/print/{$this->external_id}/ticket_58",
            'print_ticket_50' => url('')."/dispatch-order/print/{$this->external_id}/ticket_50",
        ];
    }
}
