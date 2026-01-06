<?php

namespace App\Models\Tenant;

use Hyn\Tenancy\Abstracts\TenantModel;
use Illuminate\Database\Eloquent\Model;

class Channel extends ModelTenant
{

    protected $table = 'channels_reg';

    protected $fillable = [
        'id','channel_name',
    ];

}


