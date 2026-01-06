<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['sender_id', 'receiver_id', 'message'];

    protected $connection = 'tenant'; 

    public function sender()
    {
        return $this->belongsTo(Personal::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(Personal::class, 'receiver_id');
    }
}
