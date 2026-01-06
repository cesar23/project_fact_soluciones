<?php

namespace App\Http\Controllers\Tenant\Api;

use Exception;
use Carbon\Carbon;
use App\Models\Box;
use App\Models\Tenant\User;
use App\Models\Tenant\Company;
use App\Models\Tenant\Document;
use App\Models\Tenant\SaleNote;
use Illuminate\Http\Request;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Establishment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Restaurant\Models\Area;
use Illuminate\Support\Facades\Auth;
use Modules\Restaurant\Models\Orden;
use Modules\Restaurant\Models\Table;
use Modules\Restaurant\Models\OrdenItem;
use Modules\Restaurant\Events\OrdenEvent;
use Modules\Restaurant\Events\PrintEvent;
use Modules\Restaurant\Events\OrdenCancelEvent;
use Modules\Restaurant\Http\Resources\OrdenCollection;
use Modules\Restaurant\Http\Resources\OrdenItemCollection;

class OrdenController extends Controller
{
    public function printTicket(Request $request)
    {
        $company = Company::first();
        $orden = $request->id;

        $ordenes = Orden::where('id', $request->id)->first();
        if ($ordenes == null) {
            return [
                "success" => false,
                "message" => "Nº Pedido no existe..."
            ];
        }
        $ordenItem = OrdenItem::where('orden_id', $ordenes->id)->first();
        $user = User::findOrFail($ordenItem->user_id);
        $establishment = Establishment::where('id', $user->establishment_id)->first();
        //
        if ($request->area_id == 0) {
            $ordens = OrdenItem::where('orden_id', $orden);
        } else {
            $ordens = OrdenItem::where('orden_id', $orden)->where('area_id', $request->area_id);
        }

        $orden_items = $ordens->get();

        $date = Carbon::today()->format('d-m-y');
        if ($ordens->count() == 1) {
            $height = 250;
        } else {
            $height = 230;
        }
        // $height=$height+$ordens->count()*20;
        $height = 8 * 40;
        $height = $height + $ordens->count() * 20;
        try {
            $pdf = PDF::loadView('restaurant::ordens.ticket', compact('establishment', 'date', 'company', 'orden', 'ordenes', 'orden_items'))
                ->setPaper(array(0, 0, 249.45, $height));
        } catch (Exception $e) {
            return ['m' => $e->getMessage()];
        }

        return $pdf->stream('pdf_file.pdf');
    }
    public function index()
    {
        $configuration = Configuration::first();

        return view('restaurant::ordens.index', compact('configuration'));
    }
    public function columns()
    {
        return [
            'date' => 'Fecha',
            'number' => 'Nº Orden',
            'customer_id' => 'Clientes'
        ];
    }
    public function ordenslist()
    {
        $date = Carbon::now()->format('Y-m-d');
        $ordens = new OrdenCollection(Orden::whereDate('date', '=', $date)->get());
        return [
            'success' => true,
            'data' => $ordens
        ];
    }
    public function records(Request $request)
    {
        $configuration = Configuration::first();

        if ($request->column == 'client') {
            $records = Orden::whereHas('customer', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->value}%")
                    ->orWhere('number', 'like', "%{$request->value}%");
            });
        } else if ($request->column == 'date') {
            $records = Orden::whereBetween('date', [$request->desde, $request->hasta]);
        } else {
            if ($configuration->commands_fisico == 1) {
                $records = Orden::where('commands_fisico', 'like', "%{$request->value}%");
            } else {
                $records = Orden::where($request->column, 'like', "%{$request->value}%");
            }
        }

        return new OrdenCollection($records->paginate(100));
    }

    public function state(Request $request)
    {
        $id = $request->id;
        $orden = Orden::find($id);
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
        $orden = Orden::find($id);
        $establishment = Establishment::findOrFail(auth()->user()->establishment_id);
        if ($orden == null) {
            return [
                'success' => false,
                'print'  => "Nº Orden no existe"
            ];
        } else {
            return [
                'success' => true,
                'data' => $orden,
                'printer' => $establishment->printer,
                'direct_printing' => (bool) $establishment->direct_printing,
                'printer_serve' => $establishment->printer_serve,
                'print'   => url('') . "/restaurant/worker/print-ticket/{$id}"
            ];
        }
    }
    public function store(Request $request)
    {
        try {
            $configuration = Configuration::first();
            if ($configuration->commands_fisico == true) {
                $orden = Orden::where('commands_fisico', $request->commands_fisico)->where('status_orden_id', '4')->first();
                if ($orden !== null) {
                    return [
                        'success' => false,
                        'message' => 'Nº de Comanda ya fue cobradao'
                    ];
                }
            }
            if ($request->caja == false) {
                $pin = $request->pin;
                $user = User::where('pin', $pin)->first();
                if (!$user || !$pin) {
                    return [
                        'success' => false,
                        'message' => 'Pin incorrecto'
                    ];
                }
            } else {
                $user = User::where('id', auth()->user()->id)->first();
            }
            if ($configuration->commands_fisico == true) {
                $Orden = Orden::where('commands_fisico', $request->commands_fisico)->first();
                if ($Orden != null) {
                    $id = $Orden->id;
                } else {
                    $id = null;
                }
            } else {
                if ($request->id != null) {
                    $Orden = Orden::find($request->id);
                    $id = $Orden->id;
                } else {
                    $id = $request->id;
                }
            }

            $new_orden = collect($request->orden);
            $items = $request->items;
            $user_id = $user->id;
            $message = 'Pedido realizado.';

            if ($request->caja == true) {
                $table = Table::where('number', "caja")->first();
                if ($table == null &&  auth()->user()->type != 'admin') {
                    $table = Table::firstOrNew(['id' => $request->table_id]);
                    $table->area_id =  auth()->user()->area_id != null ?  auth()->user()->area_id : null;
                    $table->number = 'caja';
                    $table->status_table_id = '2';
                    $table->save();
                }
            } else {
                if (isset($request->orden['table_id'])) {
                    $table = Table::find($request->orden['table_id']);
                    $table->status_table_id = 2;
                    $table->save();
                }
            }

            //si la orden existe solo quiero agregar
            //los items

            if ($id != null) {
                $orden = Orden::find($id);
                $table = Table::find($orden->table_id);
                $message = 'Pedido agregado.'; //me refiero en punto de caja
                //creo la orden y guardo los items
            } else {
                $orden = new Orden;
                $orden = $orden->fill($new_orden->all());
                $orden->date = $request->date_opencash;
                $orden->to_carry = $request->to_carry;
                $orden->commands_fisico = $request->commands_fisico;

        
                $orden->save();
                $table = Table::find($orden->table_id);
                $table->status_table_id = 2;
                $table->save();
            }

            /* ----------------------------- */

            foreach ($items as $item) {

                $orden_item = new OrdenItem;
                $orden_item->food_id = $item['food']['id'];
                $orden_item->observations = $item['observation'] ?? '-';
                $orden_item->quantity = $item['quantity'];
                $orden_item->price = $item['food']['price'];
                $orden_item->user_id = $user_id;
                $orden_item->orden_id = $orden->id;
                $orden_item->status_orden_id = 1;
                $orden_item->date = $request->date_opencash == null ? date('Y-m-d') : $request->date_opencash;
                $orden_item->time = date('H:i:s');
                $orden_item->area_id = $item['food']['area_id'];
                $orden_item->save();
                event(new OrdenEvent($orden_item->id));
            }

            if ($configuration->restaurant == true) {
                if (auth()->user()->type == 'admin') {
                    $area = Area::where('description', 'like', '%caja%')->first();
                } else {
                    $area = Area::findOrFail(auth()->user()->area_id);
                }
                event(new PrintEvent($orden->id, "0", $request->printing, null));
                if ($configuration->print_kitchen == true) {
                    $foods_area = OrdenItem::select(DB::raw("DISTINCT(area_id) as area_id"))->where('orden_id', $orden->id);
                    foreach ($foods_area->get() as $row) {
                        event(new PrintEvent($orden->id, "0", $request->printing, $row->area_id));
                    }
                }
            }
            $id = strval($orden->id);
            $establishment = Establishment::findOrFail(auth()->user()->establishment_id);
            return [
                'id' => $orden->id,
                'success' => true,
                'message' => $message,
                'ordenId' => $orden->id,
                'printer' => $establishment->printer,
                'copies' => $establishment->copies,
                'printer_serve' => $establishment->printer_serve,
                'direct_printing' => (bool) $establishment->direct_printing,
                'print'   => url('') . "/restaurant/worker/print-ticket/{$id}"
            ];
            /* ----------------------------- */
        } catch (Exception $e) {
            //   $ordens_items = OrdenItem::where('orden_id', $orden->id)->count();
            //     if ($ordens_items == 0) {
            //         $orden->delete();
            //         $table = Table::find($orden->table_id);
            //         $table->status_table_id = 1;
            //         $table->save();
            //     }
            return [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ];
        }
    }
    public function cancelOrden(Request $request)
    {
        $id = $request->id;
        $items = OrdenItem::where('orden_id', $id)->get();

        foreach ($items as $item) {
            //cancelar orden
            $item->delete();
            //event(new OrdenCancelEvent($item->id));
        }
        $orden = Orden::find($id);
        $orden->delete();
        $table_id = $orden->table_id;
        $ordens = Orden::where('status_orden_id', 1)->where('table_id', $table_id)->count();
        if ($ordens == 0) {
            $table = Table::find($table_id);
            $table->status_table_id = 1;
            $table->save();
        }
        return ['success' => true, 'message' => 'Orden cancelada con éxito.'];
    }
    public function destroyorden($id)
    {
        $configuration = Configuration::first();
        if ($configuration->commands_fisico == 1) {
            $search = Orden::where('commands_fisico', $id)->first();
            if ($search !== null) {
                $orden = Orden::find($search->id);
            }
        } else {
            $orden = Orden::find($id);
        }
        if ($orden->document_id != null) {
            Document::where('orden_id', $orden->id)->delete();
        }
        if ($orden->sale_note_id != null) {
            SaleNote::where('orden_id', $orden->id)->delete();
        }
        $table_id = $orden->table_id;
        $ordens = Orden::where('status_orden_id', 1)->where('table_id', $table_id)->count();
        if ($ordens == 0) {
            $table = Table::find($table_id);
            $table->status_table_id = 1;
            $table->save();
        }

        if ($orden->sale_note_id == null || $orden->document_id == null) {
            OrdenItem::where('orden_id', $orden->id)->delete();
            $orden->delete();
        }
        return ['success' => true, 'message' => 'Orden anulada con éxito.'];
    }

    public function finishOrden(Request $request)
    {


        $id = $request->id;
        $orden = Orden::find($id);
        $orden->status_orden_id = 4;

        $orden->save();

        //enviar evento pa eliminar las ordenes listas

        return [
            'success' => true,
            'message' => 'Orden finalizada'
        ];
    }
}
