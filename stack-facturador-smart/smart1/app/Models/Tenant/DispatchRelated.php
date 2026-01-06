<?php

namespace App\Models\Tenant;


class DispatchRelated extends ModelTenant
{   
    public $timestamps = false;
    protected $table = 'dispatches_related_to_dispatch';
    protected $fillable = [
        'dispatch_id',
        'serie_number',
        'company_number',
        'document_type'
    ];



    public function dispatch()
    {
        return $this->belongsTo(Dispatch::class, 'dispatch_id');
    }


}
