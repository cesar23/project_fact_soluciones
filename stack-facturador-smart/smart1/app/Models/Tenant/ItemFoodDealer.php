<?php

namespace App\Models\Tenant;


class ItemFoodDealer extends ModelTenant
{
    public $timestamps = false;
    protected $table = 'item_food_dealer';
    protected $fillable = [
        'item_id',
        'start_time',
        'end_time',
    ];

    protected $casts = [
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

 
    // public function getCollectionData()
    // {
    //     //crea un string random de 10 caracteres
    //     $data = $this->toArray();
    //     $data['item'] = $this->item;
    //     $data['item_bonus'] = $this->bonus_item->getDataToItemModal();
    //     $data['item_bonus']["bonus"] = true;
    //     $data['item_bonus']["sale_affectation_igv_type_id"] = "15";
    //     $data['item_bonus']["sale_affectation_igv_type"] = AffectationIgvType::find("15");
    //     //borrar la key 'bonus_items" del array 'bonus'
    //     unset($data['item_bonus']["bonus"]['bonus_items']);

    //     return $data;
    // }
}
