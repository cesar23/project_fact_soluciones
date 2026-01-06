<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplyContract extends ModelTenant
{
    protected $table = 'supply_contract';

    protected $fillable = [
        'supply_solicitude_id',
        'person_id',
        'supplie_plan_id',
        'supply_id',
        'path_solicitude',
        'supply_service_id',
        'address',
        'install_date',
        'start_date',
        'finish_date',
        'active',
        'observation',
    ];

    protected $casts = [
        'active' => 'boolean',
        'install_date' => 'date',
        'start_date' => 'date',
        'finish_date' => 'date',
    ];

    public function supplySolicitude(): BelongsTo
    {
        return $this->belongsTo(SupplySolicitude::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function suppliePlan(): BelongsTo
    {
        return $this->belongsTo(SupplyPlan::class);
    }

    public function supply(): BelongsTo
    {
        return $this->belongsTo(Supply::class);
    }

    public function supplyService(): BelongsTo
    {
        return $this->belongsTo(SupplyService::class);
    }
}