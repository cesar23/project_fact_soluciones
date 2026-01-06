<?php

namespace App\Http\Resources\Tenant;

use App\CoreFacturalo\Helpers\QrCode\QrCodeGenerate;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CanchasCollection extends ResourceCollection
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
            if($row->customer_id){
                $name = $row->customer->name;
                $phone = $row->customer->telephone;
            }else{
                $name = $row->reservante_nombre.' '.$row->reservante_apellidos;
                $phone = $row->numero;
            }
            if($row->type_id){
                $name_location = $row->type->nombre;
                $location = $row->type->ubicacion;
                $capacity = $row->type->capacidad;
            }else{
                $name_location = $row->nombre;
                $location = $row->ubicacion;
                $capacity = $row->capacidad;
            }

            return [
                'id' => $row->id,
                'phone' => $phone,
                'name' => $name_location,
                'location' => $location,
                'capacity' => $capacity,
                'description' => $row->description,
                'reservante' => $name,
                'time' => $row->hora_reserva,
                'duration' => $row->tiempo_reserva,
                'date' => $row->fecha_reserva,
                'ticket' => $row->ticket,
                'anulado'  => (bool) $row->anulado,

            ];
        });
    }
}