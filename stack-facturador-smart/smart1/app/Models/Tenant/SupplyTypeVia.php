<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;

class SupplyTypeVia extends ModelTenant
{
    protected $table = 'supply_type_via';
    protected $fillable = [
        'code',
        'description',
        'short'
    ];

    public $timestamps = false;
}

