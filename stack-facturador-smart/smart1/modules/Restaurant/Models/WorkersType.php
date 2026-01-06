<?php

namespace Modules\Restaurant\Models;

use App\Models\Tenant\ModelTenant;
class WorkersType extends ModelTenant
{

    public $timestamps = false;
    protected $hidden = ["created_at", "updated_at"];
    protected $table = "workers_type";
    protected $fillable = [
        'description',
        'active',
    ];
}
