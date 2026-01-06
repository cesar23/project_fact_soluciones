<?php

namespace App\Models\Tenant;

use Modules\Expense\Models\Expense;

class WarrantyDocument extends ModelTenant
{
    protected $fillable = [
        'date_cancel',
        'sale_note_id',
        'document_id',
        'expense_id',
        'quantity',
        'total',
        'comment',
        'state',
    ];

    protected $casts = [
        'state' => 'boolean',
        'date_cancel' => 'date',
    ];

    protected $table = 'warranty_documents';

    public $timestamps = false;

    public function sale_note()
    {
        return $this->belongsTo(SaleNote::class);
    }


    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }
}
