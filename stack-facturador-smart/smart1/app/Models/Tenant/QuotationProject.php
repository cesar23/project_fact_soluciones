<?php

namespace App\Models\Tenant;


use App\Traits\SellerIdTrait;


class QuotationProject extends ModelTenant
{
    use SellerIdTrait;


    protected $fillable = [
        "id",
        "quotation_id",
        "project_name",
        "atention",
        "direction",
        "email",
        "telephone",
        "limit_date",
        "percentage",
        "observations",
    ];

  
    protected $casts = [
        'date_of_issue' => 'date',

    ];



}
