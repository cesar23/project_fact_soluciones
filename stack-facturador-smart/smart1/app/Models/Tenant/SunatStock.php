<?php

namespace App\Models\Tenant;


class SunatStock extends ModelTenant
{   
    protected $table = 'sunat_stock';
    protected $fillable = [
        'start_stock',
        'end_stock',
        'period',
        'item_id',
        'warehouse_id'
    ];


}