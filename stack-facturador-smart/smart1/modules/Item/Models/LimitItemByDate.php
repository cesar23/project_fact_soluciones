<?php

namespace Modules\Item\Models;

use App\Models\Tenant\Item;
use App\Models\Tenant\Person;
use App\Models\Tenant\ModelTenant;
use App\Traits\CacheTrait;

class LimitItemByDate extends ModelTenant
{
    use CacheTrait;

    protected $fillable = [
        'item_id',
        'customer_id',
        'date',
        'limit',
        'reached'
    ];

    protected $table = 'limit_item_by_date';

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function customer()
    {
        return $this->belongsTo(Person::class);
    }

    public function scopeFilterForTables($query)
    {
        return $query->select('id', 'item_id', 'customer_id', 'date', 'limit', 'reached');
    }

    public function getRowResourceApi()
    {
        return [
            'id' => $this->id,
            'item_id' => $this->item_id,
            'customer_id' => $this->customer_id,
            'date' => $this->date,
            'limit' => $this->limit,
            'reached' => $this->reached,
        ];
    }


    /**
     * 
     * Data para filtros - select
     *
     * @return array
     */
    public static function getDataForFilters()
    {
        return self::select(['id', 'item_id', 'customer_id', 'date', 'limit', 'reached'])->orderBy('date')->get();
    }

    public static function getLimitItemByDateOrderByName()
    {
        $cache_key = CacheTrait::getCacheKey('limit_item_by_date_order_by_name');

        $limit_item_by_date = CacheTrait::getCache($cache_key);

        if (!$limit_item_by_date) {
            $limit_item_by_date = self::select(['id', 'item_id', 'customer_id', 'date', 'limit', 'reached'])->orderBy('date')->get();
            CacheTrait::storeCache($cache_key, $limit_item_by_date);
        }

        return $limit_item_by_date;
    }
}
