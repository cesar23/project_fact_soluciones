<?php

namespace App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;

class ApportionmentItemsStock extends ModelTenant
{
   // protected $table = 'pr';
    protected $table = 'apportionment_items_stock';
    protected $fillable = [
        "purchase_item_id",
        "item_id",
        "warehouse_id",
        "stock",
        "stock_remaining",
        "unit_price_apportioned",
        "observation",
    
    ];

    public function purchase_item()
    {
        return $this->belongsTo(PurchaseItem::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public static function calculateStockRemaining($item_id, $quantity)
    {
        $apportionment_items_stock = self::where('item_id', $item_id)
            ->where('stock_remaining', '>', 0)
            ->orderBy('created_at', 'asc') // Ordenar por fecha de creación (más antiguo primero)
            ->get();
        
        $stock_remaining = $apportionment_items_stock->sum('stock_remaining');
        return $stock_remaining;
    }

    public static function updateStockRemaining($item_id, $quantity_to_subtract)
    {
        $apportionment_items_stock = self::where('item_id', $item_id)
            ->where('stock_remaining', '>', 0)
            ->orderBy('created_at', 'asc') // Ordenar por fecha de creación (más antiguo primero)
            ->get();
        if($apportionment_items_stock->isEmpty()) return 0;
        
        $remaining_quantity = $quantity_to_subtract;
        
        foreach ($apportionment_items_stock as $item_stock) {
            if ($remaining_quantity <= 0) {
                break; // Ya no hay cantidad por restar
            }
            
            if ($item_stock->stock_remaining >= $remaining_quantity) {
                // Este registro tiene suficiente stock para completar la resta
                $item_stock->stock_remaining -= $remaining_quantity;
                $item_stock->save();
                $remaining_quantity = 0;
            } else {
                // Este registro no tiene suficiente stock, restar todo y continuar
                $remaining_quantity -= $item_stock->stock_remaining;
                $item_stock->stock_remaining = 0;
                $item_stock->save();
            }
        }
        
        return $remaining_quantity; // Retorna la cantidad que no se pudo restar (si es 0, se completó exitosamente)
    }

   
}