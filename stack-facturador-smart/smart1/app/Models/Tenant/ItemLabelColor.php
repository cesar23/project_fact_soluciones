<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemLabelColor extends ModelTenant
{
    protected $fillable = [
        'item_id',
        'label_color_id',
        'warehouse_id'
    ];

    /**
     * Relación con el item
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Relación con el color de etiqueta
     */
    public function labelColor(): BelongsTo
    {
        return $this->belongsTo(LabelColor::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}