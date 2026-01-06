<?php

namespace App\Models\Tenant\Catalogs;

use App\Models\Tenant\TechnicalServiceItem;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use App\Traits\CacheTrait;

class AffectationIgvType extends ModelCatalog
{
    use CacheTrait;
    use UsesTenantConnection;

    protected $table = "cat_affectation_igv_types";
    public $incrementing = false;
    protected $casts = [
        'exportation' => 'integer',
        'free' => 'integer',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public  function technical_service_item()
    {
        return $this->hasMany(TechnicalServiceItem::class, 'affectation_igv_type_id');
    }

    public static function getAffectationIgvTypesOrderByName()
    {
        $cache_key = CacheTrait::getCacheKey('affectation_igv_types_order_by_name');
        $affectation_igv_types = CacheTrait::getCache($cache_key);
        if(!$affectation_igv_types){
            $affectation_igv_types = self::whereActive()->get();
            CacheTrait::storeCache($cache_key, $affectation_igv_types);
        }
        return $affectation_igv_types;
    }
    
}
