<?php

namespace App\Models\Tenant\Catalogs;

use App\Models\Tenant\ModelTenant;

class DocumentRelatedToDocuments extends ModelTenant
{

    protected $table = "document_related_to_documents";
    public $timestamps = false;

    protected $fillable = [
        'document_id',
        'related_document',
        'document_type_id',
    ];
}