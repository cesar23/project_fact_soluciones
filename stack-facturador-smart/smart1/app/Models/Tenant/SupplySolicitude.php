<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplySolicitude extends ModelTenant
{
    protected $table = 'supply_solicitude';

    protected $fillable = [
        'person_id',
        'supply_id',
        'user_id',
        'supply_service_id',
        'program_date',
        'start_date',
        'finish_date',
        'use',
        'active',
        'review',
        'cod_tipo',
        'supply_debt_id',
        'observation',
        'consolidated',
        'consolidated_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'program_date' => 'date',
        'start_date' => 'date',
        'finish_date' => 'date',
        'review' => 'integer',
        'cod_tipo' => 'integer',
        'consolidated' => 'boolean',
        'consolidated_at' => 'datetime',
    ];

    public function supplyService(): BelongsTo
    {
        return $this->belongsTo(SupplyService::class);
    }

    public function supplyDebt(): BelongsTo
    {
        return $this->belongsTo(SupplyDebt::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function supply(): BelongsTo
    {
        return $this->belongsTo(Supply::class);
    }

    public function supplyContract(): HasMany
    {
        return $this->hasMany(SupplyContract::class);
    }

    public function supplySolicitudeItems(): HasMany
    {
        return $this->hasMany(SupplySolicitudeItem::class, 'supply_solicitude_id');
    }
}