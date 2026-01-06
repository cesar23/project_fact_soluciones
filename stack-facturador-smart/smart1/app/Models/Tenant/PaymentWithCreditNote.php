<?php

namespace App\Models\Tenant;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Modules\Expense\Models\ExpensePayment;

class PaymentWithCreditNote extends ModelTenant
{
    use UsesTenantConnection;

    protected $table = 'payments_with_credit_note';
    protected $fillable = [
        'sale_note_payment_id',
        'document_payment_id',
        'expense_payment_id',
        'note_id',
        'amount',
    ];

    
    public function sale_note_payment()
    {
        return $this->belongsTo(SaleNotePayment::class);
    }

    public function document_payment()
    {
        return $this->belongsTo(DocumentPayment::class);
    }
    
    public function expense_payment()
    {
        return $this->belongsTo(ExpensePayment::class);
    }
    
    public function note()
    {
        return $this->belongsTo(Note::class);
    }
    
    
    
}
