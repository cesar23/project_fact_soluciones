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
class RegisterInputsMovement extends ModelTenant
{
    protected $table = 'register_inputs_movements';

    protected $fillable = [
        'date_of_issue',
        'person_id',
        'item_id',
        'quantity',
        'warehouse_id',
        'lot_code',
        'observation',
    ];

    protected $casts = [
        'date_of_issue' => 'date',
        'quantity' => 'decimal:4',
    ];

    /**
     * Get the person associated with this movement
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * Get the item associated with this movement
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the warehouse associated with this movement
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}