<?php

namespace App\Models\Tenant\Catalogs;

use App\Traits\CacheTrait;
use Hyn\Tenancy\Traits\UsesTenantConnection;

class OperationType extends ModelCatalog
{
    use UsesTenantConnection;
    use CacheTrait;

    protected $table = "cat_operation_types";
    public $incrementing = false;

    protected $casts = [
        'exportation' => 'integer',
        'free' => 'integer',
    ];

    public static function getOperationTypesOrderByName()
    {
        $cache_key = CacheTrait::getCacheKey('operation_types_order_by_name');
        $operation_types = CacheTrait::getCache($cache_key);
        if(!$operation_types){
            $operation_types = self::whereActive()->select('id', 'description','exportation')->get();
            CacheTrait::storeCache($cache_key, $operation_types);
        }
        return $operation_types;
    }
}