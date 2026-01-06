<?php

namespace App\Models\Tenant;


class IntegrateUserLevelsModule extends ModelTenant
{
    public $timestamps = true;
    protected $table = "integrate_user_levels_module";
    protected $fillable = [
        'integrate_user_type_id',
        'module_level_id',
    ];


}
