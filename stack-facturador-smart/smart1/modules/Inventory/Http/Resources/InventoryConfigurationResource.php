<?php

namespace Modules\Inventory\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InventoryConfigurationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'stock_control' => (bool) $this->stock_control,             
            'generate_internal_id' => (bool) $this->generate_internal_id,
            'inventory_review' => $this->inventory_review,
            'validate_stock_add_item' => $this->validate_stock_add_item,
            'confirm_inventory_transaction' =>  (bool)$this->confirm_inventory_transaction,
            'order_note_with_stock'  => (bool)$this->order_note_with_stock,
            'item_set_by_warehouse' => (bool)$this->item_set_by_warehouse,
            'show_other_view_inventory' => (bool)$this->show_other_view_inventory,
        ];
    }
}