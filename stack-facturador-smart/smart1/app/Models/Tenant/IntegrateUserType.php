<?php

namespace App\Models\Tenant;


class IntegrateUserType extends ModelTenant
{
    public $timestamps = true;
    protected $table = "integrate_user_type";
    protected $fillable = [
        'name',
    ];


}
