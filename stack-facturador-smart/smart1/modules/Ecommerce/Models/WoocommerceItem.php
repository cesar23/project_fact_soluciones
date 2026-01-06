<?php

namespace Modules\Ecommerce\Models;

use App\Models\Tenant\ModelTenant;

class WoocommerceItem extends ModelTenant
{
    public $timestamps = false;
    protected $table = "woocommerce_item";
    protected $fillable = [
        'id',
        'item_id',
        'woocommerce_item_id'
    ];
}
