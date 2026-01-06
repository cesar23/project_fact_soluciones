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
     * Class Brand
     *
     * @property int               $id
     * @property string|null             $name
     * @property Carbon|null       $created_at
     * @property Carbon|null       $updated_at
     * @property Collection|Item[] $items
     * @mixin ModelTenant
     * @package App\Models
     * @property-read int|null     $items_count
     * @method static Builder|Brand newModelQuery()
     * @method static Builder|Brand newQuery()
     * @method static Builder|Brand query()
     */
    class Brand extends ModelTenant
    {
        use CacheTrait;

        protected $fillable = [
            'name',
            'image'
        ];

        /**
         * @return HasMany
         */
        public function items()
        {
            return $this->hasMany(Item::class);
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
         * @return Brand
         */
        public function setName(?string $name): Brand
        {
            $this->name = $name;
            return $this;
        }

        public static function getBrandsOrderByName()
        {
            $cache_key = CacheTrait::getCacheKey('brands_order_by_name');
            $brands = CacheTrait::getCache($cache_key);
            if(!$brands){
                $brands = self::select(['id', 'name'])->orderBy('name')->get();
                CacheTrait::storeCache($cache_key, $brands);
            }
            return $brands;
        }
    }
