<?php

namespace Modules\Inventory\Models;

use App\Models\Tenant\Item;
use App\Models\Tenant\ModelTenant;
use Hyn\Tenancy\Traits\UsesTenantConnection;




class InventoryTransferToAccept extends ModelTenant
{
    use UsesTenantConnection;

    public $timestamps = false;
    protected $table = 'inventory_transfer_to_accept';
    protected $with = [
    
    ];

    protected $casts = [
    
    ];
    protected $fillable = [
        'id',
        'inventory_transfer_id',
        'item_id',
        'series_lots',
        'attributes',
    ];

    
    public function item(){
        return $this->belongsTo(Item::class);
    }
    public function inventory_transfer()
    {
        return $this->belongsTo(InventoryTransfer::class);
    }


    public function getSeriesLotsAttribute($value)
    {
        return (is_null($value)) ? null : json_decode($value, true);
    }



    

}
