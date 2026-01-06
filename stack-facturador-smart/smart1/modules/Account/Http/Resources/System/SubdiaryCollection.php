<?php

namespace Modules\Account\Http\Resources\System;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SubdiaryCollection extends ResourceCollection
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
                'code' => $row->code,
                // 'prefix' => method_exists($row, 'generatePrefix') ? $row->generatePrefix() : $row->prefix,
                'prefix' => null,
                'date' => $row->date,
                'description' => $row->description,
                'book_code' => $row->book_code,
                'items' => $row->items->transform(function($item, $key) {
                    return [
                        'code' => $item->code,
                        'general_description' => $item->general_description,
                        'description' => $item->description,
                        'debit' => (bool) $item->debit,
                        'credit' => (bool) $item->credit,
                        'debit_amount' => $item->debit_amount,
                        'credit_amount' => $item->credit_amount,
                    ];
                }),
            ];
        });
    }
}