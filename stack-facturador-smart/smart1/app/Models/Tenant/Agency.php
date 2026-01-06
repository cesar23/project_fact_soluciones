<?php

namespace App\Models\Tenant;

use Hyn\Tenancy\Abstracts\TenantModel;

class Agency extends TenantModel
{
    protected $table = 'agencies';
    public $timestamps = false;
    protected $fillable = [
        'description',
        'active',
    ];

}
