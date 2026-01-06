<?php

namespace App\Models\Tenant;




class DispatchOrderRoute extends ModelTenant
{
    protected $table = 'dispatch_orders_route';

    protected $fillable = [
        'description'
    ];


    public function items()
    {
        return $this->hasMany(DispatchOrderRouteItem::class, 'dispatch_order_route_id');
    }

    public function getCollectionData()
    {
        $date = $this->created_at->format('Y-m-d');
        $description = $this->description;
        return [
            'id' => $this->id,
            'description' => $description,
            'date' => $date,
            'items_count' => $this->items->count()
        ];
    }

}
