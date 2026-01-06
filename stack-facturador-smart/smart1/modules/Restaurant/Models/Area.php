<?php

namespace Modules\Restaurant\Models;


use App\Models\Tenant\ModelTenant;
class Area extends ModelTenant
{

    public $timestamps = false;
    protected $table = "areas";
    protected $fillable = [
        'description',
        'printer',
        'copies',
        'active',
    ];
}
