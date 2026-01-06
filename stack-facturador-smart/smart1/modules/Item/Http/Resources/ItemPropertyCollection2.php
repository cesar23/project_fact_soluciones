<?php

namespace Modules\Item\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ItemPropertyCollection2 extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {
        return $this->collection->transform(function ($row, $key) {
            $lot_code = isset($row->item_loteable->lot_code) ? $row->item_loteable->lot_code : null;
            return [
                'id'           => $row->id,
                'item_id'        => $row->item_id,
                'warehouse_id'   => $row->warehouse_id,
                'chassis'        => $row->chassis,
                'attribute'       => $row->attribute,
                'attribute2'     => $row->attribute2,
                'attribute3'     => $row->attribute3,
                'attribute4'     => $row->attribute4,
                'attribute5'     => $row->attribute5,
                'state'          => $row->state,
                 'has_sale'     => (bool)$row->has_sale,
                
            ];
        });
    }
}
