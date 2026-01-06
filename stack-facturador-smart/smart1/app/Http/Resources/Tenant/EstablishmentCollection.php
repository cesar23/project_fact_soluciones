<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\ResourceCollection;

class EstablishmentCollection extends ResourceCollection
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
                'code' => $row->code,
                'description' => $row->description,
                'template_documents' => $row->template_documents,
                'template_sale_notes' => $row->template_sale_notes,
                'template_dispatches' => $row->template_dispatches,
                'template_quotations' => $row->template_quotations,
                'users' => $row->users->count(),
                'active' => (bool) $row->active,
                'gekawa_url' => $row->gekawa_url,
                'gekawa_1' => $row->gekawa_1,
                'gekawa_2' => $row->gekawa_2,
            ];
        });
    }

}