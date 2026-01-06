<?php

namespace App\Models\Tenant;

use App\Traits\CacheTrait;

class ConditionBlockPaymentMethod extends ModelTenant
{
    use CacheTrait;
    protected $table = "condition_block_payment_methods";
    protected $fillable = [
        'payment_condition_id',
        'payment_method_type'
    ];


    public static function getCashPaymentMethods($nc_payment_nv = false)
    {
        $cache_key = CacheTrait::getCacheKey('cash_payment_methods');
        $cash_payment_methods = CacheTrait::getCache($cache_key);
        $configuration = Configuration::getConfig();
        if (!$cash_payment_methods) {

            $types = self::where('payment_condition_id', '01')->pluck('payment_method_type')->toArray();
            $cash_payment_methods = PaymentMethodType::query();
            if(!$configuration->nc_payment_nv){
                $cash_payment_methods = $cash_payment_methods->where('id', '<>', 'NC');
            }
            $cash_payment_methods = $cash_payment_methods->whereRaw(implode(' OR ', array_map(function ($type) {
                return "$type = true";
            }, $types)));
        
            $cash_payment_methods = $cash_payment_methods->get();
            CacheTrait::storeCache($cache_key, $cash_payment_methods);
        }
        return $cash_payment_methods;
    }

    public static function getCreditPaymentMethods()
    {
        $cache_key = CacheTrait::getCacheKey('credit_payment_methods');
        $credit_payment_methods = CacheTrait::getCache($cache_key);
        if (!$credit_payment_methods) {
            $types = self::where('payment_condition_id', '02')->pluck('payment_method_type')->toArray();
            $credit_payment_methods = PaymentMethodType::whereRaw(implode(' OR ', array_map(function ($type) {
                return "$type = true";
            }, $types)))->get();
            CacheTrait::storeCache($cache_key, $credit_payment_methods);
        }
        return $credit_payment_methods;
    }
}
