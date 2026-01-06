<?php

namespace Modules\Inventory\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Carbon\Carbon;


class ReportKardexAttributesCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        return $this->collection->transform(function($row, $key)  {

            $diff = '';

            if($row->date_of_due)
            {
                $now = Carbon::now();
                $due =   Carbon::parse($row->date_of_due);
                $diff = $now->diffInDays($due);
            }

            return [
                'id' => $row->id,
                'warehouse'  => $row->warehouse_id==null ?  "" : optional($row->warehouse)->description,
                'name_item'  => $row->item->description,
                'has_sale'   => $row->has_sale == true ? "Vendido" : "Disponible", 
                'chassis'    => $row->chassis,
                'attribute'  => $row->attribute,
                'attribute2' => $row->attribute2,
                'attribute3' => $row->attribute3,
                'attribute4' => $row->attribute4,
                'attribute5' => $row->attribute5,
                'state'      => $row->state==true ? 'Activo' : 'No Activo',
                ];
        });
    }




}
