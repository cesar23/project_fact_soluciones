<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Catalogs\OperationType;

class InitStock extends ModelTenant
{
    public $timestamps = true;
    protected $table = "init_stock";
    protected $fillable = [
        'item_id',
        'warehouse_id',
        'init_date',
        'stock',
    ];

    protected $casts = [
        'init_date' => 'date',
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
