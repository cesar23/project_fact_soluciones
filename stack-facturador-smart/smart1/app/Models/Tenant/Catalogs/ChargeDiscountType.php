<?php

namespace App\Models\Tenant\Catalogs;

use App\Traits\CacheTrait;
use Hyn\Tenancy\Traits\UsesTenantConnection;

class ChargeDiscountType extends ModelCatalog
{
    use UsesTenantConnection;
    use CacheTrait;

    protected $table = "cat_charge_discount_types";
    public $incrementing = false;

    public function scopeWhereType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeWhereLevel($query, $level)
    {
        return $query->where('level', $level);
    }

        
    /**
     * 
     * Obtener descuentos globales que afectan y no afectan la base
     *
     * @return array
     */
    public static function getGlobalDiscounts()
    {
        return self::whereIn('id', ['02', '03'])->whereActive()->get();
    }

    public static function getGlobalDiscountsCache(){
        $cache_key = CacheTrait::getCacheKey('global_discounts');
        $global_discounts = CacheTrait::getCache($cache_key);
        if(!$global_discounts){
            $global_discounts = self::whereType('discount')->whereLevel('item')->get();
            CacheTrait::storeCache($cache_key, $global_discounts);
        }
        return $global_discounts;
    }

    public static function getGlobalChargesCache(){
        $cache_key = CacheTrait::getCacheKey('global_charges');
        $global_charges = CacheTrait::getCache($cache_key);
        if(!$global_charges){
            $global_charges = self::whereType('charge')->whereLevel('item')->get();
            CacheTrait::storeCache($cache_key, $global_charges);
        }
        return $global_charges;
    }
    
}