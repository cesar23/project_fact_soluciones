<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;

class SupplyOffice extends ModelTenant
{
    protected $table = 'supply_office';

    protected $fillable = [
        'name',
        'description',
    ];

    public $timestamps = false;
}