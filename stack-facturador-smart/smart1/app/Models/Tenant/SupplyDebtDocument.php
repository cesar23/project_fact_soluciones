<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplyDebtDocument extends ModelTenant
{
    protected $table = 'supply_debt_documents';

    protected $fillable = [
        'debt_id',
        'supply_plan_document_id',
        'amount_paid',
        'debt_amount_before',
        'debt_amount_after',
        'payment_type',
        'is_cancelled',
        'cancelled_at',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'debt_amount_before' => 'decimal:2', 
        'debt_amount_after' => 'decimal:2',
        'is_cancelled' => 'boolean',
        'cancelled_at' => 'datetime',
    ];

    public function debt(): BelongsTo
    {
        return $this->belongsTo(SupplyDebt::class);
    }

    public function supplyPlanDocument(): BelongsTo
    {
        return $this->belongsTo(SupplyPlanDocument::class);
    }
}