<?php

namespace Modules\Hotel\Http\Resources;

use Modules\Order\Models\OrderNote;
use Illuminate\Http\Resources\Json\JsonResource;

class HotelReservationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'reservation_date' => $this->reservation_date ? $this->reservation_date->format('Y-m-d') : null,
            'reservation_method' => $this->reservation_method,
            'departure_time' => $this->departure_time ? $this->departure_time->format('H:i:s') : null,
            'name' => $this->name,
            'document' => $this->document,
            'sex' => $this->sex,
            'age' => $this->age,
            'room_id' => $this->room_id,
            'number_of_nights' => $this->number_of_nights,
            'breakfast_type' => $this->breakfast_type,
            'check_in_date' => $this->check_in_date ? $this->check_in_date->format('Y-m-d') : null,
            'check_out_date' => $this->check_out_date ? $this->check_out_date->format('Y-m-d') : null,
            'arrival_time' => $this->arrival_time ? $this->arrival_time->format('H:i:s') : null,
            'transfer_in' => $this->transfer_in,
            'transfer_out' => $this->transfer_out,
            'nightly_rate' => $this->nightly_rate,
            'total_amount' => $this->total_amount,
            'agency' => $this->agency,
            'contact' => $this->contact,
            'created_by' => $this->created_by,
            'observations' => $this->observations,
            'customer_id' => $this->customer_id,
            'sale_note_id' => $this->sale_note_id,
            'paid' => $this->sale_note ? $this->sale_note->total : 0,
            'payments' => $this->sale_note ? $this->sale_note->payments : []

        ];
    }
}
