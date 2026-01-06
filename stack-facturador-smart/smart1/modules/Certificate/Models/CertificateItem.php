<?php

namespace Modules\Certificate\Models;


use App\Models\Tenant\ModelTenant;

class CertificateItem extends ModelTenant
{
    protected $table = 'certificates_items';
    protected $fillable = [
        'description',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function certificate()
    {
        return $this->belongsTo(Certificate::class);
    }



    
}