<?php

namespace Modules\Inventory\Models;

use App\Models\System\User;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class ValidationWarehouse extends Model
{
    use UsesTenantConnection;

    protected $table = 'validation_warehouse';    
    protected $fillable = [
        'warehouse_id',
        'user_id',
        'date_of_validation',
        'observations',
    ];

    protected $casts = [
        'date_of_validation' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(ValidationWarehouseItem::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
}
