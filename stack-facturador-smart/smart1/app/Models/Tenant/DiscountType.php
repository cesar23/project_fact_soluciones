<?php

namespace App\Models\Tenant;

use Modules\Item\Models\Brand;
use Modules\Item\Models\Category;

class DiscountType extends ModelTenant
{
    protected $table = 'discounts_types';
    protected $fillable = [
        'description',
        'discount_value',
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

    public function discount_type_items()
    {
        return $this->hasMany(DiscountTypeItem::class, 'discounts_type_id');
    }

}