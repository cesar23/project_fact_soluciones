<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplyDebt extends ModelTenant
{
    protected $table = 'supply_debt';

    protected $fillable = [
        'supply_contract_id',
        'person_id',
        'supply_id',
        'serie_receipt',
        'correlative_receipt',
        'amount',
        'original_amount',
        'paid_amount',
        'last_payment_date',
        'payment_count',
        'cancelled_amount',
        'year',
        'month',
        'generation_date',
        'due_date',
        'active',
        'type',
        'supply_type_debt_id',
        'supply_concept_id',
    ];

    protected $casts = [
        'active' => 'boolean',
        'generation_date' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'original_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'cancelled_amount' => 'decimal:2',
        'last_payment_date' => 'datetime',
    ];

    public function supplyContract(): BelongsTo
    {
        return $this->belongsTo(SupplyContract::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function supply(): BelongsTo
    {
        return $this->belongsTo(Supply::class);
    }
    public function supplyDebtDocuments(): HasMany
    {
        return $this->hasMany(SupplyDebtDocument::class);
    }

    public function supplyTypeDebt(): BelongsTo
    {
        return $this->belongsTo(SupplyTypeDebt::class);
    }

    public function supplyConcept(): BelongsTo
    {
        return $this->belongsTo(SupplyConcept::class);
    }

    // Campos calculados
    public function getRemainingAmountAttribute()
    {
        return ($this->original_amount ?? $this->amount) - ($this->paid_amount ?? 0) + ($this->cancelled_amount ?? 0);
    }

    public function getIsPaidAttribute()
    {
        return $this->remaining_amount <= 0.01;
    }

    public function getHasPaymentsAttribute()
    {
        return ($this->payment_count ?? 0) > 0;
    }


}