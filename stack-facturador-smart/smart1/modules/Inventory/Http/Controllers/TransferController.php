<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SearchItemController;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Dispatch;
use App\Models\Tenant\DispatchItem;
use App\Models\Tenant\Item;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\PurchaseItem;
use App\Models\Tenant\Series;
use Barryvdh\DomPDF\Facade\Pdf;
use Mpdf\Mpdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\Exports\InventoryTransferExport;
use Modules\Inventory\Http\Resources\TransferCollection;
use Modules\Inventory\Http\Resources\TransferResource;
use Modules\Inventory\Traits\InventoryTrait;
use Modules\Inventory\Models\Inventory;
use Modules\Inventory\Models\ItemWarehouse;
use Modules\Inventory\Models\InventoryTransferItem;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Models\InventoryTransfer;
use Modules\Inventory\Http\Requests\InventoryRequest;
use Modules\Inventory\Http\Requests\TransferRequest;

use Modules\Item\Models\ItemLot;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Excel;
use Modules\Inventory\Imports\TransferImport;
use Modules\Inventory\Models\InventoryConfiguration;
use Modules\Inventory\Models\InventoryTransferToAccept;
use Modules\Item\Models\ItemLotsGroup;
use Modules\Item\Models\ItemProperty;
use Modules\Item\Models\ItemPropertyInventory;

class TransferController extends Controller
{
    use InventoryTrait;

    public function index()
    {
        $inventory_configuration = InventoryConfiguration::first();
        return view('inventory::transfers.index', compact('inventory_configuration'));
    }

    public function create()
    {
        // $establishment_id = auth()->user()->establishment_id;
        //$current_warehouse = Warehouse::where('establishment_id', $establishment_id)->first();
        return view('inventory::transfers.form');
    }
    public function import(Request $request)
    {
        if ($request->hasFile('file')) {
            try {
                $import = new TransferImport();
                $import->import($request->file('file'), null, Excel::XLSX);
                $data = $import->getData();
                return [
                    'success' => true,
                    'message' => __('app.actions.upload.success'),
                    'data' => $data
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }
        return [
            'success' => false,
            'message' => __('app.actions.upload.error'),
        ];
    }

    public function columns()
    {
        return [
            'series' => 'Serie',
            'number' => 'Número',
            'created_at' => 'Fecha de emisión',
        ];
    }

    /**
     * Aplica filtros comunes a las consultas de transferencias
     */
    private function applyFilters($query, Request $request)
    {
        if ($request->column && $request->value) {
            if ($request->column == 'created_at') {
                $query = $query->where('created_at', 'like', "%{$request->value}%");
            } else {
                $query = $query->where("{$request->column}", 'like', "%{$request->value}%");
            }
        }

        if ($request->warehouse_id) {
            $query = $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->warehouse_destination_id) {
            $query = $query->where('warehouse_destination_id', $request->warehouse_destination_id);
        }

        if ($request->state) {
            $query = $query->where('state', $request->state);
        }

        if ($request->user_id) {
            $query = $query->where('user_id', $request->user_id);
        }

        return $query;
    }

    /**
     * It returns a collection of records from the database
     *
     * @param Request request
     *
     * @return A collection of records.
     */
    public function records(Request $request)
    {
        // Cargar solo las relaciones necesarias
        $records = InventoryTransfer::with([
            'warehouse:id,description',
            'warehouse_destination:id,description',
            'user:id,name',
            'user_to_accept:id,name'
        ]);

        $records = $this->applyFilters($records, $request);
        $records = $records->orderBy('id', 'desc');

        return new TransferCollection($records->paginate(config('tenant.items_per_page')));
    }



    /**
     * Exporta transferencias a PDF con optimizaciones de rendimiento
     * 
     * Optimizaciones implementadas:
     * - Eager loading selectivo de relaciones
     * - Eliminación del problema N+1 query
     * - Limitación de registros para evitar PDFs muy grandes
     * - Consultas optimizadas con select específico
     * 
     * Recomendaciones adicionales para mejorar rendimiento:
     * - Agregar índices en la base de datos: warehouse_id, warehouse_destination_id, user_id, created_at
     * - Considerar paginación para grandes volúmenes de datos
     * - Implementar cache para consultas frecuentes
     */
    public function exportPdf(Request $request)
    {
        // Cargar solo las relaciones necesarias para el PDF
        $records = InventoryTransfer::with([
            'warehouse:id,description',
            'warehouse_destination:id,description',
            'user:id,name',
            'user_to_accept:id,name'
        ]);

        $records = $this->applyFilters($records, $request);

        // Limitar el número de registros para evitar PDFs muy grandes
        $maxRecords = 1000; // Configurable según necesidades
        $records = $records->limit($maxRecords);

        // Obtener el warehouse del usuario una sola vez
        $user = auth()->user();
        $user_warehouse = Warehouse::select('id')
            ->where('establishment_id', $user->establishment_id)
            ->first();

        // Procesar los datos de manera más eficiente
        $data = $records->get()->map(function ($transfer) use ($user, $user_warehouse) {
            $warehouse_id = $user_warehouse ? $user_warehouse->id : null;
            $can_confirm = $warehouse_id === $transfer->warehouse_destination_id;
            $can_confirm = $transfer->user_accept_id ? ($transfer->user_accept_id === $user->id) : $can_confirm;
            $user_to_accept = $transfer->user_to_accept ? $transfer->user_to_accept->name : null;
            $description = $user_to_accept ? "{$transfer->description} - Para ser aceptado por {$user_to_accept}" : $transfer->description;

            return [
                'can_confirm' => $can_confirm,
                'state' => $transfer->state,
                'series' => $transfer->series,
                'number' => $transfer->number,
                'id' => $transfer->id,
                'user_name' => $transfer->user->name,
                'description' => $description,
                'quantity' => round($transfer->quantity, 1),
                'warehouse' => $transfer->warehouse->description,
                'warehouse_destination' => $transfer->warehouse_destination->description,
                'created_at' => $transfer->created_at->format('Y-m-d H:i:s'),
            ];
        });

        // Obtener la empresa una sola vez
        $company = Company::select('name', 'number')->first();

        $pdf = PDF::loadView('inventory::transfers.export.export_pdf', compact('data', 'company'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream('transfers.pdf');
    }
    public function tables()
    {
        return [
            'inventory_configuration' => InventoryConfiguration::first(),
            //'items' => $this->optionsItemWareHouse(),
            'warehouses' => $this->optionsWarehouse(),
            'users' => $this->optionsUser()
        ];
    }

    public function record($id)
    {
        $record = new TransferResource(Inventory::findOrFail($id));

        return $record;
    }




    /* public function store(Request $request)
     {

         $result =  DB::connection('tenant')->transaction(function () use ($request) {

             $id = $request->input('id');
             $item_id = $request->input('item_id');
             $warehouse_id = $request->input('warehouse_id');
             $warehouse_destination_id = $request->input('warehouse_destination_id');
             $stock = $request->input('stock');
             $quantity = $request->input('quantity');
             $detail = $request->input('detail');

             if($warehouse_id === $warehouse_destination_id) {
                 return  [
                     'success' => false,
                     'message' => 'El almacén destino no puede ser igual al de origen'
                 ];
             }
             if($stock < $quantity) {
                 return  [
                     'success' => false,
                     'message' => 'La cantidad a trasladar no puede ser mayor al que se tiene en el almacén.'
                 ];
             }

             $re_it_warehouse = ItemWarehouse::where([['item_id',$item_id],['warehouse_id', $warehouse_destination_id]])->first();

             if(!$re_it_warehouse) {
                 return  [
                     'success' => false,
                     'message' => 'El producto no se encuentra registrado en el almacén destino.'
                 ];
             }


             $inventory = Inventory::findOrFail($id);

             //proccess stock
             $origin_inv_kardex = $inventory->inventory_kardex->first();
             $origin_item_warehouse = ItemWarehouse::where([['item_id',$origin_inv_kardex->item_id],['warehouse_id', $origin_inv_kardex->warehouse_id]])->first();
             $origin_item_warehouse->stock += $inventory->quantity;
             $origin_item_warehouse->stock -= $quantity;
             $origin_item_warehouse->update();


             $destination_inv_kardex = $inventory->inventory_kardex->last();
             $destination_item_warehouse = ItemWarehouse::where([['item_id',$destination_inv_kardex->item_id],['warehouse_id', $destination_inv_kardex->warehouse_id]])->first();
             $destination_item_warehouse->stock -= $inventory->quantity;
             $destination_item_warehouse->update();


             $new_item_warehouse = ItemWarehouse::where([['item_id',$item_id],['warehouse_id', $warehouse_destination_id]])->first();
             $new_item_warehouse->stock += $quantity;
             $new_item_warehouse->update();

             //proccess stock

             //proccess kardex
             $origin_inv_kardex->quantity = -$quantity;
             $origin_inv_kardex->update();

             $destination_inv_kardex->quantity = $quantity;
             $destination_inv_kardex->warehouse_id = $warehouse_destination_id;
             $destination_inv_kardex->update();
             //proccess kardex

             $inventory->warehouse_destination_id = $warehouse_destination_id;
             $inventory->quantity = $quantity;
             $inventory->detail = $detail;


             $inventory->update();

             return  [
                 'success' => true,
                 'message' => 'Traslado actualizado con éxito'
             ];
         });

         return $result;
     }*/


    public function destroy($id)
    {

        DB::connection('tenant')->transaction(function () use ($id) {

            $record = Inventory::findOrFail($id);

            $origin_inv_kardex = $record->inventory_kardex->first();
            $destination_inv_kardex = $record->inventory_kardex->last();

            $destination_item_warehouse = ItemWarehouse::where([['item_id', $destination_inv_kardex->item_id], ['warehouse_id', $destination_inv_kardex->warehouse_id]])->first();
            $destination_item_warehouse->stock -= $record->quantity;
            $destination_item_warehouse->update();

            $origin_item_warehouse = ItemWarehouse::where([['item_id', $origin_inv_kardex->item_id], ['warehouse_id', $origin_inv_kardex->warehouse_id]])->first();
            $origin_item_warehouse->stock += $record->quantity;
            $origin_item_warehouse->update();

            $record->inventory_kardex()->delete();
            $record->delete();
        });


        return [
            'success' => true,
            'message' => 'Traslado eliminado con éxito'
        ];
    }

    public function stock($item_id, $warehouse_id)
    {

        $row = ItemWarehouse::where([['item_id', $item_id], ['warehouse_id', $warehouse_id]])->first();

        return [
            'stock' => ($row) ? $row->stock : 0
        ];
    }


    /**
     *
     * Validar si existe la serie
     *
     * @param  Series $series
     * @param  string $document_type_id
     * @return void
     */
    public function checkIfExistSerie($series, $document_type_id)
    {
        if (is_null($series)) {
            $document_type_description = $this->generalGetDocumentTypeDescription($document_type_id);

            throw new Exception("No se encontró una serie para el tipo de documento {$document_type_id} - {$document_type_description}, registre la serie en Establecimientos/Series");
        }
    }
    public function acceptTransfer($id)
    {
        DB::connection('tenant')->beginTransaction();
        try {
            $this->restoreStockTransfer($id);
            $transfer_to_accept = InventoryTransferToAccept::where('inventory_transfer_id', $id)->get();
            $inventory_transfer = InventoryTransfer::findOrFail($id);
            $warehouse_id = $inventory_transfer->warehouse_id;
            $warehouse_destination_id = $inventory_transfer->warehouse_destination_id;
            foreach ($transfer_to_accept as $it) {
                $inventory = new Inventory();
                $inventory->type = 2;
                $inventory->description = 'Traslado';
                if (isset($it->attributes)) {
                    foreach ($it->attributes as $row) {
                        $itemattributes = ItemProperty::find($row['id']);
                        $itemattributes->warehouse_id = $warehouse_destination_id;
                        $itemattributes->has_sale = false;
                        $itemattributes->save();
                    }
                }
                $inventory->item_id = $it->item_id;
                $inventory->warehouse_id = $warehouse_id;
                $inventory->warehouse_destination_id = $warehouse_destination_id;
                $inventory->quantity = $it->quantity;
                $inventory->inventories_transfer_id = $id;
                $inventory->save();
                $series_lots = $it->series_lots;

                if (isset($series_lots['lots'])) {
                    foreach ($series_lots['lots'] as $lot) {
                        if ($lot['has_sale']) {
                            $item_lot = ItemLot::findOrFail($lot['id']);
                            $item_lot->warehouse_id = $inventory->warehouse_destination_id;
                            $item_lot->update();

                            // historico de item para traslado
                            InventoryTransferItem::query()->create([
                                'inventory_transfer_id' => $id,
                                'item_lot_id' => $lot['id'],
                            ]);
                        }
                    }
                }

                if (isset($series_lots['lot_groups'])) {
                    foreach ($series_lots['lot_groups'] as $lot) {
                        InventoryTransferItem::query()->create([
                            'inventory_transfer_id' => $id,
                            'item_lots_group_id' => $lot['id'],
                        ]);
                    }
                }
            }

            $inventory_transfer->state = 2;
            $inventory_transfer->update();
            InventoryTransferToAccept::where('inventory_transfer_id', $id)->delete();
            DB::connection('tenant')->commit();
            return [
                'success' => true,
                'message' => 'Traslado aceptado con éxito'
            ];
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    public function rejectTransfer($id)
    {
        // $transfer_to_accept = InventoryTransferToAccept::where('inventory_transfer_id', $id)->get();
        $this->restoreStockTransfer($id);
        InventoryTransferToAccept::where('inventory_transfer_id', $id)->delete();
        $inventory_transfer = InventoryTransfer::findOrFail($id);
        $inventory_transfer->state = 3;
        $inventory_transfer->update();
        return [
            'success' => true,
            'message' => 'Traslado rechazado con éxito'
        ];
    }

    function restoreStockTransfer($id)
    {
        $transfers_to_accept = InventoryTransferToAccept::where('inventory_transfer_id', $id)->get();
        foreach ($transfers_to_accept as $transfer_to_accept) {
            $item_id = $transfer_to_accept->item_id;
            $series_lots = $transfer_to_accept->series_lots;
            $item = Item::findOrFail($item_id);
            $item->stock += $transfer_to_accept->quantity;
            $item->update();
            $item_warehouse = ItemWarehouse::where([['item_id', $item_id], ['warehouse_id', $transfer_to_accept->inventory_transfer->warehouse_id]])->first();
            if (!$item_warehouse) {
                $item_warehouse = ItemWarehouse::create([
                    'item_id' => $item_id,
                    'warehouse_id' => $transfer_to_accept->inventory_transfer->warehouse_id,
                    'stock' => 0,
                ]);
            }
            $stock_absolute = 0;
            if ($item_warehouse->stock < 0) {
                $stock_absolute = abs($item_warehouse->stock);
            }
            $item_warehouse->stock += $transfer_to_accept->quantity + $stock_absolute;
            $item_warehouse->update();

            if (isset($series_lots['lots'])) {
                foreach ($series_lots['lots'] as $lot) {
                    $item_lot = ItemLot::findOrFail($lot['id']);
                    $item_lot->has_sale = 0;
                    $item_lot->update();
                }
            }

            if (isset($series_lots['lot_groups'])) {
                foreach ($series_lots['lot_groups'] as $lot_group) {
                    $item_lots_group = ItemLotsGroup::findOrFail($lot_group['id']);
                    $item_lots_group->quantity += $lot_group['compromise_quantity'];
                    $item_lots_group->update();
                }
            }
        }
    }
    public function storeToAccept(Request $request)
    {
        DB::connection('tenant')->beginTransaction();
        try {
            $document_type_id = 'U4';
            $warehouse_id = $request->input('warehouse_id');

            $warehouse = Warehouse::query()
                ->select('id', 'establishment_id')
                ->where('id', $warehouse_id)
                ->first();

            $series = Series::query()
                ->select('number')
                ->where('establishment_id', $warehouse->establishment_id)
                ->where('document_type_id', 'U4')
                ->first();

            $this->checkIfExistSerie($series, $document_type_id);

            $row = InventoryTransfer::query()
                ->create([
                    'description' => $request->description,
                    'warehouse_id' => $request->warehouse_id,
                    'dispatch_id' => $request->dispatch_id,
                    'purchase_id' => $request->purchase_id,
                    'warehouse_destination_id' => $request->warehouse_destination_id,
                    'quantity' => count($request->items),
                    'document_type_id' => $document_type_id,
                    'series' => $series->number,
                    'number' => '#',
                    'state' => 1,
                    'user_accept_id' => $request->user_accept_id,
                ]);

            foreach ($request->items as $it) {
                $inventoryToAccept = new InventoryTransferToAccept();
                $inventoryToAccept->inventory_transfer_id = $row->id;
                $inventoryToAccept->item_id = $it['id'];
                $inventoryToAccept->quantity = $it['quantity'];
                $inventoryToAccept->attributes = isset($it['idAttributeSelect']) ? $it['idAttributeSelect'] : null;
                $series_lots = ['lots' =>  $it['lots']];
                foreach ($it['lots'] as $lot) {
                    $item_lot = ItemLot::findOrFail($lot['id']);
                    $item_lot->has_sale = 1;
                    $item_lot->update();
                }
                $lot_groups = $this->searchLotGroup($request->lot_groups_total, $it['id']);
                if ($lot_groups) {
                    $series_lots['lot_groups'] = $lot_groups;
                }
                $inventoryToAccept->series_lots = json_encode($series_lots);
                $inventoryToAccept->save();
                if (isset($it['idAttributeSelect'])) {
                    foreach ($it['idAttributeSelect'] as $value) {
                        $this->restStock($it['id'], $warehouse_id, $it['quantity'], $value['id']);
                    }
                } else {
                    $this->restStock($it['id'], $warehouse_id, $it['quantity']);
                }
            }


            DB::connection('tenant')->commit();

            return [
                'success' => true,
                'message' => 'Traslado por aceptar creado con éxito'
            ];
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();

            return [
                'success' => false,
                'stack' => $e->getTrace(),
                'message' => $e->getMessage()
            ];
        }
    }

    function searchLotGroup($lot_groups, $item_id)
    {
        $lot_group_result = [];
        foreach ($lot_groups as $lot_group) {
            $lote_group_id = $lot_group['id'];
            $item_lots_group = ItemLotsGroup::findOrFail($lote_group_id);
            $lot_group_item_id = $item_lots_group->item_id;
            if ($lot_group_item_id == $item_id) {
                $quantity = $lot_group['compromise_quantity'];
                $item_lots_group->quantity -= $quantity;
                $item_lots_group->update();
                $lot_group_result[] = $lot_group;
            }
        }
        if (count($lot_group_result) == 0) {
            return null;
        }
        return  $lot_group_result;
    }
    function restStock($item_id, $warehouse_id, $quantity, $idAttributeSelect_id = null)
    {
        $row = ItemWarehouse::where([['item_id', $item_id], ['warehouse_id', $warehouse_id]])->first();
        $row->stock -= $quantity;
        $row->update();
        $item = Item::findOrFail($item_id);
        $item->stock -= $quantity;
        $item->update();
        if ($idAttributeSelect_id) {
            $configuration = Configuration::getConfig();
            if ($configuration->view_attributes == true && $idAttributeSelect_id) {
                ItemProperty::where('id', $idAttributeSelect_id)->update([
                    'warehouse_id' => $warehouse_id
                ]);
            }
        }
    }
    public function storeMassivePurchase(Request $request)
    {
        $process = [];
        $errors = [];
        $data = [];
        $transfer = $request->all();

        $purchase_id = $transfer['purchase_id'];
        $warehouse_id = $transfer['warehouse_id'];
        $warehouse_destination_id = $transfer['warehouse_destination_id'];
        $description = $transfer['description'];
        $user_id = $transfer['user_id'];

        $purchase = Purchase::select('id',   'number', 'series')->where('id', $purchase_id)->first();
        $number_full = $purchase->series . '-' . $purchase->number;

        $items = PurchaseItem::where('purchase_id', $purchase_id)
            ->select('item_id', 'quantity')
            ->get()
            ->transform(function ($item) {
                return [
                    'id' => $item->item_id,
                    'quantity' => $item->quantity,
                    'lots' => [],
                ];
            });

        // Crear nueva instancia de Request para cada transferencia
        if ($user_id) {
            $transferRequest = new Request();
        } else {
            $transferRequest = new TransferRequest();
        }

        $transferRequest->merge([
            'purchase_id' => $purchase_id,
            'items' => $items->toArray(), // Convertir a array para evitar problemas
            'warehouse_id' => $warehouse_id,
            'warehouse_destination_id' => $warehouse_destination_id,
            'description' => $description,
            'user_accept_id' => $user_id,
            'lot_groups_total' => []
        ]);

        $response = null;
        if ($user_id) {
            $response = $this->storeToAccept($transferRequest);
        } else {
            $response = $this->store($transferRequest);
        }

        if ($response['success']) {
            $process[] = $number_full;
        } else {
            $message = $response['message'];
            $stack = isset($response['stack']) ? $response['stack'] : [];
            $errors[] = [
                'number' => $number_full,
                'message' => $message,
                'stack' => $stack
            ];
        }

        return [
            'success' => true,
            'message' => 'Guías transferidas correctamente',
            'data' => $data,
            'process' => $process,
            'errors' => $errors
        ];
    }
    public function storeMassive(Request $request)
    {
        $transfers = $request->transfers;
        $process = [];
        $errors = [];
        $data = [];

        foreach ($transfers as $transfer) {
            $dispatch_id = $transfer['id'];
            $warehouse_id = $transfer['warehouse_id'];
            $warehouse_destination_id = $transfer['warehouse_destination_id'];
            $description = $transfer['description'];
            $user_id = $transfer['user_id'];

            $dispatch = Dispatch::select('id', 'number', 'series')->where('id', $dispatch_id)->first();
            $number_full = $dispatch->series . '-' . $dispatch->number;

            // Obtener items únicos del dispatch para evitar duplicaciones
            $items = DispatchItem::where('dispatch_id', $dispatch_id)
                ->select('item_id', 'quantity')
                ->get()
                ->transform(function ($item) {
                    return [
                        'id' => $item->item_id,
                        'quantity' => $item->quantity,
                        'lots' => [],
                    ];
                });

            // Crear nueva instancia de Request para cada transferencia
            if ($user_id) {
                $transferRequest = new Request();
            } else {
                $transferRequest = new TransferRequest();
            }

            $transferRequest->merge([
                'dispatch_id' => $dispatch_id,
                'items' => $items->toArray(), // Convertir a array para evitar problemas
                'warehouse_id' => $warehouse_id,
                'warehouse_destination_id' => $warehouse_destination_id,
                'description' => $description,
                'user_accept_id' => $user_id,
                'lot_groups_total' => []
            ]);

            $response = null;
            if ($user_id) {
                $response = $this->storeToAccept($transferRequest);
            } else {
                $response = $this->store($transferRequest);
            }

            if ($response['success']) {
                $process[] = $number_full;
            } else {
                $message = $response['message'];
                $stack = isset($response['stack']) ? $response['stack'] : [];
                $errors[] = [
                    'number' => $number_full,
                    'message' => $message,
                    'stack' => $stack
                ];
            }
        }

        return [
            'success' => true,
            'message' => 'Guías transferidas correctamente',
            'data' => $data,
            'process' => $process,
            'errors' => $errors
        ];
    }

    public function store(TransferRequest $request)
    {
        DB::connection('tenant')->beginTransaction();
        try {
            $document_type_id = 'U4';
            $warehouse_id = $request->input('warehouse_id');

            $warehouse = Warehouse::query()
                ->select('id', 'establishment_id')
                ->where('id', $warehouse_id)
                ->first();

            $series = Series::query()
                ->select('number')
                ->where('establishment_id', $warehouse->establishment_id)
                ->where('document_type_id', 'U4')
                ->first();

            $this->checkIfExistSerie($series, $document_type_id);
            $warehouse_destination_id = $request->warehouse_destination_id;
            $row = InventoryTransfer::query()
                ->create([
                    'description' => $request->description,
                    'warehouse_id' => $request->warehouse_id,
                    'warehouse_destination_id' => $request->warehouse_destination_id,
                    'quantity' => count($request->items),
                    'document_type_id' => $document_type_id,
                    'series' => $series->number,
                    'number' => '#',
                    'dispatch_id' => $request->dispatch_id,
                ]);

            foreach ($request->items as $it) {
                $inventory = new Inventory();
                $inventory->type = 2;
                $inventory->description = 'Traslado';
                $inventory->item_id = $it['id'];
                $inventory->warehouse_id = $request->warehouse_id;
                $inventory->warehouse_destination_id = $request->warehouse_destination_id;
                $inventory->quantity = $it['quantity'];
                $inventory->inventories_transfer_id = $row->id;
                $inventory->save();
                if (isset($it['idAttributeSelect'])) {

                    foreach ($it['idAttributeSelect'] as $row) {
                        $itemattributes = ItemProperty::find($row['id']);
                        // $itemattributes->has_sale = true;
                        $itemattributes->warehouse_id = $inventory->warehouse_destination_id;
                        $itemattributes->save();

                        ItemPropertyInventory::query()->create([
                            'item_property_id' => $row['id'],
                            'inventory_id' => $inventory->id,
                        ]);
                    }
                }

                foreach ($it['lots'] as $lot) {
                    if ($lot['has_sale']) {
                        $item_lot = ItemLot::findOrFail($lot['id']);
                        $item_lot->warehouse_id = $inventory->warehouse_destination_id;
                        $item_lot->update();

                        // historico de item para traslado
                        InventoryTransferItem::query()->create([
                            'inventory_transfer_id' => $row->id,
                            'item_lot_id' => $lot['id'],
                        ]);
                    }
                }
            }

            if ($request->lot_groups_total) {
                foreach ($request->lot_groups_total as $lot) {
                    $lot_group = ItemLotsGroup::find($lot['id']);
                    $code = $lot_group->code;
                    $item_id = $lot_group->item_id;
                    $lot_group_destination = ItemLotsGroup::where('code', $code)->where('item_id', $item_id)->where('warehouse_id', $warehouse_destination_id)->first();
                    if ($lot_group_destination) {
                        $lot_group_destination->quantity += $lot['compromise_quantity'];
                        $lot_group_destination->update();
                        $lot_group->quantity -= $lot['compromise_quantity'];
                        $lot_group->update();
                    } else {
                        $lot_group_destination = new ItemLotsGroup();
                        $lot_group_destination->code = $code;
                        $lot_group_destination->item_id = $item_id;
                        $lot_group_destination->date_of_due = $lot_group->date_of_due;
                        $lot_group_destination->warehouse_id = $warehouse_destination_id;
                        $lot_group_destination->quantity = $lot['compromise_quantity'];
                        $lot_group_destination->save();
                        $lot_group->quantity -= $lot['compromise_quantity'];
                        $lot_group->update();
                    }


                    InventoryTransferItem::query()->create([
                        'inventory_transfer_id' => $row->id,
                        'item_lots_group_id' => $lot['id'],
                    ]);
                }
            }

            DB::connection('tenant')->commit();

            return [
                'success' => true,
                'message' => 'Traslado creado con éxito'
            ];
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }


    public function searchItems(Request $request)
    {
        $items = SearchItemController::getItemToTrasferWithSearch($request);
        return compact('items');
    }

    public function items($warehouse_id)
    {
        return ['items' => SearchItemController::getItemToTrasferWithoutSearch($warehouse_id)];
        return [
            'items' => $this->optionsItemWareHousexId($warehouse_id),
        ];
    }


    /**
     * No se implementa
     *
     * @param \Modules\Inventory\Models\InventoryTransfer $inventoryTransfer
     *
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function excel(InventoryTransfer $inventoryTransfer)
    {
        return null;
        $export = new InventoryTransferExport();
        $export->setInventory($inventoryTransfer);
        return $export->download('Reporte_Traslado_' . $inventoryTransfer->id . '_' . date('YmdHis') . '.xlsx');
    }

    public function getInventoryTransferData(InventoryTransfer $inventoryTransfer)
    {
        return null;
        // return $this->excel(($inventoryTransfer));
        $data = $inventoryTransfer->getPdfData();
        $pdf = PDF::loadView('inventory::transfers.export.pdf', compact('data'));
        $pdf->setPaper('A4', 'landscape');
        $filename = 'Reporte_Traslado_' . $inventoryTransfer->id . '_' . date('YmdHis');
        return $pdf->download($filename . '.pdf');
    }


    /**
     * Genera un pdf para nota de traslado
     *
     * @param \Modules\Inventory\Models\InventoryTransfer $inventoryTransfer
     *
     * @return \Illuminate\Http\Response
     */
    public function getPdf(InventoryTransfer $inventoryTransfer): \Illuminate\Http\Response
    {
        $data = $inventoryTransfer->getPdfData();
        $pdf = PDF::loadView('inventory::transfers.export.pdf', compact('data'));
        $pdf->setPaper('A4', 'portrait');
        $filename = 'Reporte_Traslado_' . $inventoryTransfer->id . '_' . date('YmdHis');
        return $pdf->stream($filename . '.pdf');
    }

    /**
     * Genera un ticket/comprobante de transferencia en formato PDF
     *
     * @param \Modules\Inventory\Models\InventoryTransfer $inventoryTransfer
     *
     * @return \Illuminate\Http\Response
     */
    public function getTicket(InventoryTransfer $inventoryTransfer): \Illuminate\Http\Response
    {
        $data = $inventoryTransfer->getPdfData();
        $base_height = 120;
        $inventories = !empty($data['inventories']) ? $data['inventories'] : new Collection();
        $base_height = $base_height + $inventories->count() * 20;

        // Tamaño de papel para ticket térmico de 80mm de ancho
        // 80mm = 226.77 puntos (1mm = 2.834645669 puntos)

        $pdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => [76, $base_height],
            'margin_top' => 0,
            'margin_right' => 0,
            'margin_bottom' => 0,
            'margin_left' => 0,
        ]);
        $pdf->shrink_tables_to_fit = 1;
        $pdf->WriteHTML(view('inventory::transfers.export.ticket', compact('data'))->render());


        $filename = 'Ticket_Traslado_' . $inventoryTransfer->series . '_' . $inventoryTransfer->number . '_' . date('YmdHis');
        return response($pdf->Output($filename . '.pdf', 'I'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="' . $filename . '.pdf"');
    }
}
