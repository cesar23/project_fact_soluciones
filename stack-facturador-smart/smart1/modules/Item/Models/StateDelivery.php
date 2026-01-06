<?php

namespace Modules\Item\Models;

use App\Models\Tenant\ModelTenant;

class StateDelivery extends ModelTenant
{
    protected $table = 'state_deliveries';
    protected $fillable = [ 
        'name',
    ];
 
    
 

}