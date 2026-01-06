<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;

class SupplyTypeDebt extends ModelTenant
{
    protected $table = 'supply_type_debt';

    protected $fillable = [
        'description',
        'code',
    ];

    public $timestamps = false;
}