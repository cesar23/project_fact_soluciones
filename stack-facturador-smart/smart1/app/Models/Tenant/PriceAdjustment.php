<?php

namespace App\Models\Tenant;

use Illuminate\Support\Facades\DB;
use Modules\Item\Models\Brand;
use Modules\Item\Models\Category;

class PriceAdjustment extends ModelTenant
{
    protected $table = 'price_adjustments';

    protected $fillable = [
        'description',
        'apply_to',
        'adjustment_type',
        'operation',
        'value',
        'applied',
        'applied_at',
        'applied_by',
        'items_affected',
        'notes'
    ];

    protected $casts = [
        'applied' => 'boolean',
        'applied_at' => 'datetime',
        'value' => 'decimal:2',
        'items_affected' => 'integer',
    ];

    public function price_adjustment_items()
    {
        return $this->hasMany(PriceAdjustmentItem::class);
    }

    public function applied_by_user()
    {
        return $this->belongsTo(User::class, 'applied_by');
    }

    /**
     * Aplica el ajuste de precio a los productos seleccionados
     */
    public function apply()
    {
        if ($this->applied) {
            return [
                'success' => false,
                'message' => 'Este ajuste de precios ya fue aplicado'
            ];
        }

        $itemsAffected = 0;

        // Construir la query base según el tipo de aplicación
        $query = Item::query();

        if ($this->apply_to === 'category') {
            $categoryIds = $this->price_adjustment_items()
                ->whereNotNull('category_id')
                ->pluck('category_id')
                ->toArray();
            $query->whereIn('category_id', $categoryIds);
        } elseif ($this->apply_to === 'brand') {
            $brandIds = $this->price_adjustment_items()
                ->whereNotNull('brand_id')
                ->pluck('brand_id')
                ->toArray();
            $query->whereIn('brand_id', $brandIds);
        } elseif ($this->apply_to === 'specific') {
            $itemIds = $this->price_adjustment_items()
                ->whereNotNull('item_id')
                ->pluck('item_id')
                ->toArray();
            $query->whereIn('id', $itemIds);
        }
        // Si es 'all', la query ya está sin filtros

        // Calcular el ajuste según el tipo
        if ($this->adjustment_type === 'percentage') {
            $percentage = $this->value / 100;
            if ($this->operation === 'increase') {
                // Aumento por porcentaje: precio * (1 + porcentaje)
                $itemsAffected = $query->update([
                    'sale_unit_price' => DB::raw("sale_unit_price * (1 + {$percentage})")
                ]);
            } else {
                // Disminución por porcentaje: precio * (1 - porcentaje)
                $itemsAffected = $query->update([
                    'sale_unit_price' => DB::raw("GREATEST(0, sale_unit_price * (1 - {$percentage}))")
                ]);
            }
        } else {
            // Ajuste por monto fijo
            $amount = $this->value;
            if ($this->operation === 'increase') {
                $itemsAffected = $query->update([
                    'sale_unit_price' => DB::raw("sale_unit_price + {$amount}")
                ]);
            } else {
                $itemsAffected = $query->update([
                    'sale_unit_price' => DB::raw("GREATEST(0, sale_unit_price - {$amount})")
                ]);
            }
        }

        // Marcar como aplicado
        $this->applied = true;
        $this->applied_at = now();
        $this->applied_by = auth()->id();
        $this->items_affected = $itemsAffected;
        $this->save();

        return [
            'success' => true,
            'message' => "Ajuste aplicado correctamente a {$itemsAffected} productos",
            'items_affected' => $itemsAffected
        ];
    }

    /**
     * Calcula el nuevo precio basado en el tipo de ajuste y operación
     */
    private function calculateNewPrice($oldPrice)
    {
        if ($this->adjustment_type === 'percentage') {
            $adjustment = $oldPrice * ($this->value / 100);
        } else {
            $adjustment = $this->value;
        }

        if ($this->operation === 'increase') {
            return $oldPrice + $adjustment;
        } else {
            return max(0, $oldPrice - $adjustment); // No permitir precios negativos
        }
    }
}