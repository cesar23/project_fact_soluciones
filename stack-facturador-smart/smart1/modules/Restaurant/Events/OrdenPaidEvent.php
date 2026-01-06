<?php

namespace Modules\Restaurant\Events;

use App\Http\Controllers\Tenant\ConfigurationController;
use App\Models\Tenant\Configuration;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class OrdenPaidEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $paid;
    public function __construct($paid)
    {
        $this->paid = $paid;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return
            new Channel('orden_paid');
    }

    public function broadcastAs()
    {
        $configuration = Configuration::first();
        $event_name = $configuration->socket_channel;
        if (!$event_name) {
            $configuration->socket_channel = Str::random(10);
            $configuration->save();
            ConfigurationController::clearCache();
            $event_name = $configuration->socket_channel;
        }
        return 'order-paid-' . $event_name;
    }
}
