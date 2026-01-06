<?php

namespace Modules\Restaurant\Http\Resources;

use App\Models\Tenant\User;
use App\Models\Tenant\Person;
use App\Models\Tenant\Document;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\Configuration;
use Illuminate\Support\Facades\DB;
use Modules\Restaurant\Models\Orden;
use Modules\Restaurant\Models\Table;
use Illuminate\Support\Facades\Storage;
use Modules\Restaurant\Models\OrdenItem;
use Modules\Restaurant\Models\StatusOrden;
use Illuminate\Http\Resources\Json\ResourceCollection;

class OrdenCollection extends ResourceCollection
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
            $configuration = Configuration::first();
            if($row->sale_note_id != null){
                $document_type_id="80";
                $document_type="NOTA DE VENTA";
                $document_number=$row->salenote->series." - ".$row->salenote->number;
                $total=$row->salenote->total;
            }else if($row->document != null){
                $document_type_id="01";
                $document_number=$row->document->series." - ".$row->document->number;
                $document_type=$row->document->document_type->description;
                $total=$row->document->total;

            }else{
                $document_type=null;
                $document_number="";
                $document_type=null;
                $total=0;
                $document_type_id=null;
            }
             return [
                'table'             => $row->mesa->number,
                'id'                => ($configuration->commands_fisico=="1") ? $row->commands_fisico : $row->id,
                 'date'              => \Carbon\Carbon::parse($row->date)->format('d-m-Y'),
                 'status_orden_id'   => $row->status_orden->description,
                 'document_type'     =>  $document_type,
                 'document_type_id'  => $document_type_id,
                 'document_number'   => $document_number,
                 'status'           =>  $row->status_orden->description,
                 'status_id'          => $row->status_orden->id,
                 'customer'          => optional($row->customer)->name,
                 'document'          => $row->document,
                 'sale_note'         => $row->salenote,
                'orden_items'       => $row->orden_items,
                 'total'             =>  $total
            ];
        });
    }
}
