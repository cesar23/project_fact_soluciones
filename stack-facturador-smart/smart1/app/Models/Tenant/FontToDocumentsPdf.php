<?php

namespace App\Models\Tenant;

use Hyn\Tenancy\Traits\UsesTenantConnection;

class FontToDocumentsPdf extends ModelTenant
{
    use UsesTenantConnection;


    protected $table = "font_to_documents_pdf";

    protected $fillable = [
        'id',
        'document_type',
        'format',
        'font_size',
    ];




}
