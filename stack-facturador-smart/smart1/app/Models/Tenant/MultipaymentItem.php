<?php

namespace App\Models\Tenant;


class MultipaymentItem extends ModelTenant
{
    protected $table = 'multipayment_items';
    public $timestamps = false;
    protected $fillable = [
        'multipayment_id',
        'sale_note_payment_id',
        'document_payment_id',
        'total',
        'remaining',
    
    ];

    protected $casts = [
        // 'date' => 'date',
    ];

    public function multipayment()
    {
        return $this->belongsTo(Multipayment::class);
    }

    public function sale_note_payment()
    {
        return $this->belongsTo(SaleNotePayment::class);
    }

    public function document_payment()
    {
        return $this->belongsTo(DocumentPayment::class);
    }

}
