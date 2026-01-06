<?php

namespace App\Http\Resources\Tenant;

use App\CoreFacturalo\Helpers\QrCode\QrCodeGenerate;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CanchasTypeCollection extends ResourceCollection
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
            // $qrCode = new QrCodeGenerate();
    
            // $qrCodeImage = $qrCode->displayPNGBase64($row->ticket,300);
    
            
            return [
                'id' => $row->id,
                'name' => $row->nombre,
                'location' => $row->ubicacion,
                'capacity' => $row->capacidad,
                'description' => $row->description,

            ];
        });
    }
}