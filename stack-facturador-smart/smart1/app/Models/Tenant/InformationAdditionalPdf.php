<?php

namespace App\Models\Tenant;

use Hyn\Tenancy\Abstracts\TenantModel;

class InformationAdditionalPdf extends TenantModel
{
    protected $table = 'information_additional_pdf';
    public $timestamps = true;
    protected $fillable = [
        'description',
        'image',
        'is_active',
    ];

}
