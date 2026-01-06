<?php

namespace Modules\Restaurant\Models;

use App\Models\Tenant\Item;
use App\Models\Tenant\ModelTenant;

class OrdenItem extends ModelTenant
{

    public $timestamps = false;
    protected $table = "orden_item";
    protected $with = ['status'];

    protected $fillable = [
        'date',
        'time',
        'observations',
        'orden_id',
        'item_id',
        'status_orden_id',
        'area_id',
        'quantity',
        'price',
        'batch_number',
     ];

    public function item()
    {
        return  $this->belongsTo(Item::class);
    }
    public function status()
    {
        return  $this->belongsTo(StatusOrden::class);
    }
    public function orden()
    {
        return  $this->belongsTo(Orden::class);
    }


}
