<?php

namespace App\Models\Tenant;

use Hyn\Tenancy\Traits\UsesTenantConnection;

class SunatDocumentState extends ModelTenant
{
    use UsesTenantConnection;

    protected $fillable = [
        'document_id',
        'state_type_id',
        'attempts',
        'state_date',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function stateType()
    {
        return $this->belongsTo(StateType::class);
    }
}
