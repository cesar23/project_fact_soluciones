<?php

namespace Modules\Account\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class LedgerAccountCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->transform(function($row, $key) {
            return [
                'code' => $row->code,
                'name' => $row->name,
                'can_edit' => strlen($row->code) == 6,
                'active' => (bool) $row->active,
            ];
        });
    }
} 