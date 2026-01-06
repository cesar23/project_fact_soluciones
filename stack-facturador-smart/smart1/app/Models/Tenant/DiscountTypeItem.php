<?php

namespace App\Models\Tenant;

use Modules\Item\Models\Brand;
use Modules\Item\Models\Category;

class DiscountTypeItem extends ModelTenant
{

    protected $table = 'discounts_type_items';
    protected $fillable = [
        'item_id',
        'discounts_type_id',
        'brand_id',
        'category_id',

    ];



    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function discount_type()
    {
        return $this->belongsTo(DiscountType::class);
    }   

    public function brands()
    {
        return $this->belongsTo(Brand::class);
    }

    public function categories()
    {
        return $this->belongsTo(Category::class);
    }
    
    
    
}