<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Catalogs\Country;
use App\Models\Tenant\Catalogs\Department;
use App\Models\Tenant\Catalogs\District;
use App\Models\Tenant\Catalogs\Province;
use Modules\Inventory\Models\Warehouse;

class UnitMeasure extends ModelTenant
{
    protected $table = "unit_measure";
     protected $fillable = [
        'code',
        'description'
    ];

      
}
