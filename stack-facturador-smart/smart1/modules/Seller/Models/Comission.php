<?php

namespace Modules\Seller\Models;

use App\Models\Tenant\ModelTenant;


class Comission extends ModelTenant
{
    
    protected $fillable = [
        'percentage',
        'margin',
        'active'
    ];
  

    protected $casts = [
        'active' => 'boolean',
    ];


    
}