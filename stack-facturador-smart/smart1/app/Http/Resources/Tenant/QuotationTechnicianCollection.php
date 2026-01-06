<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\ResourceCollection;

class QuotationTechnicianCollection extends ResourceCollection
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
                'name' => $row->name,
                'number' => $row->number,
                'email' => $row->email,
                'phone' => $row->phone,
                'image' => $row->image,
                'image_url' => $row->image_url,
                'image_path' => $row->image_path,
                'created_at' => $row->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $row->updated_at->format('Y-m-d H:i:s'),
                'btn_options' => $row->btn_options,
            ];
        });
    }
}