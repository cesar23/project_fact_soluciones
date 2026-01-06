<?php

namespace Modules\BusinessTurn\Models;

use App\Models\Tenant\Catalogs\Department;
use App\Models\Tenant\Catalogs\District;
use App\Models\Tenant\Catalogs\Province;
use App\Models\Tenant\ModelTenant;

class AgencyTransport extends ModelTenant
{
    public $timestamps = false;
    protected $fillable = [
        'description',
        'ubigeo',
        'address',
    ];



    public function getUbigeoAttribute($value)
    {
        return (is_null($value)) ? null : json_decode($value);
    }

    public function setUbigeoAttribute($value)
    {
        $this->attributes['ubigeo'] = (is_null($value)) ? null : json_encode($value);
    }
    /**
     * Datos para listado/edicion
     *
     * @return void
     */
    public function getRowResource()
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'ubigeo_format' => $this->format_ubigeo(),
            'ubigeo' => $this->ubigeo,
            'address' => $this->address,
        ];
    }

    function format_ubigeo()
    {
        $ubigeo = $this->ubigeo;
    
        //ubigeo es un array de 3 elementos
        $department = $ubigeo[0];
        $province = $ubigeo[1];
        $district = $ubigeo[2];
        $department = Department::find($department);
        $province = Province::find($province);
        $district = District::find($district);
        return $department->description . ' - ' . $province->description . ' - ' . $district->description;
    }
}
