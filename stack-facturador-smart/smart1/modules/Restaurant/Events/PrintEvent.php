<?php

namespace Modules\Restaurant\Events;

use App\Http\Controllers\Tenant\ConfigurationController;
use App\Models\Tenant\User;
use App\Models\Tenant\Document;
use App\Models\Tenant\SaleNote;
use Illuminate\Support\Str;
use App\Models\Tenant\Configuration;
use App\Models\Establishment;
use App\Models\Tenant\Establishment as TenantEstablishment;
use Modules\Restaurant\Models\Area;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Modules\Restaurant\Models\OrdenItem;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PrintEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $data;
    public function __construct($id,$document_type=0,$printing=true,$area_id=null)
    {


        $establishment=TenantEstablishment::findOrFail(auth()->user()->establishment_id);
        $configuration =Configuration::first();
        $multiple_boxes = (bool) $configuration->multiple_boxes;
        //
        if($document_type==0){
            $orderItem=OrdenItem::where('orden_id','=',$id)->first();
            if($orderItem==null){
              $user=User::where('id',auth()->user()->id)->first();
            }else{
              $user=User::where('id',$orderItem->user_id)->first();
            }
        }else{
            $user=User::where('id',auth()->user()->id)->first();    
        }
        if(auth()->user()->type=='admin'){
            $area_printer=Area::where('description', 'like','%caja%')->first();
            $area_id=$area_printer->id;
            $printerName=$area_printer->printer;
            $copies=$area_printer->copies;
        }else{
            if($area_id!=null){
                $area_printer=Area::findOrFail($area_id);
                $printerName=$area_printer->printer;
                $copies=$area_printer->copies;
            }else{
                if($configuration->multiple_boxes==true){
                    if($area_id==null){
                        $area_printer=Area::where('description', 'like','%caja%')->first();
                        $area_id=$area_printer->id;
                        $printerName=$area_printer->printer;
                        $copies=$area_printer->copies;
                    }
                }else{
                    $printerName=$establishment->printer;
                    $copies=$establishment->copies;
                }
            }
        }




        switch ($document_type) {
            case "0":
                $documentLink = url('')."/restaurant/worker/print-ticket?id={$id}&area_id={$area_id}";
                break;
            case "01":
                $doc=Document::where('id',$id)->first();
                $documentLink = url('')."/print/document/{$doc->external_id}/ticket";
                break;
            case "03":
                $doc=Document::where('id',$id)->first();
                $documentLink = url('')."/print/document/{$doc->external_id}/ticket";
                break;
            case "80":
                $doc=SaleNote::where('id',$id)->first();
                 $documentLink = url('')."/sale-notes/print/{$doc->external_id}/ticket";
                break;
        }


        $this->data = array(
            'printer' => $printerName,
            'printing' =>$printing,
            'copies' => $copies,
            'direct_printing' => (bool) $establishment->direct_printing,
            'print'   => $documentLink,
            'multiple_boxes' => (bool) $configuration->multiple_boxes,
            'typeuser' =>auth()->user()->type,
            'user_id' => $user->id,
            'area_id' => $area_id
        );
     }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return
            new Channel('print_orden');
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
        return 'print-order-' . $event_name;
    }
}
