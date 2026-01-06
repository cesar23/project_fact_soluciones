<?php

namespace Modules\Order\Http\Resources;

use Modules\Order\Models\OrderNote;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderNoteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $order_note = OrderNote::find($this->id);

        return [
            'id' => $this->id,
            'external_id' => $this->external_id,  
            'identifier' => $this->identifier,
            'full_number' => $this->number_full,
            'date_of_issue' => $this->date_of_issue->format('Y-m-d'), 
            'order_note' => $order_note,
            'print_ticket' => url('')."/order-notes/print/{$this->external_id}/ticket",
            'print_a4' => url('')."/order-notes/print/{$this->external_id}/a4",
            'print_a5' => url('')."/order-notes/print/{$this->external_id}/a5",
            'print_receipt' => url('')."/order-notes/receipt/{$this->id}",
            'print_ticket_58' => url('')."/order-notes/print/{$this->external_id}/ticket_58",
            'message_text' => "Su pedido {$this->number_full} ha sido generado correctamente, puede revisarlo en el siguiente enlace: ".url('')."/order-notes/print/{$this->external_id}/a4".''
        ];
    }
}
