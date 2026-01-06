<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;

class SupplyConcept extends ModelTenant
{
    protected $table = 'supply_concept';

    protected $fillable = [
        'name',
        'code',
        'cost',
        'type',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean'
    ];


}