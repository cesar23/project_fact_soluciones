<?php

namespace Modules\Restaurant\Events;

use App\Http\Controllers\Tenant\ConfigurationController;
use Illuminate\Support\Str;
use App\Models\Tenant\Configuration;
use App\Models\Establishment;
use Modules\Restaurant\Models\Food;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Modules\Restaurant\Models\OrdenItem;

class StockEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $data;
    public function __construct($id)
    {
        $order_item=OrdenItem::where('orden_id',$id)->get();
        $this->data = array('order_item' => $order_item);
     }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('stock_orden');
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
        return 'stock-order-' . $event_name;
    }
}
