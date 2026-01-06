<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DocumentRecurrenceItemCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        return $this->collection->transform(function($row, $key) {
            return [
                'id' => $row->id,
                'emission_date' => $row->emission_date,
                'emission_time' => $row->emission_time,
                'emitted' => $row->emitted,
                'email_sent' => $row->email_sent,
                'whatsapp_sent' => $row->whatsapp_sent,
                'send_email' => $row->send_email,
                'send_whatsapp' => $row->send_whatsapp,
            ];
        });
    }
}