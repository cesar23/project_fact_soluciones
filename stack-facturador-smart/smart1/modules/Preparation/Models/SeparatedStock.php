<?php

namespace Modules\Preparation\Models;

use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\Item;
use Modules\Inventory\Models\Warehouse;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class SeparatedStock
 *
 * @package Modules\Preparation\Models
 * @property int $id
 * @property int $item_id
 * @property int $warehouse_id
 * @property int $order_transformation_id
 * @property float $quantity
 * @property string $description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class SeparatedStock extends ModelTenant
{
    protected $table = 'separated_stock';

    protected $fillable = [
        'item_id',
        'warehouse_id',
        'order_transformation_id',
        'quantity',
        'description'
    ];

    protected $casts = [
        'quantity' => 'float',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function orderTransformation(): BelongsTo
    {
        return $this->belongsTo(OrderTransformation::class);
    }
}