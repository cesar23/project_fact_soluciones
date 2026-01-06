<?php

namespace App\Models\Tenant;

class PersonFiles extends ModelTenant
{   protected $table = 'person_files';
    protected $fillable = [
        'person_id',
        'file',

    ];

   
    public function person(){
        return $this->belongsTo(Person::class);
    }

}
