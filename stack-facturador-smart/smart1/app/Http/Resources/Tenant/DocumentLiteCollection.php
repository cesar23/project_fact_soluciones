<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DocumentLiteCollection extends ResourceCollection
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
                'state_type_description' => $row->state_type->description,
                'company_name' => $row->company->name,
                'total' => $row->total,
                'number' => $row->number_full,
                'customer_name' => $row->customer->name,
                'date_of_issue' => $row->date_of_issue->format('Y-m-d'),
            ];
        });
    }
}
