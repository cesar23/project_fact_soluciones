<?php

namespace App\Models\Tenant;

use Modules\Item\Models\Brand;
use Modules\Item\Models\Category;

class PriceAdjustmentItem extends ModelTenant
{
    protected $table = 'price_adjustment_items';

    protected $fillable = [
        'price_adjustment_id',
        'item_id',
        'brand_id',
        'category_id',
        'old_price',
        'new_price'
    ];

    protected $casts = [
        'old_price' => 'decimal:2',
        'new_price' => 'decimal:2',
    ];

    public function price_adjustment()
    {
        return $this->belongsTo(PriceAdjustment::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}