<?php

    namespace App\Models\Tenant;

use Hyn\Tenancy\Traits\UsesTenantConnection;
    /**
     * @method static \Illuminate\Database\Eloquent\Builder|ProductionOrder whereTypeUser()
     */


    class StateDispatchOrder extends ModelTenant
    {
        use UsesTenantConnection;

        protected $with = [
          
        ];

        protected $fillable = [
           "description",
           "active"

        ];

        protected $casts = [
            'active' => 'boolean',
        ];

    }
