<?php

namespace App\Models\Tenant;

use Modules\Item\Models\ItemLot;

/**
 * Class Customer
 *
 * @package App\Models\Tenant
 * @mixin ModelTenant
 */
class WeaponTracking extends ModelTenant
{
    protected $table = 'weapon_tracking';
    protected $fillable = [
        'item_id',
        'person_id',
        'item_lot_id',
        'date_of_issue',
        'time_of_issue',
        'type',
        'destiny',
        'observation',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
    public function person()
    {
        return $this->belongsTo(Person::class);
    }
    public function item_lot()
    {
        return $this->belongsTo(ItemLot::class);
    }

    
}
