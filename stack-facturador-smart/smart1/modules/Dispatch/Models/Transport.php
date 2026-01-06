<?php

namespace Modules\Dispatch\Models;

use App\Models\Tenant\ModelTenant;

class Transport extends ModelTenant
{
    protected $fillable = [
        'auth_plate_primary',
        'auth_plate_secondary',
        'model',
        'brand',
        'tuc',
        'tuc_secondary',
        'plate_number', 'secondary_plate_number',
        'is_default',
        'is_active',
        'configuration'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];
}
