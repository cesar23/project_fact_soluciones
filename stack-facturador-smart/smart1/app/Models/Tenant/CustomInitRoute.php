<?php

/**

 */

namespace App\Models\Tenant;



class CustomInitRoute extends ModelTenant
{

    protected $table = 'custom_init_routes';

    protected $fillable = [
        'name',
        'route'
    ];

}
