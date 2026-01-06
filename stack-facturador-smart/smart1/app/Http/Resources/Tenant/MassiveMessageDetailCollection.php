<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\ResourceCollection;

class MassiveMessageDetailCollection extends ResourceCollection
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
                'person' => $row->person->name,
                'telephone' => $row->person->telephone,
                'message' => $row->message,
                'status' => $row->status,
                'attempts' => $row->attempts,
                'last_attempt_at' => $row->last_attempt_at->format('Y-m-d'),
            ];
        });
    }
}
