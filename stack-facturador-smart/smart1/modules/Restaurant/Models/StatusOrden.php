<?php

namespace Modules\Restaurant\Models;

use App\Models\Tenant\ModelTenant;

class StatusOrden extends ModelTenant
{

    public $timestamps = false;
    protected $table = "status_orden";
    protected $fillable = [
        'description',
        'active',

    ];
}
