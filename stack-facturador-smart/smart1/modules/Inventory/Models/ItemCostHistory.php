<?php

namespace Modules\Inventory\Models;

use App\Models\Tenant\Item;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class ItemCostHistory extends Model
{
    use UsesTenantConnection;

    protected $table = 'item_cost_histories';
    protected $fillable = [
        'item_id',
        'warehouse_id',
        'date',
        'average_cost',
        'stock',
        'inventory_kardex_id',
        'inventory_kardexable_id',
        'inventory_kardexable_type',
    ];

    protected $casts = [
        'average_cost' => 'float',
        'stock' => 'float',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function inventoryKardexable()
    {
        return $this->morphTo();
    }
}
