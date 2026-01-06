<?php

namespace App\Models\Tenant\Catalogs;

use App\Models\Tenant\TechnicalServiceItem;
use App\Traits\CacheTrait;
use Hyn\Tenancy\Traits\UsesTenantConnection;

class SystemIscType extends ModelCatalog
{
    use UsesTenantConnection;
    use CacheTrait;

    protected $table = "cat_system_isc_types";
    public $incrementing = false;


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public  function technical_service_item()
    {
        return $this->hasMany(TechnicalServiceItem::class, 'system_isc_type_id');
    }

    public static function getSystemIscTypesOrderByName()
    {
        $cache_key = CacheTrait::getCacheKey('system_isc_types_order_by_name');
        $system_isc_types = CacheTrait::getCache($cache_key);
        if(!$system_isc_types){
            $system_isc_types = self::whereActive()->get();
            CacheTrait::storeCache($cache_key, $system_isc_types);
        }
        return $system_isc_types;
    }
    
}
