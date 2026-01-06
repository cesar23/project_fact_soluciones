<?php


    namespace Modules\Item\Models;

    use Hyn\Tenancy\Traits\UsesTenantConnection;
    use App\Models\Tenant\Item;
use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\Warehouse;

    class ItemProperty extends ModelTenant
    {
        use UsesTenantConnection;
        protected $table="item_properties";
        protected $fillable = [
            'item_id',
            'warehouse_id',
            'chassis',
            'attribute',
            'attribute2',
            'attribute3',
            'attribute4',
            'attribute5',
            'sales_price',
            'state',
            'has_sale',
         ];
        protected $hidden = [
            'created_at',
            'updated_at',
        ];
        protected $casts = [
            'state'     => 'bool',
            'has_sale'  => 'bool'
        ];
        public function item()
        {
            return $this->belongsTo(Item::class);
        }
        public function warehouse()
        {
            return $this->belongsTo(Warehouse::class,'warehouse_id','id');
        }
        public function scopeWhereAvailableAttribute($query, $item_id)
        {
            $query->where('item_id', $item_id)->where('has_sale', false)->where('warehouse_id', $this->getCurrentWarehouseId());
        }
    }
