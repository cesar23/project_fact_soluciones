<?php

namespace App\Models\Tenant;

/**
 * Class Customer
 *
 * @package App\Models\Tenant
 * @mixin ModelTenant
 */
class MessageIntegrateSystem extends ModelTenant
{
    public $timestamps = false;
    protected $table = 'messages_email_integrate_system';
    protected $fillable = [
        'trigger_event',
        'comment',
        'message',
        'active',
    ];

    public static function getMessage($trigger_event)
    {
        $message = self::where('trigger_event', $trigger_event)
            ->where('active', true)
            ->first();
        return $message ? $message->message : '';
    }
}
