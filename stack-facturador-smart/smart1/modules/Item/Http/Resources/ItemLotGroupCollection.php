<?php

namespace Modules\Item\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ItemLotGroupCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        return $this->collection->transform(function ($row, $key) {



            $status = $row->state;

            $file = $row->file;
            if ($file) {
                $file = storage_path("app/public/uploads/items/" . $file);
                if (file_exists($file)) {
                    $file = url('storage/uploads/items/' . $row->file);
                } else {
                    $file = null;
                }
            }
            return [
                'id' => $row->id,
                'code' => $row->code,
                'item_description' => $row->item->description,
                'date' => $row->date_of_due,
                'state_id' => optional($status)->id,
                'item_id' => $row->item_id,
                'stock' => $row->quantity,
                'warehouse_id' => $row->warehouse_id,
                'file' => $file,
            ];
        });
    }
}
