<?php

namespace Modules\Inventory\Models;

use App\Models\Tenant\Item;
use App\Models\Tenant\ModelTenant;

class CostAverage extends ModelTenant
{

    public $timestamps = true;
    protected $table = 'cost_average';

    protected $fillable = [
        'average',
        "item_id",
        "purchase_id",
        "purchase_date",
        "purchase_cost",
        "total_purchase_cost",
        "purchase_quantity",
        "stock_without_purchase",
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
