<?php

namespace App\Models\Tenant;



class PurchaseResponsible extends ModelTenant
{
    protected $table = 'purchase_responsibles';

    protected $fillable = [
        'identity_document_type_id',
        'number',
        'name',
        'country_id',
        'department_id',
        'province_id',
        'district_id',
        'address',
        'email',
        'telephone',
        'active',
        'created_at',
        'updated_at',
        
    ];

 
}