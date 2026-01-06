<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Catalogs\Country;
use App\Models\Tenant\Catalogs\Department;
use App\Models\Tenant\Catalogs\District;
use App\Models\Tenant\Catalogs\Province;
use App\Traits\CacheTrait;

class Warehouse extends ModelTenant
{
    use CacheTrait;
    protected $fillable = [
        'establishment_id',
        'description',
        'active',
    ];


    /**
     * Se usa en la relacion con el inventario kardex en modules/Inventory/Traits/InventoryTrait.php.
     * Tambien se debe tener en cuenta modules/Inventory/Providers/InventoryKardexServiceProvider.php y
     * app/Providers/KardexServiceProvider.php para la correcta gestion de kardex
     *
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inventory_kardex()
    {
        return $this->hasMany(InventoryKardex::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public  function technical_service_item()
    {
        return $this->hasMany(TechnicalServiceItem::class, 'warehouse_id');
    }

    public static function getDescriptionWarehouse($id)
    {
        $cache_key = CacheTrait::getCacheKey('warehouse_description');
        $warehouse_description = CacheTrait::getCache($cache_key);
        if (!$warehouse_description) {
            $warehouse_description = Warehouse::find($id)->description;
            CacheTrait::storeCache($cache_key, $warehouse_description);
        }
        return $warehouse_description;
    }
    
}
