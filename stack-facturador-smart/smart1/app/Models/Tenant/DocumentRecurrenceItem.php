<?php

namespace App\Models\Tenant;

use Hyn\Tenancy\Traits\UsesTenantConnection;

class DocumentRecurrenceItem extends ModelTenant
{
    use UsesTenantConnection;


    protected $table = "document_recurrence_items";

    protected $fillable = [
        'id',
        'emission_date',
        'emission_time',
        'emitted',
        'email_sent',
        'whatsapp_sent',
        'document_recurrence_id',
        'active',
    ];

    public function document_recurrence()
    {
        return $this->belongsTo(DocumentRecurrence::class, 'document_recurrence_id');
    }
}
