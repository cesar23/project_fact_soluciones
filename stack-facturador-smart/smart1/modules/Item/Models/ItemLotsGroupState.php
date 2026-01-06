<?php

namespace Modules\Item\Models;

use App\Models\Tenant\Item;
use App\Models\Tenant\ModelTenant;
use Modules\Inventory\Models\InventoryTransferItem;



class ItemLotsGroupState extends ModelTenant
{
    protected $table = 'item_lots_group_state';


    protected $fillable = [
        'description',
        'active',
    ];
}
