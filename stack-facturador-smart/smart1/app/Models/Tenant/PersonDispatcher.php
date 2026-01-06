<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Catalogs\IdentityDocumentType;

class PersonDispatcher extends ModelTenant
{   protected $table = 'person_dispatchers';
    public $timestamps = false;
    protected $fillable = [
        'identity_document_type_id',
        'number',
        'name',
        'telephone',
    ];

    public function identity_document_type()
    {
        return $this->belongsTo(IdentityDocumentType::class, 'identity_document_type_id');
    }
    

}
