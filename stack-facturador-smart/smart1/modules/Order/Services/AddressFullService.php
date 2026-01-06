<?php

namespace Modules\Order\Services;

use App\Models\Tenant\Catalogs\Department;
use App\Models\Tenant\Catalogs\District;
use App\Models\Tenant\Catalogs\Province;


class AddressFullService
{

    public static function getDescription($district_id)
    {
        if($district_id === null) return null;
        $district = District::findOrFail($district_id);

        return "{$district->province->department->description} - {$district->province->description} - {$district->description}";
        
    }
    

}