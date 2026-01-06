<?php

namespace App\Models\Tenant;




class QuotationServicesNotServices extends ModelTenant
{


    protected $fillable = [
        "id",
        "quotation_id",
        "document_service_id",
        "document_not_service_id",
    
    ];

  


}
