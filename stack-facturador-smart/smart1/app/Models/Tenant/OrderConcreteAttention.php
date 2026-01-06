<?php

namespace App\Models\Tenant;

class OrderConcreteAttention extends ModelTenant
{
    protected $table = 'order_concrete_attentions';

    protected $fillable = [
        'dispatch_note',
        'quantity',
        'order_concrete_id'
    ];

    public function orderConcrete()
    {
        return $this->belongsTo(OrderConcrete::class);
    }
} 