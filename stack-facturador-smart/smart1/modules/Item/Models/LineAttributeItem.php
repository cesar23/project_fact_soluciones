<?php

namespace Modules\Item\Models;

use App\Models\Tenant\Item;
use App\Models\Tenant\ModelTenant;
use App\Traits\CacheTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class LineAttributeItem
 *
 * @property int               $id
 * @property string|null       $name
 * @property string|null       $image
 * @property bool              $active
 * @property Carbon|null       $created_at
 * @property Carbon|null       $updated_at
 * @property Collection|ItemAttribute[] $itemAttributes
 * @mixin ModelTenant
 * @package Modules\Item\Models
 * @property-read int|null     $item_attributes_count
 * @method static Builder|LineAttributeItem newModelQuery()
 * @method static Builder|LineAttributeItem newQuery()
 * @method static Builder|LineAttributeItem query()
 */
class LineAttributeItem extends ModelTenant
{
    use CacheTrait;

    protected $table = 'cat_line';

    protected $fillable = [
        'name',
        'image',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    /**
     * @return HasMany
     */
    public function itemAttributes()
    {
        return $this->hasMany(ItemAttribute::class, 'cat_line_id');
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     *
     * @return LineAttributeItem
     */
    public function setName(?string $name): LineAttributeItem
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return bool
     */
    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     *
     * @return LineAttributeItem
     */
    public function setActive(bool $active): LineAttributeItem
    {
        $this->active = $active;
        return $this;
    }

    public static function getLinesOrderByName()
    {
        $cache_key = CacheTrait::getCacheKey('lines_order_by_name');
        $lines = CacheTrait::getCache($cache_key);
        if(!$lines){
            $lines = self::select(['id', 'name'])->where('active', true)->orderBy('name')->get();
            CacheTrait::storeCache($cache_key, $lines);
        }
        return $lines;
    }
}