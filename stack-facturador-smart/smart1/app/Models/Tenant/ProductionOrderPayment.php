<?php

namespace App\Models\Tenant;


use Hyn\Tenancy\Traits\UsesTenantConnection;



class ProductionOrderPayment extends ModelTenant
{
    use UsesTenantConnection;
    
    public $timestamps = false;
    protected $with = [];

    protected $fillable = [
        "production_order_id",
        "date_of_payment",
        "payment_method_type_id",
        "has_card",
        "card_brand_id",
        "reference",
        "change",
        "payment",
        
    ];

    protected $casts = [
        'date_of_payment' => 'date',
    ];
    public function payment_method_type()
    {
        return $this->belongsTo(PaymentMethodType::class, 'payment_method_type_id');
    }

    public function card_brand()
    {
        return $this->belongsTo(CardBrand::class, 'card_brand_id');
    }

    public function production_order()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

}
