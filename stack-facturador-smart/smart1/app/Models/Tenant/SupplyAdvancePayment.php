<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\Document;
use App\Models\Tenant\SaleNote;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplyAdvancePayment extends ModelTenant
{
    protected $table = 'supply_advance_payments';

    protected $fillable = [
        'supply_id',
        'amount',
        'payment_date',
        'year',
        'month',
        'active',
        'document_type_id',
        'periods',
        'total_amount',
        'document_id',
        'sale_note_id',
        'is_used',
        'used_in_document_id',
        'used_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'year' => 'integer',
        'active' => 'boolean',
        'periods' => 'array',
        'total_amount' => 'decimal:2',
        'is_used' => 'boolean',
        'used_at' => 'datetime',
    ];

    public function supply(): BelongsTo
    {
        return $this->belongsTo(Supply::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function saleNote(): BelongsTo
    {
        return $this->belongsTo(SaleNote::class);
    }
}