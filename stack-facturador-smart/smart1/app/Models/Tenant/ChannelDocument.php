<?php

namespace App\Models\Tenant;

use Hyn\Tenancy\Abstracts\TenantModel;

class ChannelDocument extends TenantModel
{

    protected $table = 'channels_documents';

    protected $fillable = [
        'id', 'channel_reg_id', 'document_id', 'dispatch_id'
    ];

    public function channel()
    {
        return $this->belongsTo(Channel::class, 'channel_reg_id');
    }

}
