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
 * Class IngredientAttributeItem
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
 * @method static Builder|IngredientAttributeItem newModelQuery()
 * @method static Builder|IngredientAttributeItem newQuery()
 * @method static Builder|IngredientAttributeItem query()
 */
class IngredientAttributeItem extends ModelTenant
{
    use CacheTrait;

    protected $table = 'cat_ingredient';

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
        return $this->hasMany(ItemAttribute::class, 'cat_ingredient_id');
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
     * @return IngredientAttributeItem
     */
    public function setName(?string $name): IngredientAttributeItem
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
     * @return IngredientAttributeItem
     */
    public function setActive(bool $active): IngredientAttributeItem
    {
        $this->active = $active;
        return $this;
    }

    public static function getIngredientsOrderByName()
    {
        $cache_key = CacheTrait::getCacheKey('ingredients_order_by_name');
        $ingredients = CacheTrait::getCache($cache_key);
        if(!$ingredients){
            $ingredients = self::select(['id', 'name'])->where('active', true)->orderBy('name')->get();
            CacheTrait::storeCache($cache_key, $ingredients);
        }
        return $ingredients;
    }
}