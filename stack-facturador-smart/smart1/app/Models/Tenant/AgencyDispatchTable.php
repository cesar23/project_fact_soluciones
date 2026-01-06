<?php

namespace App\Models\Tenant;

use Hyn\Tenancy\Abstracts\TenantModel;

class AgencyDispatchTable extends TenantModel
{
    protected $table = 'agencies_dispatches_table';
    public $timestamps = false;
    protected $with = ['agency'];
    protected $fillable = [
        'date_of_issue',
        'agency_id',
        'destination',
        'reference',
        'dispatch_file',
        'dispatch_order_id'

    ];


    public function agency()
    {
        return $this->belongsTo('App\Models\Tenant\Agency');
    }
}
