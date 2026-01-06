<?php

namespace App\Models\Tenant;

class OrderConcreteSupply extends ModelTenant
{
    protected $table = 'order_concrete_supplies';

    protected $fillable = [
        'description',
        'type',
        'quantity',
        'total',
        'order_concrete_id'
    ];

    public function orderConcrete()
    {
        return $this->belongsTo(OrderConcrete::class);
    }
} 