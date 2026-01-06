<?php

namespace App\Models\Tenant;


class NameQuotations extends ModelTenant
{

    protected $table = 'name_quotations';
    protected $fillable = [
        "quotations_optional",
        "quotations_optional_value",
    ];

    
}
