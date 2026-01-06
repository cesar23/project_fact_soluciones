<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SupplyAdvancePaymentCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return $this->collection->transform(function($row, $key) {
            return [
                'id' => $row->id,
                'supply_id' => $row->supply_id,
                'supply_name' => $row->supply->old_code . ' - ' . $row->supply->cod_route,
                'person_name' => $row->supply->person->name . ' - ' . $row->supply->person->number,
                'amount' => floatval($row->amount),
                'payment_date' => $row->payment_date->format('Y-m-d'),
                'payment_date_formatted' => $row->payment_date->format('d/m/Y'),
                'year' => $row->year,
                'month' => $row->month,
                'period' => $row->month . ' - ' . $row->year,
                'active' => $row->active,
                'state' => $row->active ? 'Activo' : 'Inactivo',
                'document_type_id' => $row->document_type_id,
                'document_type_name' => $row->document_type_id === '03' ? 'BOLETA ELECTRONICA' : 'NOTA DE VENTA',
                'created_at' => $row->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $row->updated_at->format('Y-m-d H:i:s'),
            ];
        });
    }
}