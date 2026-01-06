<?php


namespace Modules\Suscription\Models\Tenant;

use App\Models\Tenant\Document;
use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\Person;
use App\Models\Tenant\SaleNote;
use Hyn\Tenancy\Traits\UsesTenantConnection;

class SuscriptionPayment extends ModelTenant
{

    use UsesTenantConnection;
    protected $with = ['document', 'sale_note'];
    protected $table = 'suscription_payment_periods';
    // public $timestamps = false;

    protected $fillable = [
        'document_id',
        'sale_note_id',
        'client_id',
        'child_id',
        'period',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
    public function sale_note()
    {
        return $this->belongsTo(SaleNote::class);
    }

    public function client()
    {
        return $this->belongsTo(Person::class, 'client_id');
    }

    public function child()
    {
        return $this->belongsTo(Person::class, 'child_id');
    }

    public function getRowResource()
    {
        $document = $this->document ?? $this->sale_note;
        $total_paid = $document->payments->sum('payment');
        $total_pending_paid = $document->total - $total_paid;
        $date_of_issue = is_string($document->date_of_issue) ? $document->date_of_issue : $document->date_of_issue->format('Y-m-d');
        return [
            'customer_name' => $this->client->name,
            'children_name' => $this->child->name,
            'grade' => $document->grade,
            'date_of_issue' => $date_of_issue,
            'total' => $document->total,
            'section' => $document->section,
            'number_full' => $document->number_full,
            'state_type_description' => $document->state_type_description,
            'currency_type_id' => $document->currency_type_id,
            'total_paid' => $total_paid,
            'total_pending_paid' => $total_pending_paid,
            'total_canceled' => $total_pending_paid == 0 ? true : false,
        ];
    }
}
