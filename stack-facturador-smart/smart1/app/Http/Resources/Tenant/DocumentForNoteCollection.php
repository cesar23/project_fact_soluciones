<?php

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\Company;
use App\Models\Tenant\EmailSendLog;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DocumentForNoteCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        return $this->collection->transform(function ($row, $key) {
            
            return[
                'id' => $row->id,
                'checked' => false,
                'date_of_issue' => $row->date_of_issue->format('Y-m-d'),
                'customer_name' => $row->customer->name,
                'customer_number' => $row->customer->number,
                'number_full' => $row->number_full,
                'total' => $row->total,
            ];
        });
    }


}
