<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Catalogs\AffectationIgvType;

class ItemBonus extends ModelTenant
{
    public $timestamps = false;
    protected $table = 'item_bonus';
    protected $fillable = [
        'item_id',
        'item_bonus_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'float',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function bonus_item()
    {
        return $this->belongsTo(Item::class, 'item_bonus_id');
    }
    public function getCollectionData()
    {
        //crea un string random de 10 caracteres
        $data = $this->toArray();
        $data['item'] = $this->item;
        $data['item_bonus'] = $this->bonus_item->getDataToItemModal();
        $data['item_bonus']["bonus"] = true;
        $data['item_bonus']["sale_affectation_igv_type_id"] = "15";
        $data['item_bonus']["sale_affectation_igv_type"] = AffectationIgvType::find("15");
        //borrar la key 'bonus_items" del array 'bonus'
        unset($data['item_bonus']["bonus"]['bonus_items']);

        return $data;
    }
}
