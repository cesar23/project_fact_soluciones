<?php

namespace App\Models\Tenant;

use Modules\Item\Models\Brand;
use Modules\Item\Models\Category;

class ChargeType extends ModelTenant
{
    protected $table = 'charges_types';
    protected $fillable = [
        'description',
        'charge_value',
        'image',
        'type',
        'is_percentage',
        'apply_to_all_items',
        'active',
    ];

    protected $casts = [
        'is_percentage' => 'boolean',
        'active' => 'boolean',
        'apply_to_all_items' => 'boolean',
    ];

    public function charge_type_items()
    {
        return $this->hasMany(ChargeTypeItem::class, 'charges_type_id');
    }

}