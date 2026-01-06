<?php

namespace Modules\Ecommerce\Models;

use App\Models\Tenant\ModelTenant;

class WoocommerceConfiguration extends ModelTenant
{
    
    protected $table = "configuration_ecommerce";

    protected $fillable = [
        'woocommerce_api_url',
        'woocommerce_api_key',
        'woocommerce_api_secret',
        'woocommerce_api_version',
        'woocommerce_api_last_sync',
        'last_id'
    ];
}
