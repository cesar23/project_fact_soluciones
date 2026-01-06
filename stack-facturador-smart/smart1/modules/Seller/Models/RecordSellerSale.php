<?php

namespace Modules\Seller\Models;

use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\User;

class RecordSellerSale extends ModelTenant
{

    protected  $table = 'record_sales_sellers';
    protected $fillable = [
        'user_id',
        'date_of_record',
        'total',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
