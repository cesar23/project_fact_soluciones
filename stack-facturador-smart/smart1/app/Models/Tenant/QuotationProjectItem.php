<?php

namespace App\Models\Tenant;

use App\Traits\AttributePerItems;

class QuotationProjectItem extends ModelTenant
{
    use AttributePerItems;
    public $timestamps = false;
    protected $fillable = [
        "id",
        "quotation_item_id",
        "disponibility",
        "header"
    ];
}
