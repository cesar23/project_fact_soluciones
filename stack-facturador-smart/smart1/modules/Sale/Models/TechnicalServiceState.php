<?php

namespace Modules\Sale\Models;

use App\Models\Tenant\{
    ModelTenant
};


class TechnicalServiceState extends ModelTenant
{

    protected $table = 'technical_services_states';
    public $timestamps = false;
    protected $fillable = [
        'name',
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
            'name' => $this->name,
        ];
    }


    public function technical_services()
    {
        return $this->hasMany(TechnicalService::class);
    }
}
