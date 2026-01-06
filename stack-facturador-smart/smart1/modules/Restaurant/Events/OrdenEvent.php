<?php

namespace Modules\Restaurant\Events;

use App\Http\Controllers\Tenant\ConfigurationController;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Restaurant\Http\Resources\OrdenItemCollection;
use Modules\Restaurant\Models\Food;
use Modules\Restaurant\Models\Orden;
use Modules\Restaurant\Models\OrdenItem;
use Modules\Restaurant\Models\Table;
use Illuminate\Support\Str;

class OrdenEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $order_item;
    public function __construct($id)
    {
        $orden = OrdenItem::find($id);
        $user = User::find($orden->user_id)->name;
        $general_orden  = Orden::find($orden->orden_id);
        $table = Table::find($general_orden->table_id);
        $food = Food::find($orden->food_id);
        $observation = $orden->observations;
        $this->order_item = array('id' => $id, 'time' => $orden->time, 'user' => $user, 'orden' => $general_orden,  'food' => $food, 'table' => $table, 'observations' => $observation);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return
            new Channel('orden_request');
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
    
        return 'order-request-' . $event_name;
    }
}
