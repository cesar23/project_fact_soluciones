<?php

namespace Modules\Item\Models;

use App\Models\Tenant\Item;
use App\Models\Tenant\ModelTenant;
use App\Traits\CacheTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ItemAttribute
 *
 * @property int               $id
 * @property int               $item_id
 * @property int|null          $cat_line_id
 * @property int|null          $cat_ingredient_id
 * @property Carbon|null       $created_at
 * @property Carbon|null       $updated_at
 * @property Item              $item
 * @property LineAttributeItem|null $line
 * @property IngredientAttributeItem|null $ingredient
 * @mixin ModelTenant
 * @package Modules\Item\Models
 * @method static Builder|ItemAttribute newModelQuery()
 * @method static Builder|ItemAttribute newQuery()
 * @method static Builder|ItemAttribute query()
 */
class ItemAttribute extends ModelTenant
{
    use CacheTrait;

    protected $table = 'item_attributes';

    protected $fillable = [
        'item_id',
        'cat_line_id',
        'cat_ingredient_id'
    ];

    protected $casts = [
        'item_id' => 'integer',
        'cat_line_id' => 'integer',
        'cat_ingredient_id' => 'integer'
    ];

    /**
     * @return BelongsTo
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * @return BelongsTo
     */
    public function line()
    {
        return $this->belongsTo(LineAttributeItem::class, 'cat_line_id');
    }

    /**
     * @return BelongsTo
     */
    public function ingredient()
    {
        return $this->belongsTo(IngredientAttributeItem::class, 'cat_ingredient_id');
    }

    /**
     * @return int
     */
    public function getItemId(): int
    {
        return $this->item_id;
    }

    /**
     * @param int $item_id
     *
     * @return ItemAttribute
     */
    public function setItemId(int $item_id): ItemAttribute
    {
        $this->item_id = $item_id;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCatLineId(): ?int
    {
        return $this->cat_line_id;
    }

    /**
     * @param int|null $cat_line_id
     *
     * @return ItemAttribute
     */
    public function setCatLineId(?int $cat_line_id): ItemAttribute
    {
        $this->cat_line_id = $cat_line_id;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCatIngredientId(): ?int
    {
        return $this->cat_ingredient_id;
    }

    /**
     * @param int|null $cat_ingredient_id
     *
     * @return ItemAttribute
     */
    public function setCatIngredientId(?int $cat_ingredient_id): ItemAttribute
    {
        $this->cat_ingredient_id = $cat_ingredient_id;
        return $this;
    }
}