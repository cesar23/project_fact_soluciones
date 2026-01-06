<?php

namespace Modules\Inventory\Models;

use App\Models\Tenant\Item;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class ValidationWarehouseItem extends Model
{
    use UsesTenantConnection;

    public $timestamps = false;
    protected $table = 'validation_warehouse_items';
    protected $fillable = [
        'validation_warehouse_id',
        'item_id',
        'quantity',
        'stock',
        'lots',
        'lots_not_count',
    ];

    protected $casts = [
        'quantity' => 'float',
        'stock' => 'float',
        'lots' => 'array',
        'lots_not_count' => 'array',
    ];
    
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function validation_warehouse()
    {
        return $this->belongsTo(ValidationWarehouse::class);
    }

    public function setLotsAttribute($value)
    {
        $this->attributes['lots'] = json_encode($value);
    }

    public function getLotsAttribute($value)
    {
        return (is_null($value)) ? null : json_decode($value, true);
    }

    public function setLotsNotCountAttribute($value)
    {
        $this->attributes['lots_not_count'] = json_encode($value);
    }

    public function getLotsNotCountAttribute($value)
    {
        return (is_null($value)) ? null : json_decode($value, true);
    }



    
}
