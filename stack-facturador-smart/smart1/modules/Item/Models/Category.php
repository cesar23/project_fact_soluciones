<?php

namespace Modules\Item\Models;

use App\Models\Tenant\Item;
use App\Models\Tenant\ModelTenant;
use App\Traits\CacheTrait;

class Category extends ModelTenant
{
    use CacheTrait;

    protected $fillable = [
        'name',
        'image'
    ];

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function scopeFilterForTables($query)
    {
        return $query->select('id', 'name')->orderBy('name');
    }

    public function getRowResourceApi()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'selected' => false,
        ];
    }


    /**
     * 
     * Data para filtros - select
     *
     * @return array
     */
    public static function getDataForFilters()
    {
        return self::select(['id', 'name'])->orderBy('name')->get();
    }

    public static function getCategoriesOrderByName()
    {
        $cache_key = CacheTrait::getCacheKey('categories_order_by_name');

        $categories = CacheTrait::getCache($cache_key);

        if (!$categories) {
            $categories = self::select(['id', 'name'])->orderBy('name')->get();
            CacheTrait::storeCache($cache_key, $categories);
        }

        return $categories;
    }
}
