<?php

namespace App\Models\Tenant;

use Modules\Order\Models\OrderNote;

class FoodDealerAuth extends ModelTenant
{
    protected $table = 'food_dealer_auth';
    protected $fillable = [
        'person_food_dealer_id',
        'auth_user_id',
        'order',
        'user_name',
    ];

    protected $casts = [
    ];

    public function person_food_dealer()
    {
        return $this->belongsTo(PersonFoodDealer::class);
    }
    

    public function auth_user()
    {
        return $this->belongsTo(User::class);
    }
}

