<?php

namespace Modules\Inventory\Http\Controllers;

use App\CoreFacturalo\Requests\Inputs\Functions;
use App\Models\Tenant\Item;
use App\Models\Tenant\Series;
// use Barryvdh\DomPDF\PDF;
use Exception;
use Barryvdh\DomPDF\Facade\Pdf;
//use App\Models\Tenant\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Models\Guide;
use Modules\Inventory\Models\InventoryTransfer;
use Modules\Item\Models\ItemLot;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SearchItemController;
use Modules\Item\Models\ItemLotsGroup;
use Modules\Inventory\Models\Inventory;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Models\ItemWarehouse;
use Modules\Inventory\Traits\InventoryTrait;
use Modules\Inventory\Models\InventoryKardex;
use Modules\Inventory\Models\InventoryTransaction;
use Modules\Inventory\Http\Requests\InventoryRequest;
use Modules\Inventory\Http\Resources\InventoryResource;
use Modules\Inventory\Http\Resources\InventoryCollection;
use App\Imports\StockImport;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use Modules\Item\Models\ItemProperty;
use App\Models\Tenant\ItemSizeStock;
use Maatwebsite\Excel\Excel;
use Modules\Inventory\Http\Requests\RemoveRequest;
use Modules\Inventory\Http\Resources\InventoryOtherViewCollection;
use Modules\Inventory\Models\InventoryTransferItem;
use Modules\Inventory\Models\ValidationWarehouse;
use Modules\Inventory\Models\ValidationWarehouseItem;
use Modules\Inventory\Models\InventoryConfiguration;

class InventoryController extends Controller
{
    use InventoryTrait;

    public function index()
    {

        $canAjustInventory = true;
        $configuration = Configuration::first();
        $seller_can_ajust_inventory = $configuration->seller_can_ajust_inventory;
        $inventory_configuration = InventoryConfiguration::first();
        $show_other_view_inventory = $inventory_configuration->show_other_view_inventory;
        $userIsSeller = auth()->user()->type == 'seller' || auth()->user()->type == 'admin_lower';
        if ($userIsSeller) {
            $canAjustInventory = $seller_can_ajust_inventory;
        }
        if ($show_other_view_inventory) {
            return view('inventory::inventory.index_other', compact('canAjustInventory'));
        }
        return view('inventory::inventory.index', compact('canAjustInventory'));
    }
    public function validateIndex()
    {
        return view('inventory::validate.index');
    }
    public function searchItemsInventory(Request $request)
    {
        $items = SearchItemController::getItemsToDocuments($request);

        return compact('items');
    }

    public function getValidations($warehouse_id)
    {

        $validations = ValidationWarehouse::where('warehouse_id', $warehouse_id)->get()
            ->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'date_of_validation' => $row->date_of_validation->format('Y-m-d'),
                    'user' => $row->user->name,
                    'observations' => $row->observations,
                ];
            });
        return compact('validations');
    }
    public function reportValidation($id)
    {
        $validate_warehouse = ValidationWarehouse::findOrFail($id);
        $items = $validate_warehouse->items;
        $date = $validate_warehouse->date_of_validation->format('Y-m-d');
        $user_name = $validate_warehouse->user->name;
        $warehouse_name = $validate_warehouse->warehouse->description;
        $observations = $validate_warehouse->observations;

        $pdf = PDF::loadView('inventory::validate.report', compact(
            "items",
            "date",
            "user_name",
            "warehouse_name",
            "observations"
        ));
        $filename = 'Validacion_inventario_' . date('YmdHis');

        return $pdf->stream($filename . '.pdf');
    }
    public function saveValidation(Request $request)
    {
        $validate_items = $request->validateItems;
        $observation = $request->observation;
        $warehouse_id = $request->warehouse_id;

        $validate_warehouse = new ValidationWarehouse;
        $validate_warehouse->warehouse_id = $warehouse_id;
        $validate_warehouse->user_id = auth()->user()->id;
        $validate_warehouse->date_of_validation = date('Y-m-d');
        $validate_warehouse->observations = $observation;

        $validate_warehouse->save();

        $validate_warehouse_id = $validate_warehouse->id;

        foreach ($validate_items as $item) {
            $validate_item = new ValidationWarehouseItem;
            $validate_item->validation_warehouse_id = $validate_warehouse_id;
            $validate_item->item_id = $item['id'];
            $validate_item->quantity = $item['quantity'];
            $validate_item->stock = $item['stock'];
            if (isset($item['lots'])) {
                $lots = $item['lots'];
                //filtra del array los elementos que tengan el indice "quantity" y que no sean 0
                $lots_count = array_filter($lots, function ($lot) {
                    return $lot['quantity'] > 0;
                });
                //filtra del array los elementos que tengan el indice "quantity" y que sean 0
                $lots_not_count = array_filter($lots, function ($lot) {
                    return $lot['quantity'] == 0;
                });
                $validate_item->lots = array_values($lots_count);
                $validate_item->lots_not_count = array_values($lots_not_count);
            }
            $validate_item->save();
        }
    }
    public function validateItems(Request $request)
    {
        // $seriesSelected = $request->input('seriesSelected');
        $itemsSelected = $request->input('itemsSelected');
        //extrae en un array los ids de los items seleccionados
        $idsSelected = array_map(function ($item) {
            return $item['id'];
        }, $itemsSelected);

        $warehouse_id = $request->input('warehouse_id');
        $itemsNotCount = [];

        ItemWarehouse::whereNotIn('item_id', $idsSelected)
            ->where('warehouse_id', $warehouse_id)
            ->where('stock', '>', 0)
            ->chunk(50, function ($items) use (&$itemsNotCount, $warehouse_id) {
                foreach ($items as $item) {
                    $item_id = $item->item_id;
                    $item_to_insert =  [
                        'id' => $item->item_id,
                        'full_description' => $item->item->internal_id . ' - ' . $item->item->description,
                        'stock' => $item->stock,
                        'quantity'  => 0,
                    ];
                    $lots = ItemLot::where('item_id', $item_id)
                        ->where('warehouse_id', $warehouse_id)
                        ->where('has_sale', false)
                        ->get()
                        ->transform(function ($row) {
                            return [
                                'quantity' => 0,
                                'series' => $row->series,

                            ];
                        })->toArray();

                    if (count($lots) > 0) {
                        $item_to_insert['lots'] = $lots;
                    }
                    $itemsNotCount[] = $item_to_insert;
                }
            });

        //filtrar los items que tengan el indice "lots" y que no esten vacios
        $seriesSelected = [];
        $itemSelected = [];
        foreach ($itemsSelected as $item) {
            if (isset($item['lots']) && count($item['lots']) > 0) {
                $seriesSelected[] = $item;
            } else {
                $itemSelected[] = $item;
            }
        }
        $items = [];
        foreach ($seriesSelected as $item_lot) {
            $lots = $item_lot['lots'];
            $lots = array_values($lots);
            $item_id = $item_lot['id'];
            $quantity = $item_lot['quantity'];
            $stock = $item_lot['stock'];
            $full_description = $item_lot['full_description'];
            $lots_not_count = ItemLot::where('item_id', $item_id)
                ->where('warehouse_id', $warehouse_id)
                ->where('has_sale', false)
                ->whereNotIn('series', $lots)
                ->get()
                ->transform(function ($row) {
                    return [
                        'quantity' => 0,
                        'series' => $row->series,
                    ];
                })->toArray();
            $lots_count = array_map(function ($row) {
                return [
                    'quantity' => 1,
                    'series' => $row,
                ];
            }, $lots);
            if (count($lots_not_count) > 0) {
                $items[] = [
                    'id' => $item_id,
                    'full_description' => $full_description,
                    'stock' => $stock,
                    'quantity' => $quantity,
                    'lots' => array_values(array_merge($lots_count, $lots_not_count)),
                ];
            }
        }
        $itemsNotCount = array_merge($itemSelected, $items, $itemsNotCount);
        // $itemsNotCount = array_merge($itemsNotCount, $itemSelected);

        return [
            'success' => true,
            'itemsNotCount' => $itemsNotCount,
            'message' => 'Inventario validados'
        ];
    }
    public function searchSeriesValidate(Request $request)
    {
        $input = $request->input('input');
        $warehouse_id = $request->input('warehouse_id');

        $items = Item::whereHas('item_lots', function ($query) use ($warehouse_id, $input) {
            $query->where('warehouse_id', $warehouse_id)
                ->where('series', $input);;
        })
            ->get()->transform(function ($row)  use ($warehouse_id) {
                $stock = ItemWarehouse::where('item_id', $row->id)
                    ->where('warehouse_id', $warehouse_id)
                    ->first()->stock;

                return [
                    'id' => $row->id,
                    'description' => $row->description,
                    'full_description' => $row->internal_id . ' - ' . $row->description,
                    'internal_id' => $row->internal_id,
                    'unit_type_id' => $row->unit_type_id,
                    'stock' => $stock,
                ];
            });

        return [
            'success' => true,
            'items' => $items
        ];
    }
    public function searchItemsValidate(Request $request)
    {
        $input = $request->input('input');
        $warehouse_id = $request->input('warehouse_id');

        $items = Item::whereDoesntHave('item_lots')->whereHas('warehouses', function ($query) use ($warehouse_id) {
            $query->where('warehouse_id', $warehouse_id);
        })
            ->where('description', 'like', "%{$input}%")
            ->orWhere('internal_id', 'like', "%{$input}%")
            ->get()->transform(function ($row) use ($warehouse_id) {
                $stock = ItemWarehouse::where('item_id', $row->id)
                    ->where('warehouse_id', $warehouse_id)
                    ->first()->stock;
                return [
                    'stock' => $stock,
                    'id' => $row->id,
                    'description' => $row->description,
                    'full_description' => $row->internal_id . ' - ' . $row->description,
                    'internal_id' => $row->internal_id,
                    'unit_type_id' => $row->unit_type_id,
                ];
            });

        return [
            'success' => true,
            'items' => $items
        ];
    }
    public function validateTables()
    {
        $warehouses = Warehouse::where('active', true)->get()
            ->transform(function ($row) {
                // $date_of_validation = ValidationWarehouse::where('warehouse_id', $row->id)->max('date_of_validation');
                // $id = ValidationWarehouse::where('warehouse_id', $row->id)->where('date_of_validation', $date_of_validation)->first()->id;
                $last_validation = ValidationWarehouse::where('warehouse_id', $row->id)->latest()->first();
                $date_of_validation = ($last_validation) ? $last_validation->date_of_validation->format('Y-m-d') : null;
                $id = ($last_validation) ? $last_validation->id : null;
                return [
                    'id' => $row->id,
                    'description' => $row->description,
                    'last_validation' => $date_of_validation,
                    'id_validation' => $id,
                ];
            });

        return compact('warehouses');
    }
    public function columns()
    {
        $isFromApi = strpos(request()->url(), '/api/') !== false;

        $columns = [
            'item_id' => 'Producto',
            'item_description' => 'Descripción del producto',
            'internal_id' => 'Código interno',
            'barcode' => 'Código de barras',
        ];
        if ($isFromApi) {
            $columns['warehouse'] = 'Almacén';
        } else {
            $columns['warehouse_id'] = 'Almacén';
        }
        return $columns;
    }

    public function recordsApp(Request $request)
    {
        $column = $request->input('column');
        if ($column == 'warehouse') {
            $records = ItemWarehouse::with(['item', 'warehouse'])
                ->whereHas('item', function ($query) use ($request) {
                    $query->where('unit_type_id', '!=', 'ZZ');
                    $query->whereNotIsSet();
                })
                ->whereHas('warehouse', function ($query) use ($request) {
                    $query->where('description', 'like', '%' . $request->value . '%');
                })
                ->orderBy('item_id');
        } else {
            $records = $this->getCommonRecords($request);
        }

        return new InventoryCollection($records->paginate(config('tenant.items_per_page')));
    }
    public function records(Request $request)
    {
        $isOtherView = $request->input('isOtherView') == 'true';

        if ($isOtherView) {
            return $this->getRecordsOtherView($request);
        }

        return $this->getRecordsNormal($request);
    }

    public function getRecordsOtherView($request)
    {
        $column = $request->input('column');
        $warehouse_id = $request->input('warehouse_id');
        $product_description = $request->input('product_description');
        $records = Item::where('unit_type_id', '!=', 'ZZ')
            ->whereNotIsSet();
        if ($column == 'warehouse') {
            $records = $records
                ->whereHas('item_warehouse', function ($query) use ($request) {
                    $query->where('description', 'like', '%' . $request->value . '%');
                })
                ->orderBy('id');
        } else if ($request->column == 'warehouse_id' && $request->value) {

            $records =
                $records->whereHas('item_warehouse', function ($query) use ($request) {
                    $query->where('warehouse_id', $request->value);
                })
                ->orderBy('id');
        } else if ($request->column == 'item_id' && $request->value) {
            $records =
                $records->where('id', $request->value)
                ->orderBy('id');
        } else if ($request->column == 'item_description' && $request->value) {
            $records =
                $records->where('description', 'like', '%' . $request->value . '%')
                ->orderBy('id');
        } else {
            if ($warehouse_id || $product_description) {
                $records = $records
                    ->when($warehouse_id, function ($query) use ($warehouse_id) {
                        $query->whereHas('item_warehouse', function ($query) use ($warehouse_id) {
                            $query->where('warehouse_id', $warehouse_id);
                        });
                    })
                    ->when($product_description, function ($query) use ($product_description) {
                        $query->where('description', 'like', '%' . $product_description . '%')
                            ->orWhere('internal_id', 'like', '%' . $product_description . '%');
                    })
                    ->orderBy('id');
            }
        }

        $records = $records->orderBy('id');


        return new InventoryOtherViewCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function getRecordsOptimizedOtherView($request)
    {
        $column = $request->input('column');
        $warehouse_id = $request->input('warehouse_id');
        $product_description = $request->input('product_description');

        // Obtener items base con sus relaciones de warehouse
        $records = Item::with(['item_warehouse.warehouse'])
            ->where('unit_type_id', '!=', 'ZZ')
            ->whereNotIsSet();

        if ($column == 'warehouse') {
            $records = $records
                ->whereHas('item_warehouse.warehouse', function ($query) use ($request) {
                    $query->where('description', 'like', '%' . $request->value . '%');
                })
                ->orderBy('id');
        } else if ($request->column == 'warehouse_id' && $request->value) {
            $records =
                $records->whereHas('item_warehouse', function ($query) use ($request) {
                    $query->where('warehouse_id', $request->value);
                })
                ->orderBy('id');
        } else if ($request->column == 'item_id' && $request->value) {
            $records =
                $records->where('id', $request->value)
                ->orderBy('id');
        } else {
            if ($warehouse_id || $product_description) {
                $records = $records
                    ->when($warehouse_id, function ($query) use ($warehouse_id) {
                        $query->whereHas('item_warehouse', function ($query) use ($warehouse_id) {
                            $query->where('warehouse_id', $warehouse_id);
                        });
                    })
                    ->when($product_description, function ($query) use ($product_description) {
                        $query->where('description', 'like', '%' . $product_description . '%')
                            ->orWhere('internal_id', 'like', '%' . $product_description . '%');
                    })
                    ->orderBy('id');
            }
        }

        $records = $records->orderBy('id');

        return new InventoryOtherViewCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function getRecordsNormal($request)
    {
        $configuration = DB::connection('tenant')->table('configurations')->select('select_warehouse_to_sell')->first();
        $column = $request->input('column');
        $warehouse_id = $request->input('warehouse_id');
        $product_description = $request->input('product_description');
        $records = ItemWarehouse::with(['item', 'warehouse']);
        $warehouse_id = null;
        if (!$configuration->select_warehouse_to_sell) {
            $user = auth()->user();
            $establishment_id = $user->establishment_id;
            $warehouse_id = DB::connection('tenant')->table('warehouses')->where('establishment_id', $establishment_id)->first()->id;
        }
        if ($warehouse_id) {
            $records = $records->where('warehouse_id', $warehouse_id);
        }
        $with_stock = $request->input('with_stock') == 'true';
        if ($column == 'warehouse') {
            $records = $records
                ->whereHas('item', function ($query) use ($request) {
                    $query->where('unit_type_id', '!=', 'ZZ');
                    $query->whereNotIsSet();
                })
                ->whereHas('warehouse', function ($query) use ($request) {
                    $query->where('description', 'like', '%' . $request->value . '%');
                })
                ->orderBy('item_id');
        } else if ($request->column == 'warehouse_id' && $request->value) {

            $records =
                $records->where('warehouse_id', $request->value)
                ->orderBy('item_id');
        } else if ($request->column == 'item_description' && $request->value) {
            $records =
                $records->whereHas('item', function ($query) use ($request) {
                    $query->where('description', 'like', '%' . $request->value . '%');
                })
                ->orderBy('id');
        } else if ($request->column == 'item_id' && $request->value) {
            $records =
                $records->where('item_id', $request->value)
                ->orderBy('item_id');
        } else if ($request->column == 'internal_id' && $request->value) {
            $records =
                $records->whereHas('item', function ($query) use ($request) {
                    $query->where('internal_id', 'like', '%' . $request->value . '%');
                })
                ->orderBy('item_id');
        } else {
            if ($warehouse_id || $product_description) {
                $records = $records
                    ->when($warehouse_id, function ($query) use ($warehouse_id) {
                        $query->where('warehouse_id', $warehouse_id);
                    })
                    ->when($product_description, function ($query) use ($product_description) {
                        $query->whereHas('item', function ($query) use ($product_description) {
                            $query->where('description', 'like', '%' . $product_description . '%')
                                ->orWhere('internal_id', 'like', '%' . $product_description . '%');
                        });
                    })
                    ->orderBy('item_id');
            } else {
                $records = $this->getCommonRecords($request);
            }
        }
        if ($with_stock) {
            $records = $records->where('stock', '>', 0);
        }
        $records = $records->orderBy('item_id');


        return new InventoryCollection($records->paginate(config('tenant.items_per_page')));
    }


    /**
     *
     * Obtener registros
     *
     * @param Request $request
     * @return ItemWarehouse
     */
    public function getCommonRecords($request)
    {
        return ItemWarehouse::with(['item', 'warehouse'])
            ->whereHas('item', function ($query) use ($request) {
                $query->where('unit_type_id', '!=', 'ZZ');
                $query->whereNotIsSet();

                if ($this->applyAdvancedRecordsSearch() && $request->column === 'description') {
                    if ($request->value) $query->whereAdvancedRecordsSearch($request->column, $request->value);
                } else {
                    if ($request->column === 'description') {
                        $query->where('description', 'like', '%' . $request->value . '%')
                            ->orWhere('internal_id', 'like', '%' . $request->value . '%');
                    } else if ($request->column === 'barcode') {
                        $query->where('barcode', 'like', '%' . $request->value . '%');
                    } else {
                        if ($request->column && $request->value) {

                            $query->where($request->column, 'like', '%' . $request->value . '%');
                        }
                    }
                }
            });
    }


    public function tables()
    {
        return [
            'items' => $this->optionsItem(),
            'warehouses' => $this->optionsWarehouse()
        ];
    }
    public function recordItem($id, $warehouse_id)
    {
        $item_warehouse = ItemWarehouse::with(['item', 'warehouse'])->where('item_id', $id)->where('warehouse_id', $warehouse_id)->first();
        if (!$item_warehouse) {
            return [
                'success' => false,
                'message' => 'El producto no se encuentra registrado en el almacén indicado.'
            ];
        }
        $record = new InventoryResource($item_warehouse);
        return $record;
    }

    public function record($id)
    {
        if (is_numeric($id)) {
            $record = new InventoryResource(ItemWarehouse::with(['item', 'warehouse'])->findOrFail($id));
        } else {
            request()->validate([
                'ids' => 'required|array|min:1'
            ]);
            $data = ItemWarehouse::with(['item', 'warehouse'])
                ->whereIn('id', request('ids'))
                ->get();

            $record = InventoryResource::collection($data);
        }

        return $record;
    }

    public function tables_transaction($type)
    {
        return [
            //            'items' => $this->optionsItemFull(),
            'references' => $this->optionsReference(),
            'warehouses' => $this->optionsWarehouse(),
            'inventory_transactions' => $this->optionsInventoryTransaction($type),
        ];
    }

    public function tables_transaction_input()
    {
        return [
            'items' => $this->optionsInventoryTransaction('input'),
        ];
    }

    public function tables_transaction_output()
    {
        return [
            'items' => $this->optionsInventoryTransaction('output'),
        ];
    }
    public function searchItems(Request $request)
    {
        $search = $request->input('search');

        return [
            'items' => $this->optionsItemFull($search, 20),
        ];
    }

    public function ExtraDataList()
    {
        return view('inventory::extra_info.index');
    }

    public function store(Request $request)
    {
        $result =  DB::connection('tenant')->transaction(function () use ($request) {
            $item_id = $request->input('item_id');
            $warehouse_id = $request->input('warehouse_id');
            $quantity = $request->input('quantity');

            $item_warehouse = ItemWarehouse::firstOrNew([
                'item_id' => $item_id,
                'warehouse_id' => $warehouse_id
            ]);
            if ($item_warehouse->id) {
                return [
                    'success' => false,
                    'message' => 'El producto ya se encuentra registrado en el almacén indicado.'
                ];
            }

            // $item_warehouse->stock = $quantity;
            // $item_warehouse->save();

            $inventory = new Inventory();
            $inventory->type = 1;
            $inventory->description = 'Stock inicial';
            $inventory->item_id = $item_id;
            $inventory->warehouse_id = $warehouse_id;
            $inventory->quantity = $quantity;
            $inventory->save();

            return [
                'success' => true,
                'message' => 'Producto registrado en almacén'
            ];
        });

        return $result;
    }

    public function store_transaction_import(Request $request)
    {
        try {
            DB::connection('tenant')->beginTransaction();
            $type = $request->input('type');
            $inventories_ids = [];
            $inventory_transaction_id = $request->input('inventory_transaction_id');
            $warehouse_id = $request->input('warehouse_id');
            $warehouse = Warehouse::findOrFail($warehouse_id);
            $comments = $request->input('comments');
            $inventory_reference_id = $request->input('inventory_reference_id');
            $created_at = $request->input('created_at');
            $items = $request->input('items');
            $inventory_transaction = InventoryTransaction::findOrFail($inventory_transaction_id);
            $guide_items = [];
            foreach ($items as $item) {
                $item_id = $item['id'];
                $quantity = $item['quantity'];
                $item_warehouse = ItemWarehouse::firstOrNew([
                    'item_id' => $item_id,
                    'warehouse_id' => $warehouse_id
                ]);

                $inventory = new Inventory();
                $inventory->type = null;
                $inventory->description = $inventory_transaction->name;
                $inventory->item_id = $item_id;
                $inventory->warehouse_id = $warehouse_id;
                $inventory->quantity = $quantity;
                $inventory->inventory_reference_id = $inventory_reference_id;
                $inventory->inventory_transaction_id = $inventory_transaction_id;
                $inventory->comments = $comments;
                if ($created_at) {
                    $inventory->date_of_issue = $created_at;
                }
                $inventory->save();
                $inventories_ids[] = $inventory->id;
                $guide_items[] = [
                    'id' => $item_id,
                    'name' => $item_warehouse->item->description,
                    'stock_add' => $quantity
                ];
            }

            $res = (new GuideController())->storeWithData([
                'establishment_id' => $warehouse->establishment_id,
                'warehouse_id' => $warehouse_id,
                'date_of_issue' => now()->format('Y-m-d'),
                'time_of_issue' => now()->format('H:i:s'),
                'inventory_transaction_id' => $inventory_transaction_id,
                'observations' => $comments,
                'items' => $guide_items
            ]);
            if (!$res['success']) {
                throw new Exception($res['message']);
            }
            foreach ($inventories_ids as $inventory_id) {
                $inventory = Inventory::findOrFail($inventory_id);
                $inventory->update([
                    'guide_id' => $res['data']['id']
                ]);
            }
            DB::connection('tenant')->commit();
            return [
                'success' => true,
                'message' => 'Transacción registrada correctamente',
                'url_guide' => url('') . '/reports/kardex/get_pdf_guide/' . $res['data']['id']
            ];
        } catch (Exception $e) {
            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
        }
    }
    public function store_transaction(InventoryRequest $request)
    {
        DB::connection('tenant')->beginTransaction();
        try {

            $type = $request->input('type');
            $item_id = $request->input('item_id');
            $warehouse_id = $request->input('warehouse_id');
            $inventory_transaction_id = $request->input('inventory_transaction_id');
            $quantity = $request->input('quantity');
            $lot_code = $request->input('lot_code');
            $comments = $request->input('comments');
            $inventory_reference_id = $request->input('inventory_reference_id');
            $created_at = $request->input('created_at');


            $lots = ($request->has('lots')) ? $request->input('lots') : [];
            $sizes = ($request->has('sizes')) ? $request->input('sizes') : [];

            $item_warehouse = ItemWarehouse::firstOrNew([
                'item_id' => $item_id,
                'warehouse_id' => $warehouse_id
            ]);

            $inventory_transaction = InventoryTransaction::findOrFail($inventory_transaction_id);

            if ($type == 'output' && ($quantity > $item_warehouse->stock)) {
                return [
                    'success' => false,
                    'message' => 'La cantidad no puede ser mayor a la que se tiene en el almacén.'
                ];
            }

            $inventory = new Inventory();
            $inventory->type = null;
            $inventory->description = $inventory_transaction->name;
            $inventory->item_id = $item_id;
            $inventory->warehouse_id = $warehouse_id;
            $inventory->quantity = $quantity;
            $inventory->inventory_reference_id = $inventory_reference_id;
            $inventory->inventory_transaction_id = $inventory_transaction_id;
            $inventory->lot_code = $lot_code;
            $inventory->comments = $comments;


            if ($created_at) {
                $inventory->date_of_issue = $created_at;
            }

            $inventory->save();

            $warehouse = Warehouse::query()->find($warehouse_id);

            $itm = Item::query()
                ->select('id', 'description')
                ->where('id', $item_id)
                ->first();
            if ($request->attribute_enabled) {
                foreach ($request->attributes_list as $row) {
                    ItemProperty::updateOrCreate(['id' => $row['id']], [
                        'item_id'     => $itm->id,
                        'warehouse_id' => $request->warehouse_id,
                        'attribute'   => $row['attribute'],
                        'attribute2'  => $row['attribute2'],
                        'attribute3'  => $row['attribute3'],
                        'attribute4'  => $row['attribute4'],
                        'attribute5'  => $row['attribute5'],
                        'chassis'     => $row['chassis'],
                        'sales_price' => $row['sales_price'],
                        'state'       => "Activo"
                    ]);
                }
            }
            $itm->restoreStockSizes();
            $guide_items[] = [
                'id' => $item_id,
                'name' => $itm->description,
                'stock_add' => $quantity
            ];
            $res = (new GuideController())->storeWithData([
                'establishment_id' => $warehouse->establishment_id,
                'warehouse_id' => $warehouse_id,
                'date_of_issue' => now()->format('Y-m-d'),
                'time_of_issue' => now()->format('H:i:s'),
                'inventory_transaction_id' => $inventory_transaction_id,
                'observations' => $comments,
                'items' => $guide_items
            ]);

            if (!$res['success']) {
                throw new Exception($res['message']);
            }

            $inventory->update([
                'guide_id' => $res['data']['id']
            ]);

            $lots_enabled = isset($request->lots_enabled) ? $request->lots_enabled : false;

            if ($type == 'input') {
                foreach ($sizes as $size) {
                    //encuentra elitemsizestock y agregarle la cantidad
                    $original_size = Functions::valueKeyInArray($size, 'originalSize', null);
                    $size_ = $size['size'];
                    $is_edit = $original_size != $size_;

                    if ($is_edit) {
                        $item_size_stock = ItemSizeStock::where('item_id', $item_id)->where('warehouse_id', $warehouse_id)->where('size', $original_size)->first();
                        if ($item_size_stock) {
                            $item_size_stock->size = $size_;
                            $item_size_stock->stock = $item_size_stock->stock +  $size['quantity'];
                            $item_size_stock->update();
                        } else {
                            $new = new ItemSizeStock();
                            $new->item_id = $item_id;
                            $new->warehouse_id = $warehouse_id;
                            $new->size = $size_;
                            $new->stock = $size['quantity'];
                            $new->save();
                        }
                    } else {
                        $exit = ItemSizeStock::where('item_id', $item_id)->where('warehouse_id', $warehouse_id)->where('size', $size['size'])->first();
                        if ($exit) {
                            $exit->stock = $exit->stock + $size['quantity'];
                            $exit->update();
                        } else {
                            $new = new ItemSizeStock();
                            $new->item_id = $item_id;
                            $new->warehouse_id = $warehouse_id;
                            $new->size = $size['size'];
                            $new->stock = $size['quantity'];
                            $new->save();
                        }
                    }
                }
                foreach ($lots as $lot) {
                    /*$inventory->lots()->create([
                        'date' => $lot['date'],
                        'series' => $lot['series'],
                        'item_id' => $item_id,
                        'warehouse_id' => $warehouse_id,
                        'has_sale' => false
                    ]);*/

                    $inventory->lots()->create([
                        'date' => $lot['date'],
                        'date_incoming' => isset($lot['date_incoming']) ? $lot['date_incoming'] : null,
                        'series' => $lot['series'],
                        'item_id' => $item_id,
                        'warehouse_id' => $warehouse_id,
                        'has_sale' => false,
                        'state' => $lot['state'],
                    ]);
                }

                if ($lots_enabled) {
                    ItemLotsGroup::create([
                        'code' => $lot_code,
                        'quantity' => $quantity,
                        'date_of_due' => $request->date_of_due,
                        'item_id' => $item_id,
                        'warehouse_id' => $warehouse_id,
                    ]);
                }
            } else {
                foreach ($sizes as $size) {
                    $original_size = Functions::valueKeyInArray($size, 'originalSize', null);
                    $size_ = $size['size'];
                    $is_edit = $original_size != $size_;
                    //encuentra elitemsizestock y agregarle la cantidad
                    if ($is_edit) {
                        $item_size_stock = ItemSizeStock::where('item_id', $item_id)->where('warehouse_id', $warehouse_id)->where('size', $original_size)->first();
                        if ($item_size_stock) {
                            $item_size_stock->size = $size_;
                            $item_size_stock->stock = $item_size_stock->stock -  $size['quantity'];
                            $item_size_stock->update();
                        }
                    } else {
                        $exit = ItemSizeStock::where('item_id', $item_id)->where('warehouse_id', $warehouse_id)->where('size', $size['size'])->first();
                        if ($exit) {
                            $exit->stock = $exit->stock - $size['quantity'];
                            $exit->update();
                        }
                    }
                }
                foreach ($lots as $lot) {
                    if ($lot['has_sale']) {
                        $item_lot = ItemLot::findOrFail($lot['id']);
                        // $item_lot->delete();
                        $item_lot->has_sale = true;
                        $item_lot->state = 'Inactivo';
                        $item_lot->save();
                    }
                }

                if (isset($request->IdLoteSelected)) {
                    if (is_array($request->IdLoteSelected)) {
                        foreach ($request->IdLoteSelected as $row) {
                            Log::info($row);
                            $lot = ItemLotsGroup::find($row['id']);
                            $lot->quantity = ($lot->quantity - $row['compromise_quantity']);
                            $lot->save();
                        }
                    } else {
                        $lot = ItemLotsGroup::find($request->IdLoteSelected);
                        $lot->quantity = ($lot->quantity - $quantity);
                        $lot->save();
                    }
                }
            }
            DB::connection('tenant')->commit();

            return [
                'success' => true,
                'message' => ($type == 'input') ? 'Ingreso registrado correctamente' : 'Salida registrada correctamente'
            ];
        } catch (Exception $e) {
            DB::connection('tenant')->rollBack();

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
        }
    }

    public function moveMultiples(Request $request)
    {
        $request->validate([
            'items' => 'required|array'
        ]);

        DB::connection('tenant')->beginTransaction();
        try {
            $items = $request->items;
            foreach ($items as $item) {
                $item_id = $item['item_id'];
                $warehouse_id = $item['warehouse_id'];
                $warehouse_new_id = $item['warehouse_new_id'];
                $quantity = $item['quantity'];
                $quantity_move = $item['quantity_move'];
                $detail = $item['detail'];
                if ($quantity_move <= 0) {
                    throw new Exception("La cantidad del producto {$item['item_description']} a trasladar debe ser mayor a 0", 500);
                }

                if ($warehouse_id === $warehouse_new_id) {
                    throw new Exception("El almacén destino del producto {$item['item_description']} no puede ser igual al de origen", 500);
                }
                if ($quantity < $quantity_move) {
                    throw new Exception("La cantidad a trasladar del producto {$item['item_description']} no puede ser mayor al que se tiene en el almacén.", 500);
                }

                $inventory = new Inventory();
                $inventory->type = 2;
                $inventory->description = 'Traslado';
                $inventory->item_id = $item_id;
                $inventory->warehouse_id = $warehouse_id;
                $inventory->warehouse_destination_id = $warehouse_new_id;
                $inventory->quantity = $quantity_move;
                $inventory->detail = $detail;

                $inventory->save();
            }
            DB::connection('tenant')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Productos trasladados con éxito'
            ], 200);
        } catch (\Throwable $th) {
            DB::connection('tenant')->rollBack();
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    //	public function move(Request $request)
    //	{
    //		$result =  DB::connection('tenant')->transaction(function () use ($request) {
    //			$id = $request->input('id');
    //			$item_id = $request->input('item_id');
    //			$warehouse_id = $request->input('warehouse_id');
    //			$warehouse_new_id = $request->input('warehouse_new_id');
    //			$quantity = $request->input('quantity');
    //			$quantity_move = $request->input('quantity_move');
    //			$lots = ($request->has('lots')) ? $request->input('lots') : [];
    //			$detail = $request->input('detail');
    //
    //			if ($quantity_move <= 0) {
    //				return  [
    //					'success' => false,
    //					'message' => 'La cantidad a trasladar debe ser mayor a 0'
    //				];
    //			}
    //
    //			if ($warehouse_id === $warehouse_new_id) {
    //				return  [
    //					'success' => false,
    //					'message' => 'El almacén destino no puede ser igual al de origen'
    //				];
    //			}
    //			if ($quantity < $quantity_move) {
    //				return  [
    //					'success' => false,
    //					'message' => 'La cantidad a trasladar no puede ser mayor al que se tiene en el almacén.'
    //				];
    //			}
    //
    //			$inventory = new Inventory();
    //			$inventory->type = 2;
    //			$inventory->description = 'Traslado';
    //			$inventory->item_id = $item_id;
    //			$inventory->warehouse_id = $warehouse_id;
    //			$inventory->warehouse_destination_id = $warehouse_new_id;
    //			$inventory->quantity = $quantity_move;
    //			$inventory->detail = $detail;
    //
    //			$inventory->save();
    //
    //			foreach ($lots as $lot) {
    //				if ($lot['has_sale']) {
    //					$item_lot = ItemLot::findOrFail($lot['id']);
    //					$item_lot->warehouse_id = $inventory->warehouse_destination_id;
    //					$item_lot->update();
    //				}
    //			}
    //
    //			return  [
    //				'success' => true,
    //				'message' => 'Producto trasladado con éxito'
    //			];
    //		});
    //
    //		return $result;
    //	}

    //	public function remove(Request $request)
    //	{
    //		$result =  DB::connection('tenant')->transaction(function () use ($request) {
    //			$item_id = $request->input('item_id');
    //			$warehouse_id = $request->input('warehouse_id');
    //			$quantity = $request->input('quantity');
    //			$quantity_remove = $request->input('quantity_remove');
    //			$lots = ($request->has('lots')) ? $request->input('lots') : [];
    //
    //			//Transaction
    //			$item_warehouse = ItemWarehouse::where('item_id', $item_id)
    //										   ->where('warehouse_id', $warehouse_id)
    //										   ->first();
    //			if (!$item_warehouse) {
    //				return [
    //					'success' => false,
    //					'message' => 'El producto no se encuentra en el almacén indicado'
    //				];
    //			}
    //
    //			if ($quantity < $quantity_remove) {
    //				return  [
    //					'success' => false,
    //					'message' => 'La cantidad a retirar no puede ser mayor al que se tiene en el almacén.'
    //				];
    //			}
    //
    //			// $item_warehouse->stock = $quantity - $quantity_remove;
    //			// $item_warehouse->save();
    //
    //			$inventory = new Inventory();
    //			$inventory->type = 3;
    //			$inventory->description = 'Retirar';
    //			$inventory->item_id = $item_id;
    //			$inventory->warehouse_id = $warehouse_id;
    //			$inventory->quantity = $quantity_remove;
    //			$inventory->save();
    //
    //			foreach ($lots as $lot) {
    //				if ($lot['has_sale']) {
    //					$item_lot = ItemLot::findOrFail($lot['id']);
    //					$item_lot->delete();
    //				}
    //			}
    //
    //			return  [
    //				'success' => true,
    //				'message' => 'Producto trasladado con éxito'
    //			];
    //		});
    //
    //		return $result;
    //	}


    public function stock(Request $request)
    {
        $result =  DB::connection('tenant')->transaction(function () use ($request) {
            $id = $request->input('id');
            $item_id = $request->input('item_id');
            $warehouse_id = $request->input('warehouse_id');
            $quantity = $request->input('quantity');
            $quantity_real = $request->input('quantity_real');
            $lots = ($request->has('lots')) ? $request->input('lots') : [];
            $sizes = ($request->has('sizes')) ? $request->input('sizes') : [];
            foreach ($sizes as $size) {
                if (array_key_exists('qty', $size)) {
                    $exit = ItemSizeStock::where('item_id', $item_id)->where('warehouse_id', $warehouse_id)->where('size', $size['size'])->first();
                    if ($exit) {
                        $exit->stock = $size['qty'];
                        $exit->update();
                    } else {
                        $new = new ItemSizeStock();
                        $new->item_id = $item_id;
                        $new->warehouse_id = $warehouse_id;
                        $new->size = $size['size'];
                        $new->stock = $size['qty'];
                        $new->save();
                    }
                }
            }
            if ($quantity_real < 0) {
                return [
                    'success' => false,
                    'message' => 'La cantidad de stock real debe ser mayor o igual a 0'
                ];
            }
            $type = 1;
            $quantity_new = 0;
            $quantity_new = $quantity_real - $quantity;
            if ($quantity_real < $quantity) {
                $quantity_new = $quantity - $quantity_real;
                $type = null;
            }

            $inventory = new Inventory();
            $inventory->type = $type;
            $inventory->description = 'Stock Real';
            $inventory->item_id = $item_id;
            $inventory->warehouse_id = $warehouse_id;
            $inventory->quantity = $quantity_new;
            if ($quantity_real < $quantity) {
                $inventory->inventory_transaction_id = 28;
            }

            $inventory->real_stock = $request->quantity_real;
            $inventory->system_stock = $request->quantity;

            $inventory->save();

            return [
                'success' => true,
                'message' => 'Cantidad de stock actualizado con éxito'
            ];
        });

        return $result;
    }

    public function stockMultiples(Request $request)
    {
        $request->validate([
            'items' => 'required|array'
        ]);

        DB::connection('tenant')->beginTransaction();
        try {
            $items = $request->items;
            foreach ($items as $item) {
                $item_id = $item['item_id'];
                $warehouse_id = $item['warehouse_id'];
                $quantity = $item['quantity'];
                $quantity_real = $item['quantity_real'];

                if ($quantity_real < 0) throw new Exception("La cantidad del producto {$item['item_description']} a modificar debe ser mayor o igual a 0", 500);

                $type = 1;
                $quantity_new = 0;
                $quantity_new = $quantity_real - $quantity;
                if ($quantity_real < $quantity) {
                    $quantity_new = $quantity - $quantity_real;
                    $type = null;
                }

                $inventory = new Inventory();
                $inventory->type = $type;
                $inventory->description = 'Stock Real';
                $inventory->item_id = $item_id;
                $inventory->warehouse_id = $warehouse_id;
                $inventory->quantity = $quantity_new;
                if ($quantity_real < $quantity) {
                    $inventory->inventory_transaction_id = 28;
                }

                $inventory->real_stock = $item['quantity_real'];
                $inventory->system_stock = $item['quantity'];

                $inventory->save();
            }
            DB::connection('tenant')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Cantidad de stock actualizado con éxito'
            ], 200);
        } catch (\Throwable $th) {
            DB::connection('tenant')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    private function getRecords(Request $request)
    {
        $column = $request->input('column');
        $warehouse_id = $request->input('warehouse_id');

        $product_description = $request->input('product_description');
        if ($column == 'warehouse') {
            $records = ItemWarehouse::with(['item', 'warehouse'])
                ->whereHas('item', function ($query) use ($request) {
                    $query->where('unit_type_id', '!=', 'ZZ');
                    $query->whereNotIsSet();
                })
                ->whereHas('warehouse', function ($query) use ($request) {
                    $query->where('description', 'like', '%' . $request->value . '%');
                })
                ->orderBy('item_id');
        } else if ($request->column == 'warehouse_id' && $request->value) {

            $records = ItemWarehouse::with(['item', 'warehouse'])
                ->where('warehouse_id', $request->value)
                ->orderBy('item_id');
        } else {
            if ($warehouse_id || $product_description) {
                $records = ItemWarehouse::with(['item', 'warehouse'])
                    ->when($warehouse_id, function ($query) use ($warehouse_id) {
                        $query->where('warehouse_id', $warehouse_id);
                    })
                    ->when($product_description, function ($query) use ($product_description) {
                        $query->whereHas('item', function ($query) use ($product_description) {
                            $query->where('description', 'like', '%' . $product_description . '%')
                                ->orWhere('internal_id', 'like', '%' . $product_description . '%');
                        });
                    })
                    ->orderBy('item_id');
            } else {
                $records = $this->getCommonRecords($request);
            }
        }

        return $records->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'item_id' => $row->item_id,
                'item_internal_id' => $row->item->internal_id,
                'item_description' => $row->item->description,
                'item_fulldescription' => ($row->item->internal_id) ? "{$row->item->internal_id} - {$row->item->description}" : $row->item->description,
                'warehouse_description' => $row->warehouse->description,
                'warehouse_id' => $row->warehouse_id,
                'stock' => $row->stock,
                'created_at' => $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : null,
                'updated_at' => $row->updated_at ? $row->updated_at->format('Y-m-d H:i:s') : null,
            ];
        });
    }
    private function inner_excel(Request $request)
    {
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 300);

        $company = Company::first();
        $company_name = $company->name;
        $company_number = $company->number;
        $warehouse_description = null;

        if ($request->warehouse_id && $request->column == 'warehouse_id') {
            $warehouse = Warehouse::find($request->warehouse_id);
            $warehouse_description = $warehouse->description;
        }

        // Obtener registros optimizados
        $records = $this->getRecordsOptimized($request);

        // Crear y configurar el export
        $export = (new \Modules\Inventory\Exports\InventoryListExport())
            ->allRecords($records)
            ->companyName($company_name)
            ->companyNumber($company_number)
            ->warehouseDescription($warehouse_description);

        $filename = 'inventario_' . date('Y-m-d_H-i-s') . '.xlsx';

        return $export->download($filename);
    }

    public function excel(Request $request)
    {
        $isOtherView = $request->input('isOtherView') == 'true';
        if ($isOtherView) {
            return $this->inner_excel_other_view($request);
        }
        return $this->inner_excel($request);
    }

    public function inner_excel_other_view(Request $request)
    {
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', 300);

        $company = Company::first();
        $company_name = $company->name;
        $company_number = $company->number;

        // Crear y configurar el export de matriz
        $export = (new \Modules\Inventory\Exports\InventoryMatrixExport())
            ->companyName($company_name)
            ->companyNumber($company_number);

        $filename = 'inventario_matriz_' . date('Y-m-d_H-i-s') . '.xlsx';

        return $export->download($filename);
    }

    public function pdf(Request $request)
    {
        $isOtherView = $request->input('isOtherView') == 'true';
        if ($isOtherView) {
            return $this->inner_pdf_other_view($request);
        }
        return $this->inner_pdf($request);
    }

    public function inner_pdf(Request $request)
    {
        ini_set('memory_limit', '2G');
        ini_set('max_execution_time', 600);

        $company = Company::first();
        $company_name = $company->name;
        $company_number = $company->number;
        $warehouse_description = null;

        if ($request->warehouse_id && $request->column == 'warehouse_id') {
            $warehouse = Warehouse::find($request->warehouse_id);
            $warehouse_description = $warehouse->description;
        }

        // Generar PDF en chunks para evitar problemas de memoria
        $pdf = $this->generatePdfInChunks($request, $company_name, $company_number, $warehouse_description);

        return $pdf->stream('inventario.pdf');
    }

    public function inner_pdf_other_view(Request $request)
    {
        ini_set('memory_limit', '2G');
        ini_set('max_execution_time', 600);

        $company = Company::first();
        $company_name = $company->name;
        $company_number = $company->number;

        // Obtener almacenes y preparar datos para la matriz
        $warehouses = Warehouse::where('active', true)->orderBy('description')->get();

        // Obtener items con sus stocks por almacén
        $items = Item::with(['item_warehouse.warehouse'])
            ->where('unit_type_id', '!=', 'ZZ')
            ->whereNotIsSet()
            ->whereHas('item_warehouse')
            ->orderBy('description')
            ->get();

        // Preparar datos para el PDF
        $matrixData = collect();
        foreach ($items as $item) {
            $row = [
                'item_description' => $item->internal_id ? "{$item->internal_id} - {$item->description}" : $item->description,
                'warehouses' => []
            ];

            foreach ($warehouses as $warehouse) {
                $itemWarehouse = $item->item_warehouse->where('warehouse_id', $warehouse->id)->first();
                $stock = $itemWarehouse ? $itemWarehouse->stock : 0;
                $row['warehouses'][] = $stock;
            }

            $matrixData->push($row);
        }

        $pdf = PDF::loadView('inventory::inventory.report_matrix_pdf', compact('matrixData', 'warehouses', 'company_name', 'company_number'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream('inventario_matriz.pdf');
    }

    private function generatePdfInChunks(Request $request, $company_name, $company_number, $warehouse_description)
    {
        $chunkSize = 100; // Procesar 100 registros a la vez
        $page = 1;
        $allRecords = collect();

        // Obtener el query base
        $baseQuery = $this->getBaseQuery($request);
        $totalRecords = $baseQuery->count();

        // Procesar en chunks
        for ($offset = 0; $offset < $totalRecords; $offset += $chunkSize) {
            $chunk = $baseQuery->offset($offset)->limit($chunkSize)->get();

            $records = $chunk->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'item_id' => $row->item_id,
                    'item_internal_id' => $row->item->internal_id,
                    'item_description' => $row->item->description,
                    'item_fulldescription' => ($row->item->internal_id) ? "{$row->item->internal_id} - {$row->item->description}" : $row->item->description,
                    'warehouse_description' => $row->warehouse->description,
                    'warehouse_id' => $row->warehouse_id,
                    'stock' => $row->stock,
                    'created_at' => $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : null,
                    'updated_at' => $row->updated_at ? $row->updated_at->format('Y-m-d H:i:s') : null,
                ];
            });

            $allRecords = $allRecords->merge($records);

            // Liberar memoria
            unset($chunk);
            gc_collect_cycles();
        }

        // Generar PDF con todos los registros
        return PDF::loadView('inventory::inventory.report_pdf', compact('allRecords', 'warehouse_description', 'company_name', 'company_number'))
            ->setPaper('a4', 'landscape');
    }

    private function getBaseQuery(Request $request)
    {
        $column = $request->input('column');
        $warehouse_id = $request->input('warehouse_id');
        $product_description = $request->input('product_description');
        $with_stock = $request->input('with_stock') == 'true';
        $records = ItemWarehouse::with(['item:id,internal_id,description', 'warehouse:id,description']);
        if ($column == 'warehouse') {
            $records
                ->whereHas('item', function ($query) use ($request) {
                    $query->where('unit_type_id', '!=', 'ZZ');
                    $query->whereNotIsSet();
                })
                ->whereHas('warehouse', function ($query) use ($request) {
                    $query->where('description', 'like', '%' . $request->value . '%');
                });
        } else if ($request->column == 'warehouse_id' && $request->value) {
            $records
                ->where('warehouse_id', $request->value);
        } else if ($request->column == 'item_id' && $request->value) {
            $records
                ->where('item_id', $request->value);
        } else {
            if ($warehouse_id || $product_description) {
                $records
                    ->when($warehouse_id, function ($query) use ($warehouse_id) {
                        $query->where('warehouse_id', $warehouse_id);
                    })
                    ->when($product_description, function ($query) use ($product_description) {
                        $query->whereHas('item', function ($query) use ($product_description) {
                            $query->where('description', 'like', '%' . $product_description . '%')
                                ->orWhere('internal_id', 'like', '%' . $product_description . '%');
                        });
                    });
            } else {
                $records
                    ->whereHas('item', function ($query) {
                        $query->where('unit_type_id', '!=', 'ZZ');
                        $query->whereNotIsSet();
                    });
            }
        }

        if ($with_stock) {
            $records = $records->where('stock', '>', 0);
        }
        $records = $records->orderBy('item_id');

        return $records;
    }

    public function import(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|numeric|min:1'
        ]);
        if ($request->hasFile('file')) {
            try {
                $import = new StockImport();
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

    public function move(Request $request)
    {
        DB::connection('tenant')->beginTransaction();
        try {
            $id = $request->input('id');
            $item_id = $request->input('item_id');
            $warehouse_id = $request->input('warehouse_id');
            $warehouse_new_id = $request->input('warehouse_new_id');
            $quantity = $request->input('quantity');
            $quantity_move = $request->input('quantity_move');
            $lots = ($request->has('lots')) ? $request->input('lots') : [];
            $detail = $request->input('detail');

            if ($quantity_move <= 0) {
                return [
                    'success' => false,
                    'message' => 'La cantidad a trasladar debe ser mayor a 0'
                ];
            }

            if ($warehouse_id === $warehouse_new_id) {
                return [
                    'success' => false,
                    'message' => 'El almacén destino no puede ser igual al de origen'
                ];
            }
            if ($quantity < $quantity_move) {
                return [
                    'success' => false,
                    'message' => 'La cantidad a trasladar no puede ser mayor al que se tiene en el almacén.'
                ];
            }

            $document_type_id = 'U4';
            $warehouse = Warehouse::query()
                ->select('id', 'establishment_id')
                ->where('id', $warehouse_id)
                ->first();

            $series = Series::query()
                ->select('number')
                ->where('establishment_id', $warehouse->establishment_id)
                ->where('document_type_id', 'U4')
                ->first();

            if (!$series) {
                throw new Exception('No se encontraron series en el establecimiento.');
            }

            $row = InventoryTransfer::query()
                ->create([
                    'description' => $detail,
                    'warehouse_id' => $warehouse_id,
                    'warehouse_destination_id' => $warehouse_new_id,
                    'quantity' => 1,
                    'document_type_id' => $document_type_id,
                    'series' => $series->number,
                    'number' => '#',
                ]);
            if ($request->lots_group) {
                foreach ($request->lots_group as $lot) {
                    InventoryTransferItem::query()->create([
                        'inventory_transfer_id' => $row->id,
                        'item_lots_group_id' => $lot['id'],
                    ]);
                }
            }
            if ($request->lots) {
                foreach ($request->lots as $lot) {
                    InventoryTransferItem::query()->create([
                        'inventory_transfer_id' => $row->id,
                        'item_lot_id' => $lot['id'],
                    ]);
                }
            }
            $inventory = new Inventory();
            $inventory->type = 2;
            $inventory->description = 'Traslado';
            $inventory->item_id = $item_id;
            $inventory->warehouse_id = $warehouse_id;
            $inventory->warehouse_destination_id = $warehouse_new_id;
            $inventory->quantity = $quantity_move;
            $inventory->inventories_transfer_id = $row->id;
            $inventory->detail = $detail;
            $inventory->save();

            foreach ($lots as $lot) {
                if ($lot['has_sale']) {
                    $item_lot = ItemLot::findOrFail($lot['id']);
                    $item_lot->warehouse_id = $inventory->warehouse_destination_id;
                    $item_lot->update();
                }
            }



            DB::connection('tenant')->commit();
            return [
                'success' => true,
                'message' => 'Producto trasladado con éxito'
            ];
        } catch (Exception $e) {
            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function remove(RemoveRequest $request)
    {
        $result =  DB::connection('tenant')->transaction(function () use ($request) {
            $item_id = $request->input('item_id');
            $warehouse_id = $request->input('warehouse_id');
            $quantity = $request->input('quantity');
            $quantity_remove = $request->input('quantity_remove');
            $lots = ($request->has('lots')) ? $request->input('lots') : [];

            //Transaction
            $item_warehouse = ItemWarehouse::where('item_id', $item_id)
                ->where('warehouse_id', $warehouse_id)
                ->first();
            if (!$item_warehouse) {
                return [
                    'success' => false,
                    'message' => 'El producto no se encuentra en el almacén indicado'
                ];
            }

            if ($quantity < $quantity_remove) {
                return [
                    'success' => false,
                    'message' => 'La cantidad a retirar no puede ser mayor al que se tiene en el almacén.'
                ];
            }

            // $item_warehouse->stock = $quantity - $quantity_remove;
            // $item_warehouse->save();

            $inventory = new Inventory();
            $inventory->type = 3;
            $inventory->inventory_transaction_id = '12';
            $inventory->description = 'Retiro';
            $inventory->item_id = $item_id;
            $inventory->warehouse_id = $warehouse_id;
            $inventory->quantity = $quantity_remove;
            $inventory->save();

            $warehouse = Warehouse::query()->find($warehouse_id);

            $itm = Item::query()
                ->select('id', 'description')
                ->where('id', $item_id)
                ->first();

            $guide_items[] = [
                'id' => $item_id,
                'name' => $itm->description,
                'stock_add' => $quantity_remove
            ];

            $res = (new GuideController())->storeWithData([
                'establishment_id' => $warehouse->establishment_id,
                'warehouse_id' => $warehouse_id,
                'date_of_issue' => now()->format('Y-m-d'),
                'time_of_issue' => now()->format('H:i:s'),
                'inventory_transaction_id' => '12',
                'observations' => 'Retiro',
                'items' => $guide_items
            ]);

            if (!$res['success']) {
                // throw new Exception($res['message']);
                return [
                    'success' => false,
                    'message' => $res['message']
                ];
            }

            $inventory->update([
                'guide_id' => $res['data']['id']
            ]);

            foreach ($lots as $lot) {
                if ($lot['has_sale']) {
                    $item_lot = ItemLot::findOrFail($lot['id']);
                    $item_lot->delete();
                }
            }

            $this->removeItemLotsGroup($request);

            return [
                'success' => true,
                'message' => 'Producto trasladado con éxito'
            ];
        });

        return $result;
    }


    /**
     * Remover lotes
     *
     * @param RemoveRequest $request
     * @return void
     */
    public function removeItemLotsGroup($request)
    {
        $selected_lots_group = $request->selected_lots_group ?? null;

        if ($selected_lots_group) {
            foreach ($selected_lots_group as $lots_group) {
                $lot = $this->getItemLotsGroupById($lots_group['id']);
                $lot->quantity = $lot->quantity - $lots_group['compromise_quantity'];
                $lot->save();
            }
        }
    }


    public function initialize()
    {
        $this->initializeInventory();
    }

    public function regularize_stock()
    {
        DB::connection('tenant')->transaction(function () {
            $item_warehouses = ItemWarehouse::where('active', true)->get();

            foreach ($item_warehouses as $it_warehouse) {
                $inv_kardex = InventoryKardex::where([['item_id', $it_warehouse->item_id], ['warehouse_id', $it_warehouse->warehouse_id]])->sum('quantity');
                $it_warehouse->stock = $inv_kardex;
                $it_warehouse->save();
            }
        });

        return [
            'success' => true,
            'message' => 'Stock regularizado'
        ];
    }

    private function getRecordsOptimized(Request $request)
    {
        $column = $request->input('column');
        $warehouse_id = $request->input('warehouse_id');
        $product_description = $request->input('product_description');
        $with_stock = $request->input('with_stock') == 'true';
        $records = ItemWarehouse::with(['item:id,internal_id,description', 'warehouse:id,description']);
        // Usar chunk para procesar grandes cantidades de datos
        $query = null;

        if ($column == 'warehouse') {
            $query = $records
                ->whereHas('item', function ($query) use ($request) {
                    $query->where('unit_type_id', '!=', 'ZZ');
                    $query->whereNotIsSet();
                })
                ->whereHas('warehouse', function ($query) use ($request) {
                    $query->where('description', 'like', '%' . $request->value . '%');
                });
        } else if ($request->column == 'warehouse_id' && $request->value) {
            $query = $records
                ->where('warehouse_id', $request->value);
        } else if ($request->column == 'item_id' && $request->value) {
            $query = $records
                ->where('item_id', $request->value);
        } else {
            if ($warehouse_id || $product_description) {
                $query = $records
                    ->when($warehouse_id, function ($query) use ($warehouse_id) {
                        $query->where('warehouse_id', $warehouse_id);
                    })
                    ->when($product_description, function ($query) use ($product_description) {
                        $query->whereHas('item', function ($query) use ($product_description) {
                            $query->where('description', 'like', '%' . $product_description . '%')
                                ->orWhere('internal_id', 'like', '%' . $product_description . '%');
                        });
                    });
            } else {
                $query = $this->getCommonRecordsOptimized($request);
            }
        }

        if ($with_stock) {
            $query = $query->where('stock', '>', 0);
        }

        // Limitar registros si es necesario
        $limit = $request->input('limit', 10000);
        $query->limit($limit);

        return $query->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'item_id' => $row->item_id,
                'item_internal_id' => $row->item->internal_id,
                'item_description' => $row->item->description,
                'item_fulldescription' => ($row->item->internal_id) ? "{$row->item->internal_id} - {$row->item->description}" : $row->item->description,
                'warehouse_description' => $row->warehouse->description,
                'warehouse_id' => $row->warehouse_id,
                'stock' => $row->stock,
                'created_at' => $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : null,
                'updated_at' => $row->updated_at ? $row->updated_at->format('Y-m-d H:i:s') : null,
            ];
        });
    }

    private function getCommonRecordsOptimized($request)
    {
        // Optimizar la consulta común para usar menos memoria
        return ItemWarehouse::with(['item:id,internal_id,description', 'warehouse:id,description'])
            ->whereHas('item', function ($query) {
                $query->where('unit_type_id', '!=', 'ZZ');
                $query->whereNotIsSet();
            });
    }
}
