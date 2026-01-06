<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplyVia extends ModelTenant
{
    protected $table = 'supply_via';
    protected $fillable = [
        'name',
        'code',
        'obsevation',
        'supply_type_via_id',
        'sector_id'
    ];

    public $timestamps = false;

    public function supplyTypeVia(): BelongsTo
    {
        return $this->belongsTo(SupplyTypeVia::class);
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    public function supply(): HasMany
    {
        return $this->hasMany(Supply::class);
    }
}
