<?php

namespace Modules\Certificate\Models;

use App\Models\Tenant\ModelTenant;

class CertificatePerson extends ModelTenant
{

    protected $table = 'certificates_person';
    protected $fillable = [
        'certificate_id',
        'tag_1',
        'tag_2', 
        'tag_3',
        'tag_4',
        'tag_5',
        'tag_6',
        'tag_7',
        'tag_8',
        'tag_9',
        'external_id',
        'items',
        'active',
        'series',
        'number'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];


    public function certificate()
    {
        return $this->belongsTo(Certificate::class);
    }

    public function setItemsAttribute($value)
    {
        $this->attributes['items'] = (is_null($value)) ? null : json_encode($value);
    }
    
    public function getItemsAttribute($value)
    {
        return (is_null($value)) ? null : (object)json_decode($value);
    }





    
}