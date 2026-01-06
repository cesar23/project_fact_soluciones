<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;

class Sector extends ModelTenant
{
    protected $fillable = [
        'name',
        'code'
    ];

    public $timestamps = false;
}