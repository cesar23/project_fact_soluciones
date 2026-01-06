<?php

namespace App\Http\Controllers\Tenant\Api;

use Modules\Restaurant\Events\OrdenReadyEvent;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Restaurant\Events\OrdenCancelEvent;
use Modules\Restaurant\Http\Resources\OrdenItemCollection;
use Modules\Restaurant\Models\Orden;
use Modules\Restaurant\Models\OrdenItem;
use Modules\Restaurant\Models\Table;

class OrdenItemController extends Controller
{

    public function updateObservation(Request $request)
    {

        $id = $request->id;
        $observation = $request->observation;
        $orden = OrdenItem::find($id);

        $orden->observations  = $observation;
        $orden->save();
        return [
            'success' => true,
            'message' => 'Observación actualizada'
        ];
    }
    public function ordenDelete($id)
    {
        try {
            $orden_item = OrdenItem::find($id);
            $orden = Orden::find($orden_item->orden_id);
            $orden_item->delete();
            $orden_count = Orden::where('id',$orden_item->orden_id)->count();
            if($orden_count==0){
                $orden_delete = Orden::findOrFail($orden_item->orden_id);
                $orden_delete->delete();
            }
            event(new OrdenCancelEvent($id));
            $hasItems = OrdenItem::where('orden_id', $orden->id)->get();
            if ($hasItems->isEmpty()) {
                $table = Table::find($orden->table_id);
                $table->status_table_id = 1;
                $table->save();
                $orden->delete();
            }
            return [
                'success' => true,
                'message' => 'Pedido cancelado con éxito'
            ];
        } catch (Exception $e) {

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    public function ordensStatus(){
        $ordens=Orden::where('status',"1")->get();
        return compact('ordens');
    }
    public function ordenReady($id)
    {

        try {
            $orden_item = OrdenItem::find($id);
            $orden_item->status_orden_id = 3;
            $orden_item->save();
            $ordenCount=OrdenItem::where('id',$orden_item->orden_id)->where('status_orden_id',1)->count();
           if($ordenCount==0){
              $Orden= Orden::find($orden_item->orden_id);
              $Orden->status = 0;
              $Orden->save();
           }
            event(new OrdenReadyEvent($id));
            return [
                'success' => true,
                'message' => 'Orden lista'
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function records()
    {
        $date = Carbon::now()->format('Y-m-d');

        $ordens = new OrdenItemCollection(OrdenItem::whereDate('date', '=', $date)->where('status_orden_id', 1)->get());

        return [
            'success' => true,
            'data' => $ordens
        ];
    }
    public function list_ordens_items()
    {
        $date = Carbon::now()->format('Y-m-d');
        //dd(auth()->user()->area_id);
        $ordens = new OrdenItemCollection(OrdenItem::whereDate('date', '=', $date)->where('status_orden_id', 1)->get());

        return [
            'success' => true,
            'data' => $ordens
        ];
    }
    public function state(Request $request)
    {
        $id = $request->id;
        $orden = OrdenItem::find($id);
        $orden->active = !$orden->active;
        $orden->save();
        return [
            'success' => true,
            'data' => $orden,
            'message' => 'Área ' . ($orden->active ? 'activada' : 'desactivada')
        ];
    }
    public function record($id)
    {
        $orden = OrdenItem::find($id);

        return [
            'success' => true,
            'data' => $orden
        ];
    }

    public function destroy($id)
    {
        //
    }
}
