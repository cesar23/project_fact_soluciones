<?php

namespace App\Models\Tenant;

use Modules\Order\Models\OrderNote;

class PersonFoodDealer extends ModelTenant
{
    public $timestamps = false;
    protected $table = 'person_food_dealer';
    protected $with = ['food_dealer_auth'];
    protected $fillable = [
        'item_id',
        'person_id',
        'order_note_id',
        'date_of_issue',
    ];

    protected $casts = [
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function order_note()
    {
        return $this->belongsTo(OrderNote::class);
    }

    public function food_dealer_auth(){
        return $this->hasOne(FoodDealerAuth::class);
    }
    
}
