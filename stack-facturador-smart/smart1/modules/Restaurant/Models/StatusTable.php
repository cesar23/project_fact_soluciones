<?php

namespace Modules\Restaurant\Models;
use App\Models\Tenant\ModelTenant;

class StatusTable extends ModelTenant
{

    public $timestamps = false;
    protected $table = "status_table";
    protected $fillable = [
        'description',
        'active',

    ];
}
