<?php

namespace App\Models\Tenant;

/**
 * Class Customer
 *
 * @package App\Models\Tenant
 * @mixin ModelTenant
 */
class MallConfig extends ModelTenant
{
    public $timestamps = false;
    protected $table = 'mall_config';
    protected $fillable = [
        'store_id',
        'store_name',
        'mall_id',
        'store_number',
    ];

    
}
