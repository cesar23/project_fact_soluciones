<?php

namespace Modules\Restaurant\Models;

use App\Models\Tenant\Item;
use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\CategoryItem;
use Illuminate\Database\Eloquent\Model;

class Food extends ModelTenant
{

    public $timestamps = false;
    protected $with = ['category', 'item','area'];
    protected $table = "foods";
    protected $fillable = [
        'category_food_id',
        'description',
        'price',
        'code',
        'image',
        'area_id',
        'item_id'
    ];


    public function category()
    {
        return $this->belongsTo(CategoryItem::class, 'category_food_id');
    }
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
    public function area()
    {
        return $this->belongsTo(Area::class);
    }
}
