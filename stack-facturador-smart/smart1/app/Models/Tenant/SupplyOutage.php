<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplyOutage extends ModelTenant
{
    protected $table = 'supply_outage';

    protected $fillable = [
        'supply_contract_id',
        'observation',
        'state',
        'person_id',
        'type',
        'date_of_outage',
    ];

    public function supplyContract(): BelongsTo
    {
        return $this->belongsTo(SupplyContract::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    
}