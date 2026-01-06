<?php

namespace Modules\Sale\Models;

use App\Models\Tenant\{
    ModelTenant
};


class TechnicalServiceImage extends ModelTenant
{

    protected $table = 'technical_services_images';
    public $timestamps = false;
    protected $fillable = [
        'technical_service_id',
        'image_path',
    ];
 
    
    /**
     * Datos para listado/edicion
     *
     * @return void
     */
    public function getRowResource()
    {
        return [
            'id' => $this->id,
            'image_path' => $this->image_path,
            ];
    }


    public function technical_service()
    {
        return $this->belongsTo(TechnicalService::class);
    }

}
