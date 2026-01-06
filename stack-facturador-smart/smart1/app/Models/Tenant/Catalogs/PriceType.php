<?php

namespace App\Models\Tenant\Catalogs;

use App\Models\Tenant\TechnicalServiceItem;
use App\Traits\CacheTrait;
use Hyn\Tenancy\Traits\UsesTenantConnection;

class PriceType extends ModelCatalog
{
    use UsesTenantConnection;
    use CacheTrait;

    protected $table = "cat_price_types";
    public $incrementing = false;


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public  function technical_service_item()
    {
        return $this->hasMany(TechnicalServiceItem::class, 'price_type_id');
    }

    public static function getPriceTypesOrderByName()
    {
        $cache_key = CacheTrait::getCacheKey('price_types_order_by_name');
        $price_types = CacheTrait::getCache($cache_key);
        if(!$price_types){
            $price_types = self::whereActive()->get();
            CacheTrait::storeCache($cache_key, $price_types);
        }
        return $price_types;
    }
    
}
