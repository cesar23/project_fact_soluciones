<?php

namespace App\Models\Tenant;

use Modules\Item\Models\Brand;
use Modules\Item\Models\Category;

class ChargeTypeItem extends ModelTenant
{

    protected $table = 'charges_type_items';
    protected $fillable = [
        'item_id',
        'charges_type_id',
        'brand_id',
        'category_id',

    ];



    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function charge_type()
    {
        return $this->belongsTo(ChargeType::class);
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