<?php

/**
 */

namespace Modules\FullSuscription\Models\Tenant;


use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\Person;
use Hyn\Tenancy\Traits\UsesTenantConnection;


class FullSuscriptionCreditPerson extends ModelTenant
{
    use UsesTenantConnection;


    protected $table = 'person_full_suscription_credit';

    protected $fillable = [
        'person_id',
        'amount'
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }
}
