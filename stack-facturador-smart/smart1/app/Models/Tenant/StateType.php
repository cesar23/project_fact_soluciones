<?php

namespace App\Models\Tenant;

use App\Traits\CacheTrait;

class StateType extends ModelTenant
{
    use CacheTrait;
    
    public $incrementing = false;
    public $timestamps = false;

    
    public static function getDataApiApp()
    {
        $states = self::get();

        return $states->push([
            'id' => 'all',
            'description' => 'Todos',
        ]);
    }

    public static function getStateTypes()
    {
        $cache_key = CacheTrait::getCacheKey('state_types');
        $records = CacheTrait::getCache($cache_key);
        if (!$records) {
            $records = self::all();
            CacheTrait::storeCache($cache_key, $records);
        }
        return $records;
    }

} 