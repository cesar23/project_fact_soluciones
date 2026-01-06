<?php

namespace Modules\Preparation\Models;

use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\Person;
use App\Models\Tenant\User;
use Modules\Inventory\Models\Warehouse;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class OrderTransformation
 *
 * @package Modules\Preparation\Models
 * @property int $id
 * @property string $series
 * @property string $number
 * @property string $date_of_issue
 * @property int|null $person_id
 * @property int $warehouse_id
 * @property int $destination_warehouse_id
 * @property string $condition
 * @property string $status
 * @property string|null $observation
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class OrderTransformation extends ModelTenant
{
    protected $table = 'order_transformations';

    protected $fillable = [
        'series',
        'number',
        'date_of_issue',
        'user_id',
        'person_id',
        'warehouse_id',
        'destination_warehouse_id',
        'condition',
        'status',
        'prod_start_date',
        'prod_start_time',
        'prod_end_date',
        'prod_end_time',
        'prod_responsible',
        'mix_start_date',
        'mix_start_time',
        'mix_end_date',
        'mix_end_time',
        'mix_responsible',
        'observation'
    ];

    protected $casts = [
        'date_of_issue' => 'date',
    ];

    /**
     * Get the person associated with this order
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * Get the user associated with this order
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the origin warehouse
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the destination warehouse
     */
    public function destinationWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id');
    }

    /**
     * Get the items associated with this order
     */
    public function items()
    {
        return $this->hasMany(OrderTransformationItem::class);
    }

    /**
     * Get the separated stock associated with this order
     */
    public function separatedStock()
    {
        return $this->hasMany(SeparatedStock::class);
    }

}