<?php


namespace Modules\Item\Models;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use App\Models\Tenant\ModelTenant;
use Modules\Inventory\Models\Inventory;

class ItemPropertyInventory extends ModelTenant
{
    use UsesTenantConnection;
    protected $table = "item_properties_inventory";
    protected $fillable = [
        'item_property_id',
        'inventory_id',
        'state',
    ];
    public function item_property()
    {
        return $this->belongsTo(ItemProperty::class);
    }
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}
