<?php

namespace App\Models\Tenant;

class ItemPricePaymentCondition extends ModelTenant
{

    protected $table = 'item_price_payment_condition';
    protected $fillable = [
        'item_id',
        'payment_condition_id',
        'price',
    ];
    public $timestamps = true;

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function payment_condition()
    {
        return $this->belongsTo(PaymentCondition::class, 'payment_condition_id');
    }
    

}
