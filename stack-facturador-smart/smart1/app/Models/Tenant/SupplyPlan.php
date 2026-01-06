<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Catalogs\AffectationIgvType;
use App\Models\Tenant\ModelTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplyPlan extends ModelTenant
{
    protected $table = 'supplie_plans';

    protected $fillable = [
        'description',
        'total',
        'type_zone',
        'type_plan',
        'price_c_m',
        'price_s_m',
        'price_alc',
        'observation',
        'active',
        'affectation_type_id'
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'price_c_m' => 'decimal:2',
        'price_s_m' => 'decimal:2',
        'price_alc' => 'decimal:2',
        'active' => 'boolean'
    ];

    public function suppliesPlansRegistered(): HasMany
    {
        return $this->hasMany(SupplyPlanRegistered::class, 'supplie_plan_id');
    }

    public function affectationType(): BelongsTo
    {
        return $this->belongsTo(AffectationIgvType::class);
    }
}