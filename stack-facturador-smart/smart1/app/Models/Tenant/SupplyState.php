<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplyState extends ModelTenant
{
    protected $table = 'supplies_states';

    protected $fillable = [
        'description'
    ];

    public function supplies(): HasMany
    {
        return $this->hasMany(Supply::class, 'state_supply_id');
    }
}