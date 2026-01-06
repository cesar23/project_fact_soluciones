<?php

namespace Modules\Account\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AccountPeriodCollection extends ResourceCollection
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
                'id' => $row->id,
                'year' => optional($row->year)->format('Y'),
                'total_debit' => $row->total_debit,
                'total_credit' => $row->total_credit,
                'balance' => $row->balance,
                'created_at' => optional($row->created_at)->format('Y-m-d H:i:s'),
                'updated_at' => optional($row->updated_at)->format('Y-m-d H:i:s'),
            ];
        });
    }
} 