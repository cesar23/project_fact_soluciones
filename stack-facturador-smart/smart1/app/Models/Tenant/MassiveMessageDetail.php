<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Jobs\SendMassiveMessage;

class MassiveMessageDetail extends ModelTenant
{
    use HasFactory;

    protected $table = 'massive_message_detail';

    protected $fillable = [
        'massive_message_id',
        'person_id',
        'status',
        'error_message',
        'attempts',
        'last_attempt_at',
    ];

    protected $casts = [
        'last_attempt_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();
    }

    public function massiveMessage()
    {
        return $this->belongsTo(MassiveMessage::class, 'massive_message_id');
    }

    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    // public static function dispatchMessagesForBatch($massiveMessageId)
    // {
    
    //     $details = self::where('massive_message_id', $massiveMessageId)
    //                    ->where('status', 'pending')
    //                    ->get();
                       
    //     if ($details->count() > 0) {
    //         SendMassiveMessage::dispatch( $massiveMessageId);
    //     }
    // }
}
