<?php

namespace Modules\Inventory\Models;


use App\Models\Tenant\Item;
use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\Warehouse;

class PendingItemCostReset extends ModelTenant
{

    protected $table = 'pending_item_cost_resets';
    protected $fillable = [
        'item_id',
        'warehouse_id',
        'pending_from_date',
    ];

    protected $casts = [
        'pending_from_date' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
