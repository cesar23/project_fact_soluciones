<?php

namespace App\Models\Tenant;




class DispatchOrderRouteItem extends ModelTenant
{
    protected $table = 'dispatch_orders_route_items';
    public $timestamps = false;
    protected $fillable = [
        'dispatch_order_route_id',
        'dispatch_order_id',
        'order',
    ];


    public function dispatchOrder()
    {
        return $this->belongsTo(DispatchOrder::class);
    }

    public function dispatchOrderRoute()
    {
        return $this->belongsTo(DispatchOrderRoute::class, 'dispatch_order_route_id');
    }

}
