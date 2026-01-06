<?php

namespace App\Models\Tenant;

use Hyn\Tenancy\Abstracts\TenantModel;
use Illuminate\Auth\Authenticatable;

class PersonRegModel extends ModelTenant
{
    use Authenticatable;

    protected $table = 'person_reg';

    protected $fillable = [
        'short', 'description', 'active',
    ];

    public function persons()
    {
        return $this->hasMany(Person::class, 'person_reg_id');
    }
}


