<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\ResourceCollection;

class MassiveMessageCollection extends ResourceCollection
{
    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        return $this->collection->transform(function ($row, $key) {
            return [
                'id' => $row->id,
                'subject' => $row->subject,
                'body' => $row->body,
                'created_at' => $row->created_at->format('Y-m-d'),
            ];
        });
    }
}
