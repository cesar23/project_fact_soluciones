<?php

namespace App\Models\Tenant;

class TelephonePerson extends ModelTenant
{


    protected $table = 'telephones_persons';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['person_id', 'telephone'];
}
