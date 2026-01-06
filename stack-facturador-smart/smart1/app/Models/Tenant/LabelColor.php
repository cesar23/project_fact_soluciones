<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabelColor extends ModelTenant
{
    protected $fillable = [
        'description',
        'color'
    ];

    /**
     * Relación con los items que tienen este color de etiqueta
     */
    public function itemLabelColors(): HasMany
    {
        return $this->hasMany(ItemLabelColor::class);
    }

    /**
     * Relación con los items (muchos a muchos a través de la tabla pivot)
     */
    public function items()
    {
        return $this->belongsToMany(Item::class, 'item_label_colors');
    }
}