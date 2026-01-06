<?php

namespace App\Models\Tenant;

class SunatPurchaseSale extends ModelTenant
{   
    protected $table = 'sunat_purchase_sale';
    protected $cast = [
        'show' => 'boolean'
    ];
    protected $fillable = [
        'sunat_sale',
        'internal_sale',
        'purchase_expense',
        'period',
        'show'
    ];

  
}