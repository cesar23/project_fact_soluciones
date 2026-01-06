<?php


    namespace App\Models\Tenant;

    use Hyn\Tenancy\Traits\UsesTenantConnection;


 
    class ItemSizeStock extends ModelTenant
    {
        use UsesTenantConnection;

        public $timestamps = false;
        protected $table = 'item_sizes';
        // protected $perPage = 25;

        protected $casts = [
            'item_id' => 'int',
            'establishment_id' => 'int',
        ];

        protected $fillable = [
            'warehouse_id',
            'item_id',
            'establishment_id',
            'stock',
            'size',
        ];


        public function establishment()
        {
            return $this->belongsTo(Establishment::class);
        }
        public function warehouse()
        {
            return $this->belongsTo(Warehouse::class);
        }

        public function item()
        {
            return $this->belongsTo(Item::class);
        }

    }
