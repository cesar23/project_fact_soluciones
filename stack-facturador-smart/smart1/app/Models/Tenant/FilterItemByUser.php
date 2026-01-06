<?php

namespace App\Models\Tenant;

use App\Traits\CacheTrait;
use Hyn\Tenancy\Traits\UsesTenantConnection;

class FilterItemByUser extends ModelTenant
{
    use UsesTenantConnection,CacheTrait;
    public $timestamps = false;
    protected $table = 'filter_items_for_users';
    protected $fillable = [
        'user_id',
        'filter_active',
        'filter_name'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
