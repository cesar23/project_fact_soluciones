<?php

namespace App\Models\Tenant;

use Modules\Finance\Models\GlobalPayment;
use Modules\Finance\Models\PaymentFile;

/**
 * Class DocumentFee
 *
 * @package App\Models\Tenant
 */
class InventoryReference extends ModelTenant
{
    public $timestamps = false;
    protected $table = 'inventory_references';

    protected $fillable = [
        'code',
        'description',
    ];



    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'inventory_reference_id');
    }

    public function dispatches()
    {
        return $this->hasMany(Dispatch::class, 'inventory_reference_id');
    }
}
