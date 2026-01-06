<?php

namespace App\Models\Tenant\Catalogs;

use App\Traits\CacheTrait;
use Illuminate\Database\Eloquent\Builder;
use Hyn\Tenancy\Traits\UsesTenantConnection;

class AttributeType extends ModelCatalog
{
    use CacheTrait;
    use UsesTenantConnection;

    protected $table = "cat_attribute_types";
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'active',
        'description',
    ];

    public static function getAttributeTypesOrderByName()
    {
        $cache_key = CacheTrait::getCacheKey('attribute_types_order_by_name');
        $attribute_types = CacheTrait::getCache($cache_key);
        if(!$attribute_types){
            $attribute_types = self::whereActive()->orderBy('description')->get();
            CacheTrait::storeCache($cache_key, $attribute_types);
        }
        return $attribute_types;
    }

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::addGlobalScope('active', function (Builder $builder) {
    //         $builder->where('active', 1);
    //     });
    // }
}