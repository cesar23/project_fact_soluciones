<?php

namespace App\Models\Tenant;


class AverageHistory extends ModelTenant
{
    
    protected $table = 'average_history';
    protected $fillable = [
        'id',
        'id_document',
        'sale_note_id',
        'id_purchase',
        'purchase_cost',
        'total_purchase_cost',
        'price_balance',
        'sales_cost',
        'total_sales',
        'input',
        'output',
        'balance',
        'type_transaction',
        'total_balance',
        'serie',
        'number'
    ];

   
    
}