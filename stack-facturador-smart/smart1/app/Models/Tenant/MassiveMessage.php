<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MassiveMessage extends ModelTenant
{
    use HasFactory;

    protected $table = 'massive_messages';

    protected $fillable = [
        'type',
        'subject',
        'body',
        'attachments',
    ];

    protected $casts = [
        'attachments' => 'array',
    ];

    public function details()
    {
        return $this->hasMany(MassiveMessageDetail::class, 'massive_message_id');
    }
}
