<?php

namespace Modules\Report\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class OrderNoteVariableCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // Asegúrate de que las relaciones order_note.user, order_note.customer e item 
        // estén precargadas en el controlador usando with():
        // $query->with(['order_note.user', 'order_note.customer', 'item'])

        return $this->collection->map(function ($row) {
            return [
                'id' => $row->id,
                'customer' => $row->customer_name,
                'customer_name' => $row->customer_name,
                'customer_number' => $row->customer_number,
                'person_type' => $row->person_type,
                'delivery_date' => $row->delivery_date ?? "-",
                'item_description' => $row->item_description ?? '',
                'item_quantity' => $row->quantity,
                'unit_price' => $row->unit_price,
                'total' => $row->total,
                'created_time' => $this->formatDate($row->created_time),
                'item_id' => $row->item_id,
            ];
        })->all();
    }
    private function formatDate($full_date = null){
        if($full_date){
            $explode_date = explode(' ', $full_date);
            return $explode_date[1];
        }
        return '-';
    }
}
