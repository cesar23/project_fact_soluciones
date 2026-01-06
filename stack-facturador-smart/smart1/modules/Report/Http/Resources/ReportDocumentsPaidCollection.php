<?php

namespace Modules\Report\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;


class ReportDocumentsPaidCollection extends ResourceCollection
{


    public function toArray($request)
    {


        return $this->collection->transform(function ($row, $key) {

            return [
                'id' => $row->id,
                'document_type_id' => $row->document_type_id,
                'date_of_issue' => $row->date_of_issue,
                'series' => $row->series,
                'number' => $row->number,
                'customer_id' => $row->customer_id,
                'total' => $row->total,
                'state_type_id' => $row->state_type_id,
                'establishment_id' => $row->establishment_id,
                'seller_id' => $row->seller_id,
                'total_paid' => $row->total_paid,
                'items' => array_map(function ($item) {
                    return [
                        'item_id' => $item['item_id'],
                        'item' => json_decode($item['item'], true)
                    ];
                }, json_decode($row->items, true))
            ];
        });
    }
}
