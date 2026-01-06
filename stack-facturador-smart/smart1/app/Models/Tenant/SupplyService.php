<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;

class SupplyService extends ModelTenant
{
    protected $table = 'supply_service';

    protected $fillable = [
        'name'
    ];
}
