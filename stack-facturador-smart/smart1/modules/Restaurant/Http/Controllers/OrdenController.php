<?php

namespace Modules\Restaurant\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Tenant\Box;
use App\Models\Tenant\User;
use App\Models\Tenant\Company;
use App\Models\Tenant\Document;
use App\Models\Tenant\SaleNote;
use App\Models\DocumentItem;
use App\Models\SaleNoteItem;
use App\Models\Tenant\Catalogs\AffectationIgvType;
use Illuminate\Http\Request;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Item;
use App\Models\Tenant\Warehouse;
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
use Modules\Store\Http\Controllers\StoreController;

class OrdenController extends Controller
{
    public function getOrdenByTable($table_id)
    {
        $orden = Orden::where('table_id', $table_id)
            ->where('status_orden_id', 1)
            ->without('orden_items', 'mesa')
            ->latest()
            ->first();
        if ($orden) {
            $batch_numbers = OrdenItem::where('orden_id', $orden->id)->pluck('batch_number')->unique()->values()->toArray();
            $items = OrdenItem::where('orden_id', $orden->id)->get()->transform(function ($item) {
                return [
                    'id' => $item->id,
                    'category' => $item->item->category_id ? $item->item->category : null,
                    'item_id' => $item->item_id,
                    'description' => $item->item->description,
                    'status_orden_id' => $item->status_orden_id,
                    'sale_affectation_igv_type_id' => $item->item->sale_affectation_igv_type_id,
                    'has_igv' => $item->item->has_igv,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'observation' => $item->observations,
                ];
            });
            $orden->items = $items;
            return [
                'success' => true,
                'data' => $orden,
                'batch_numbers' => $batch_numbers
            ];
        }
        return [
            'success' => false,
            'message' => 'No hay ordenes pendientes'
        ];
    }
    public function deleteItem($orden_item_id)
    {
        $orden_item = OrdenItem::find($orden_item_id);
        if ($orden_item) {
            $orden_item->delete();
            return [
                'success' => true,
                'message' => 'Item eliminado con éxito'
            ];
        }
        return [
            'success' => false,
            'message' => 'Item no encontrado'
        ];
    }
    public function printTicket(Request $request)
    {
        $company = Company::first();
        $orden_id = $request->orden_id;
        $category_id = $request->category_id;
        $batch_number = $request->batch_number;

        $orden = Orden::where('id', $orden_id)->first();
        if ($orden == null) {
            return [
                "success" => false,
                "message" => "Nº Pedido no existe..."
            ];
        }
        $ordenItem = OrdenItem::where('orden_id', $orden->id);
        if ($batch_number != null) {
            $ordenItem = $ordenItem->where('batch_number', $batch_number);
        }
        if ($category_id != null) {
            $ordenItem = $ordenItem->whereHas('item', function ($query) use ($category_id) {
                $query->where('category_id', $category_id);
            });
        }
        $ordenItem = $ordenItem->get();
        if ($ordenItem->count() == 0) {
            return [
                "success" => false,
                "message" => "No hay items para imprimir"
            ];
        }
        $establishment_id = null;

        $firstOrdenItem = $ordenItem->first();
        if ($firstOrdenItem) {
            $user_id = $firstOrdenItem->user_id;
            $user = User::where('id', $user_id)->first();
            $establishment_id = $user->establishment_id;
        }
        $establishment = Establishment::where('id', $establishment_id)->first();


        $orden_items = $ordenItem;

        $date = $orden->created_at->format('d-m-y H:i:s');
        if ($orden_items->count() == 1) {
            $height = 250;
        } else {
            $height = 230;
        }
        // $height=$height+$ordens->count()*20;
        $height = 8 * 60;
        $height = $height + $orden_items->count() * 20;
        try {
            $pdf = PDF::loadView('restaurant::ordens.ticket', compact('establishment', 'date', 'company', 'orden', 'orden_items', 'batch_number', 'category_id'))
                ->setPaper(array(0, 0, 226.77, $height));
        } catch (Exception $e) {
            return ['m' => $e->getMessage()];
        }

        return $pdf->stream('pdf_file.pdf');
    }
    public function changeStatus(Request $request){
        $orden_item = OrdenItem::find($request->id);
        $orden_item->status_orden_id = $request->status_orden_id;
        $orden_item->save();
        return [
            'success' => true,
            'message' => 'Estado actualizado con éxito'
        ];
    }
    public function pending(Request $request)
    {
        $orden_item = OrdenItem::whereIn('status_orden_id', [1,2, 3])->get()
            ->transform(function ($item) {
                return [
                    'id' => $item->id,
                    'description' => $item->item->description,
                    'observations' => $item->observations,
                    'status_orden_id' => $item->status_orden_id,
                    'image' => ($item->item->image_medium !== 'imagen-no-disponible.jpg')
                    ? asset('storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'items' . DIRECTORY_SEPARATOR . $item->item->image_medium)
                    : asset("/logo/{$item->item->image_medium}"),
                    'quantity' => $item->quantity,
                    'table' => optional($item->orden->table)->number,
                    'reference' => $item->orden->reference,
                    'time' => $item->time,
                ];
            });

        return [
            'success' => true,
            'data' => $orden_item
        ];
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
    public function printevent(Request $request)
    {
        $user_id = $request->user_id;
        $document_type = $request->document_type;
        $printing = $request->printing;
        $area_id = $request->area_id;
        $document_id = $request->document_id;
        $establishment = Establishment::findOrFail(auth()->user()->establishment_id);
        $configuration = Configuration::first();
        $user = User::where('id', $user_id)->first();
        if ($user->type == 'admin') {
            $area_printer = Area::where('description', 'like', '%caja%')->first();
            $area_id = $area_printer->id;
            $printerName = $area_printer->printer;
            $copies = $area_printer->copies;
        } else {
            if ($configuration->print_commands == 1 || $configuration->print_commands == true) {
                $area_printer = Area::where('description', 'like', '%caja%')->first();
                $area_id = $area_printer->id;
                $printerName = $area_printer->printer;
                $copies = $area_printer->copies;
            } else if ($area_id != null) {
                $area_printer = Area::findOrFail($area_id);
                $printerName = $area_printer->printer;
                $copies = $area_printer->copies;
            }
        }

        switch ($document_type) {
            case "0":
                $documentLink = url('') . "/restaurant/worker/print-ticket/{$document_id}";
                break;
            case "01":
                $doc = Document::where('id', $document_id)->first();
                $documentLink = url('') . "/print/document/{$doc->external_id}/ticket";
                break;
            case "03":
                $doc = Document::where('id', $document_id)->first();
                $documentLink = url('') . "/print/document/{$doc->external_id}/ticket";
                break;
            case "80":
                $doc = SaleNote::where('id', $document_id)->first();
                $documentLink = url('') . "/sale-notes/print/{$doc->external_id}/ticket";
                break;
        }
        return array(
            'printer' => $printerName,
            'printing' => $printing,
            'copies' => $copies,

            'direct_printing' => (bool) $configuration->print_direct,
            'documentlink'   => $documentLink,
            'multiple_boxes' => (bool) $configuration->multiple_boxes,
            'typeuser' => $user->type,
            'user_id' => $user->id,
            'area_id' => $area_id
        );
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
    public function releaseTable($table_id)
    {
        $orden = Orden::where('table_id', $table_id)->where('status_orden_id', 1)->latest()->first();
        if ($orden) {
            $orden->status_orden_id = 5;
            OrdenItem::where('orden_id', $orden->id)->update(['status_orden_id' => 5]);
            $orden->save();
        }
        $table = Table::find($table_id);
        $table->status_table_id = 1;
        $table->save();
        return [
            'success' => true,
            'message' => 'Mesa liberada exitosamente'
        ];
    }
    public function store(Request $request)
    {
        try {

            $id = $request->ordenId;
            $user = User::where('id', auth()->user()->id)->first();
            $orden = Orden::firstOrNew(['id' => $id]);
            $orden->fill($request->all());
            if (!$orden->id) {
                $orden->status_orden_id = 1;
            }
            if ($request->to_carry == 1) {
                $orden->to_carry = 1;
                $orden->reference = $request->reference;
                $orden->table_id = null;
            } else if (isset($request->tableId)) {
                $table = Table::find($request->tableId);
                $table->status_table_id = 2;
                $orden->table_id = $table->id;
                $table->save();
            }
            $orden->save();
            $items = $request->items;
            $user_id = $user->id;
            $message = 'Pedido realizado.';



            $orden->save();
            $batch_number = OrdenItem::where('orden_id', $orden->id)->max('batch_number') + 1;
            foreach ($items as $item) {
                $orden_item = new OrdenItem;
                $orden_item->batch_number = $batch_number;
                $orden_item->item_id = $item['id'];
                $orden_item->observations = $item['observation'] ?? '-';
                $orden_item->quantity = $item['quantity'];
                $orden_item->price = $item['price'];
                $orden_item->user_id = $user_id;
                $orden_item->orden_id = $orden->id;
                $orden_item->status_orden_id = 1;
                $orden_item->date = $request->date_opencash == null ? date('Y-m-d') : $request->date_opencash;
                $orden_item->time = date('H:i:s');
                $orden_item->area_id = isset($item['area_id']) ? $item['area_id'] : null;
                $orden_item->save();
            }


            return [
                'success' => true,


                'message' => $message,
                'ordenId' => $orden->id,

                // 'print'   => url('') . "/restaurant/worker/print-ticket/{$id}"
            ];
            /* ----------------------------- */
        } catch (Exception $e) {

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

    // Listar órdenes para llevar pendientes
    public function takeAwayOrders()
    {
        $orders = Orden::where('to_carry', 1)
            ->where('status_orden_id', 1)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'reference', 'created_at'])->transform(function ($orden) {
                return [
                    'id' => $orden->id,
                    'reference' => $orden->reference,
                    'created_at' => Carbon::parse($orden->created_at)->format('d-m-Y H:i:s')
                ];
            });
        return [
            'success' => true,
            'data' => $orders
        ];
    }
    public static function getFullDescriptionToSaleNote(Item $item, Warehouse $warehouse = null)
    {

        $desc = ($item->internal_id) ? $item->internal_id . ' - ' . $item->description : $item->description;
        $category = ($item->category) ? "{$item->category->name}" : "";
        $brand = ($item->brand) ? "{$item->brand->name}" : "";

        if ($item->unit_type_id != 'ZZ') {
            $warehouse_stock = ($item->warehouses && $warehouse) ? number_format($item->warehouses->where('warehouse_id', $warehouse->id)->first() != null ? $item->warehouses->where('warehouse_id', $warehouse->id)->first()->stock : 0, 2) : 0;
            $stock = ($item->warehouses && $warehouse) ? "{$warehouse_stock}" : "";
        } else {
            $stock = '';
        }


        $desc = "{$desc} - {$brand}";

        return [
            'full_description' => $desc,
            'brand' => $brand,
            'category' => $category,
            'stock' => $stock,
        ];
    }

    public function getOrderIdByTable($table_id)
    {
        $orden = Orden::where('table_id', $table_id)->where('status_orden_id', 1)->latest()->first();
        if ($orden) {
            return [
                'success' => true,
                'data' => $orden->id
            ];
        }
        return [
            'success' => false,
            'message' => 'No hay ordenes pendientes'
        ];
    }
    public function getOrdenByIdToPay($orden_id)
    {
        $new_request = new Request();
        $igv = (new StoreController)->getIgv($new_request);
        $configuration = Configuration::first();
        $orden = Orden::with('orden_items')->find($orden_id);
        if (!$orden) {
            return [
                'success' => false,
                'message' => 'Orden no encontrada'
            ];
        }
        $items = $orden->orden_items->map(function ($item) use ($configuration) {
            $row = $item->item;
            $detail = self::getFullDescriptionToSaleNote($row);
            $price = $item->price;
            $quantity = $item->quantity;
            $total = $price * $quantity;
            $affectation_igv_type_id = $row->sale_affectation_igv_type_id;
            $affectation_igv_type = AffectationIgvType::where('id', $affectation_igv_type_id)->first();
            return [
                "affectation_igv_type" => $affectation_igv_type,
                "affectation_igv_type_id" => $affectation_igv_type_id,
                "quantity" => $quantity,
                "item" => [
                    'id' => $row->id,
                    'full_description' => $detail['full_description'],
                    "affectation_igv_type" => $affectation_igv_type,
                    'brand' => $detail['brand'],
                    'category' => $detail['category'],
                    'stock' => $detail['stock'],
                    'description' => $row->description,
                    'calculate_quantity' => (bool)$row->calculate_quantity,
                    'currency_type_id' => $row->currency_type_id,
                    'currency_type_symbol' => $row->currency_type->symbol,
                    'sale_unit_price' => round($item->price, 2),
                    'unit_price' => round($item->price, 2),
                    'quantity' => $item->quantity,
                    'observation' => $item->observation,
                    'purchase_unit_price' => number_format($row->purchase_unit_price, $configuration->decimal_quantity, ".", ""),
                    'unit_type_id' => $row->unit_type_id,
                    'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                    'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                    'has_igv' => (bool)$row->has_igv,
                    'lots_enabled' => (bool)$row->lots_enabled,
                    'series_enabled' => (bool)$row->series_enabled,
                    'is_set' => (bool)$row->is_set,
                    'item_unit_types' => $row->item_unit_types,
                    'lot_code' => $row->lot_code,
                    'restrict_sale_cpe' => $row->restrict_sale_cpe,
                    'date_of_due' => $row->date_of_due,
                    'payment_conditions' => $row->payment_conditions,
                    'total' => number_format($total, 2, ".", ""),
                ]
            ];
        });
        return [
            'success' => true,
            'data' => [
                'id' => $orden->id,
                'reference' => $orden->reference,
                'items' => $items
            ],
        ];
    }
    // Obtener una orden por su ID (para llevar)
    public function getOrdenById($orden_id)
    {
        $orden = Orden::with('orden_items')->find($orden_id);
        if (!$orden) {
            return [
                'success' => false,
                'message' => 'Orden no encontrada'
            ];
        }
        $batch_numbers = $orden->orden_items->pluck('batch_number')->unique()->toArray();
        $items = $orden->orden_items->map(function ($item) {
            return [
                'id' => $item->id,
                'item_id' => $item->item_id,
                'description' => $item->item->description,
                'status_orden_id' => $item->status_orden_id,
                'sale_affectation_igv_type_id' => $item->item->sale_affectation_igv_type_id,
                'has_igv' => $item->item->has_igv,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'observation' => $item->observation,
            ];
        });
        return [
            'success' => true,
            'data' => [
                'id' => $orden->id,
                'reference' => $orden->reference,
                'items' => $items
            ],
            'batch_numbers' => $batch_numbers
        ];
    }
}
