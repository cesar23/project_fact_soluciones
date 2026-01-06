<?php

namespace Modules\Certificate\Models;

use App\Models\Tenant\ModelTenant;

class Certificate extends ModelTenant
{

    protected $fillable = [
        'tag_1',
        'tag_3',
        'tag_4',
        'tag_5',
        'tag_6',
        'tag_7',
        'tag_8',
        'tag_9',
        'external_id',
        'water_mark_image',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(CertificateItem::class);
    }



    
}