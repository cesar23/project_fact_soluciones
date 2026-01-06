<?php

namespace App\Models\Tenant;

use Hyn\Tenancy\Traits\UsesTenantConnection;

class DocumentRecurrence extends ModelTenant
{
    use UsesTenantConnection;


    protected $table = "document_recurrence";

    protected $fillable = [
        'id',
        'document_id',
        'interval',
        'send_email',
        'send_whatsapp',
        'active',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function items()
    {
        return $this->hasMany(DocumentRecurrenceItem::class, 'document_recurrence_id');
    }

    public function translateInterval(){
        switch ($this->interval) {
            case 'daily':
                return 'Diario';
                break;
            case 'weekly':
                return 'Semanal';
                break;
            case 'biweekly':
                return 'Quincenal';
                break;
            case 'monthly':
                return 'Mensual';
                break;
            case 'bimonthly':
                return 'Bimestral';
                break;
            case 'quarterly':
                return 'Trimestral';
                break;
            case 'semiannual':
                return 'Semestral';
                break;
            case 'annual':
                return 'Anual';
                break;
            default:
                return '';
                break;
        }
    }
}
