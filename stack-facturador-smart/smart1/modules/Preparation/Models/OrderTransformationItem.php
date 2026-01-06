<?php

namespace Modules\Preparation\Models;

use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\Person;
use App\Models\Tenant\Item;
use Modules\Inventory\Models\Warehouse;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class RegisterInputsMovement
 *
 * @package Modules\Preparation\Models
 * @property int $id
 * @property string $date_of_issue
 * @property int|null $person_id
 * @property int $item_id
 * @property float $quantity
 * @property int $warehouse_id
 * @property string|null $lot_code
 * @property string|null $observation
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class OrderTransformationItem extends ModelTenant
{
    protected $table = 'order_transformation_items';

    protected $fillable = [
        'order_transformation_id',
        'item_id',
        'quantity',
        'unit_price',
        'lot_code',
        'status',
        'item_type'
    ];

    protected $casts = [
    ];

    public function orderTransformation(): BelongsTo
    {
        return $this->belongsTo(OrderTransformation::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

}