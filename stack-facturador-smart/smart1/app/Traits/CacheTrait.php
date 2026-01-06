<?php

namespace App\Traits;

use App\Models\Tenant\User;
use Hyn\Tenancy\Environment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use \Hyn\Tenancy\Facades\TenancyFacade;
use Hyn\Tenancy\Models\Website;
use Illuminate\Support\Facades\DB;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

trait CacheTrait
{

    public static function clearCache($name_cache)
    {
        $cache_key = self::getCacheKey($name_cache);
        Cache::forget($cache_key);
    }

    public static function getUuidTenant()
    {
        return TenancyFacade::website()->uuid;
    }

    public static function getCacheKey($name_cache)
    {
        $tenant_id = TenancyFacade::website()->uuid;
        return "tenant_{$tenant_id}_{$name_cache}";
    }

    public static function storeCache($cache_key, $data, $time = 60 * 60 * 2)
    {
        Cache::put($cache_key, $data, $time);
    }

    public static function getCache($cache_key)
    {
        return Cache::get($cache_key);
    }

    public static function flushCacheTenant($tenant_id = null)
    {
        try {
            if ($tenant_id !== null) {
                $client = Website::where('uuid', $tenant_id)->first();
                $tenancy = app(Environment::class);
                $tenancy->tenant($client);
            } else {
                $tenant_id = TenancyFacade::website()->uuid;
            }
            $user_ids = DB::connection('tenant')->table('users')->pluck('id')->toArray();
            $keys = [
                "locations",
                "customers_documents", "unit_types", "vc_company", "cash_payment_methods", "credit_payment_methods", "configuration", "public_config", "vc_establishment", "vc_establishment_configuration", "vc_establishment_public_config", "vc_establishment", "vc_establishments", "state_types", "tutorials_shortcuts_right", "tutorials_shortcuts_left", "tutorials_videos",
                "warehouse_description",
                "affectation_igv_types_order_by_name",
                "attribute_types_order_by_name",
                "global_discounts",
                "global_charges",
                "operation_types_order_by_name",
                "price_types_order_by_name",
                "system_isc_types_order_by_name",
                "reasons",
                "brands_order_by_name",
                "categories_order_by_name",
                "limit_item_by_date_order_by_name",
            ];

            $key_series = "series_by_user_id_";
            foreach ($user_ids as $user_id) {
                $keys[] = $key_series . $user_id;
            }
            foreach ($keys as $name_cache) {
                $cache_key = "tenant_{$tenant_id}_{$name_cache}";
                Cache::forget($cache_key);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
