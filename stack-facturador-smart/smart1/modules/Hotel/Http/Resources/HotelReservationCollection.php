<?php

namespace Modules\Hotel\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class HotelReservationCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return $this->collection->transform(function ($row) {
            $sale_note = $row->sale_note;

            return [
                'id' => $row->id,
                'row_date' => $row->row_date,
                'row_method' => $row->row_method,
                'name' => $row->name,
                'document' => $row->document,
                'sex' => $row->sex,
                'age' => $row->age,
                'room_id' => $row->room_id,
                'room' => $row->room,
                'number_of_nights' => $row->number_of_nights,
                'breakfast_type' => $row->breakfast_type,
                'check_in_date' => $row->check_in_date->format('Y-m-d'),
                'check_out_date' => $row->check_out_date ? $row->check_out_date->format('Y-m-d') : null,
                'arrival_time' => $row->arrival_time ? $row->arrival_time->format('H:i:s') : null,
                'departure_time' => $row->departure_time ? $row->departure_time->format('H:i:s') : null,
                'transfer_in' => $row->transfer_in,
                'transfer_out' => $row->transfer_out,
                'nightly_rate' => $row->nightly_rate,
                'total_amount' => $row->total_amount,
                'agency' => $row->agency,
                'contact' => $row->contact,
                'created_by' => $row->created_by,
                'active' => (bool) $row->active,
                'observations' => $row->observations,
                'sale_note_pdf' => $sale_note ? url('') . "/sale-notes/print/{$sale_note->external_id}/a4" : '' ,

            ];
        });
    }
}
