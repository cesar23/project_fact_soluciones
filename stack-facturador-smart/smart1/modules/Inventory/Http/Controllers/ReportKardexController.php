<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\InitStockCollection;
use App\Models\Tenant\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Inventory\Exports\KardexExport;
use Illuminate\Http\Request;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Company;
use App\Models\Tenant\Kardex;
use App\Models\Tenant\Item;
use Carbon\Carbon;
use Modules\Inventory\Models\Guide;
use Modules\Inventory\Models\InventoryKardex;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Http\Resources\ReportKardexCollection;
use Modules\Inventory\Http\Resources\ReportKardexLotsCollection;

use Modules\Inventory\Models\ItemWarehouse;
use Modules\Item\Models\ItemLotsGroup;
use Modules\Item\Models\ItemLot;

use Modules\Inventory\Http\Resources\ReportKardexLotsGroupCollection;
use Modules\Inventory\Http\Resources\ReportKardexItemLotCollection;
use Modules\Inventory\Models\Devolution;
use App\Models\Tenant\Dispatch;
use App\Models\Tenant\DispatchItem;
use App\Models\Tenant\Document;
use App\Models\Tenant\DocumentItem;
use App\Models\Tenant\InitStock;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\PurchaseItem;
use App\Models\Tenant\PurchaseSettlement;
use App\Models\Tenant\PurchaseSettlementItem;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\SaleNoteItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Exports\KardexAttributesExport;
use Modules\Inventory\Http\Resources\ReportKardexAttributesCollection;
use Modules\Inventory\Models\DevolutionItem;
use Modules\Inventory\Models\Inventory;
use Modules\Item\Models\ItemProperty;
use Modules\Order\Models\OrderNote;
use Modules\Order\Models\OrderNoteItem;

class ReportKardexController extends Controller
{
    protected $models = [
        "App\Models\Tenant\Document",
        "App\Models\Tenant\Purchase",
        "App\Models\Tenant\PurchaseSettlement",
        "App\Models\Tenant\SaleNote",
        "Modules\Inventory\Models\Inventory",
        "Modules\Order\Models\OrderNote",
        Devolution::class,
        Dispatch::class
    ];



    public function avg()
    {
        $limit_date = '2022-12-31';
        //
        $item_id = 23;
        $init_cost = InventoryKardex::where('inventory_kardexable_type', 'Modules\Inventory\Models\Inventory')
            ->where('item_id', $item_id)
            ->where('date_of_issue', '<=', $limit_date)
            ->select('quantity')->first();
        $cost = 0;
        if ($init_cost->quantity != 0) {
            $item = Item::find($item_id);
            $cost = $item->purchase_unit_price;
        }
        $results = [];
        $last_date = null;
        $last_stock = 0;
        Purchase::without(['user', 'soap_type', 'state_type', 'document_type', 'currency_type', 'group', 'items', 'purchase_payments'])->join('purchase_items', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->where('purchase_items.item_id', '=', $item_id)
            ->where('date_of_issue', '<=', $limit_date)
            ->select('purchases.date_of_issue', 'purchases.id', 'purchase_items.quantity', 'purchase_items.unit_price')
            ->chunk(50, function ($purchases) use ($item_id, &$results, $cost, $last_date, &$last_stock) {
                foreach ($purchases as $purchase) {
                    $stock = InventoryKardex::where('item_id', $item_id);
                    if ($last_date) {
                        $stock = $stock->where('date_of_issue', '>', $purchase->date_of_issue);
                    } else {

                        $stock = $stock->whereBetween('date_of_issue', [$last_date, $purchase->date_of_issue]);
                    }
                    $stock = $stock->where(function ($query) {
                        $query->where(function ($query) {
                            $query->where('inventory_kardexable_type', '<>', 'App\Models\Tenant\Document')
                            ->where('inventory_kardexable_type', '<>', 'App\Models\Tenant\SaleNote');
                        })
                            ->orWhere(function ($query) {
                                $query->where('inventory_kardexable_type', 'App\Models\Tenant\Document')
                                    ->whereIn('inventory_kardexable_id', function ($query) {
                                        $query->select('id')
                                            ->from('documents')
                                            ->where(function ($query) {
                                                $query->whereNull('sale_note_id')
                                                    ->orWhere('sale_note_id', '=', '')
                                                    ->orWhere(function ($query) {
                                                        $query->whereNull('order_note_id')
                                                            ->orWhere('order_note_id', '=', '');
                                                    });
                                            });
                                    });
                            })->orWhere(function ($query) {
                                $query->where('inventory_kardexable_type', 'App\Models\Tenant\SaleNote')
                                    ->whereIn('inventory_kardexable_id', function ($query) {
                                        $query->select('id')
                                            ->from('sale_notes')
                                            ->where(function ($query) {
                                                $query->whereNull('order_note_id')
                                                    ->orWhere('order_note_id', '=', '');
                                            });
                                    });
                            });
                    })
                        ->sum("quantity");

                    $last_stock += $stock;
                    $last_date = Carbon::parse($purchase->date_of_issue)->format("Y-m-d");
                    $total_cost = $purchase->quantity * $purchase->unit_price + $cost;

                    $results[] = [
                        "stock" => $last_stock,
                        "last_date" => $last_date,
                        "total_cost" => $total_cost,
                        "id_purchase" => $purchase->id,
                    ];
                }
            });






        // Obtener todos los items con su stock y costo a la fecha límite
        // $items = DB::connection('tenant')
        //     ->table('purchase_items')
        //     ->select('purchase_items.item_id', DB::raw('SUM(inventory_kardex.quantity) AS stock'), DB::raw('SUM(inventory_kardex.quantity * purchase_items.unit_price) AS costo_total'))
        //     ->leftJoin('inventory_kardex', 'purchase_items.item_id', '=', 'inventory_kardex.item_id')
        //     ->where('inventory_kardex.date_of_issue', '<=', $limit_date)
        //     ->groupBy('purchase_items.item_id')
        //     ->get();



        return compact("init_cost", "results");
    }
    public function index()
    {
        return view('inventory::reports.kardex.index');
    }
    function get_all_dates($init_date = null, $addMonth = false)
    {
        $fechaInicio = $init_date ?? "2021-01-01";
        $hoy = now();
        $fecha = Carbon::createFromFormat('Y-m-d', $fechaInicio);
        $primerosDias = [];
        while ($fecha <= $hoy) {

            $fecha = $fecha->firstOfMonth();
            if ($addMonth) {
                $fecha = $fecha
                    ->addMonth();
            }
            $primerosDias[] = $fecha->format('Y-m-d');
            $fecha->addMonthNoOverflow();
        }

        return $primerosDias;
    }
    function get_last_day($date)
    {

        $carbonFecha = Carbon::createFromFormat('Y-m-d', $date);

        $ultimoDiaMes = $carbonFecha->endOfMonth();

        return $ultimoDiaMes->format('Y-m-d'); // muestra '20
    }
    //item_adjustment
    public function item_adjustment(Request $request)
    {
        $item_id = $request->item_id;
        $warehouse_id = $request->warehouse_id;

        $warehouse_item = ItemWarehouse::where(['item_id' => $item_id, "warehouse_id" => $warehouse_id])
            ->first();
        $init_stock = InitStock::where(['item_id' => $item_id, "warehouse_id" => $warehouse_id])->latest('init_date')->first();
        $count = InitStock::where(['item_id' => $item_id, "warehouse_id" => $warehouse_id])->count();
        $one_register = false;
        if ($count == 1) {
            $one_register = true;
        }
        if ($warehouse_item && $init_stock) {
            $current_stock = $warehouse_item->stock;
            $month_stock = (float) $init_stock->stock;
            $today = Carbon::now()->format('Y-m-d');

            $sum = InventoryKardex::where(['item_id' => $item_id, "warehouse_id" => $warehouse_id])->whereBetween('date_of_issue', [$init_stock->init_date, $today])
                ->where(function ($query) {
                    $query->where(function ($query) {
                        $query->where('inventory_kardexable_type', '<>', 'App\Models\Tenant\Document')
                        ->where('inventory_kardexable_type', '<>', 'App\Models\Tenant\SaleNote');
                    })
                        ->orWhere(function ($query) {
                            $query->where('inventory_kardexable_type', 'App\Models\Tenant\Document')
                                ->whereIn('inventory_kardexable_id', function ($query) {
                                    $query->select('id')
                                        ->from('documents')
                                        ->where(function ($query) {
                                            $query->whereNull('sale_note_id')
                                                ->orWhere('sale_note_id', '=', '')
                                                ->where(function ($query) {
                                                    $query->whereNull('order_note_id')
                                                        ->orWhere('order_note_id', '=', '');
                                                });
                                        });
                                });
                        })->orWhere(function ($query) {
                            $query->where('inventory_kardexable_type', 'App\Models\Tenant\SaleNote')
                                ->whereIn('inventory_kardexable_id', function ($query) {
                                    $query->select('id')
                                        ->from('sale_notes')
                                        ->where(function ($query) {
                                            $query->whereNull('order_note_id')
                                                ->orWhere('order_note_id', '=', '');
                                        });
                                });
                        });
                })
                ->sum("quantity");
                
              
                
            if($month_stock < 0 && $one_register){
              
                $month_stock = 0;
            }
            else if($one_register){
                $month_stock = 0;
            }
            $stock = $month_stock + $sum;

            return [
                "success" => $stock == $current_stock,
                "warehouse_description" => $warehouse_item->warehouse->description,
                "stock" => $warehouse_item->stock,
                "correct_stock" => $stock,

            ];
        }

        // Log::info("item_id: ".$item_id."- warehouse_id: ".$warehouse_id);

        return ["success" => false];
    }
    public function stock_adjustment(Request $request)
    {
        $item_id = $request->item_id;
        $warehouse_id = $request->warehouse_id;
        $correct_stock = $request->correct_stock;
        $last_stock = InitStock::where('item_id', $item_id)
            ->where('warehouse_id', $warehouse_id)
            ->latest('init_date')->first();

        if (!$last_stock) {
            return ["success" => false, "message" => "Sin stock inicial guardado"];
        }
        $stock_warehouse = $correct_stock;

        ItemWarehouse::where('item_id', $item_id)
            ->where('warehouse_id', $warehouse_id)
            ->update(['stock' => $stock_warehouse]);
        $sum = ItemWarehouse::where('item_id', $item_id)
            ->sum('stock');
        Item::where('id', $item_id)->update(["stock" => $sum]);

        return ["success" => true, "message" => "Stock ajustado"];
    }

    public function get_all_init_stock_by_month(Request $request)
    {
        $page = $request->page;
        $item_id = $request->item_id;
        $warehouse_id = $request->warehouse_id ?? null;
        if ($page == null || $page == 0 || $page == 1) {
            $this->get_init_stock($request);
        }
        $records = InitStock::where('item_id', $item_id)->where('warehouse_id', $warehouse_id)
            ->orderBy('init_date', 'DESC');


        return new InitStockCollection($records->paginate(20));
    }
    function get_init_stock(Request $request)
    {
        $item_id = $request->item_id;
        $warehouse_id = $request->warehouse_id ?? null;
        $lastStock = InitStock::where('item_id', $item_id);

        if ($warehouse_id) {
            $lastStock = $lastStock->where('warehouse_id', $warehouse_id);
        } else {
            $lastStock = $lastStock->whereNull($warehouse_id);
        }
        $lastStock =   $lastStock->latest('init_date')->first();
        $lastDate = null;
        if ($lastStock) {
            $lastDate = $lastStock->init_date;
        }
        $stocks = [];



        if (!isset($lastDate)) {
            $init_date = InventoryKardex::where('item_id', $item_id);
            if ($warehouse_id && $warehouse_id != "all") {
                $init_date = $init_date->where('warehouse_id', $warehouse_id);
            }
            $init_date = $init_date->first();



            if (!isset($init_date)) {
                return ["success" => false, "msg" => "Error"];
            }
            $date = $init_date->date_of_issue;
            $init_stock = $init_date->quantity;

            $result = $this->get_all_dates($date);
            if (count($result) != 0) {
                foreach ($result as $key => $date) {
                    $last_date = $this->get_last_day($date);
                    $sum = InventoryKardex::where('item_id', $item_id);
                    if ($warehouse_id && $warehouse_id != 'all') {
                        $sum = $sum->where('warehouse_id', $warehouse_id);
                    }
                    // Log::info($);

                    $sum = $sum->whereBetween('date_of_issue', [$date, $last_date])
                        ->where(function ($query) use ($date) {
                            $query->where(function ($query) {
                                $query->where('inventory_kardexable_type', '<>', 'App\Models\Tenant\Document')
                                ->where('inventory_kardexable_type', '<>', 'App\Models\Tenant\SaleNote');
                            })
                                ->orWhere(function ($query) {
                                    $query->where('inventory_kardexable_type', 'App\Models\Tenant\Document')
                                        ->whereIn('inventory_kardexable_id', function ($query) {
                                            $query->select('id')
                                                ->from('documents')
                                                ->where(function ($query) {
                                                    $query->whereNull('sale_note_id')
                                                        ->orWhere('sale_note_id', '=', '')
                                                        ->where(function ($query) {
                                                            $query->whereNull('order_note_id')
                                                                ->orWhere('order_note_id', '=', '');
                                                        });
                                                });
                                        });
                                })->orWhere(function ($query) {
                                    $query->where('inventory_kardexable_type', 'App\Models\Tenant\SaleNote')
                                        ->whereIn('inventory_kardexable_id', function ($query) {
                                            $query->select('id')
                                                ->from('sale_notes')
                                                ->where(function ($query) {
                                                    $query->whereNull('order_note_id')
                                                        ->orWhere('order_note_id', '=', '');
                                                });
                                        });
                                });
                        })
                        ->sum("quantity");
                    
                    
                    if ($key != 0) {
                        $stocks[$date] = $stocks[$result[$key - 1]] + $sum;
                        $initStock = new InitStock([
                            'item_id' => $item_id,
                            'warehouse_id' => $warehouse_id,
                            'init_date' => $date,
                            'stock' => floatval($stocks[$result[$key - 1]]),
                        ]);
                        $initStock->save();
                    } else {
                        $stocks[$date] = floatval($sum);
                        $initStock = new InitStock([
                            'item_id' => $item_id,
                            'warehouse_id' => $warehouse_id,
                            'init_date' => $date,
                            'stock' => floatval($init_stock),
                        ]);
                        $initStock->save();
                    }
                }
            }
        } else {
            $result = $this->get_all_dates($lastDate->format("Y-m-d"));
            if (count($result) != 0) {
                foreach ($result as $key => $date) {
                    $last_date = $this->get_last_day($date);
                    $sum = InventoryKardex::where('item_id', $item_id);
                    if ($warehouse_id && $warehouse_id != 'all') {
                        $sum = $sum->where('warehouse_id', $warehouse_id);
                    }

                    $sum = $sum->whereBetween('date_of_issue', [$date, $last_date])
                        ->where(function ($query) use ($date) {
                            $query->where(function ($query) {
                                $query->where('inventory_kardexable_type', '<>', 'App\Models\Tenant\Document')
                                ->where('inventory_kardexable_type', '<>', 'App\Models\Tenant\SaleNote');
                            })
                                ->orWhere(function ($query) {
                                    $query->where('inventory_kardexable_type', 'App\Models\Tenant\Document')
                                        ->whereIn('inventory_kardexable_id', function ($query) {
                                            $query->select('id')
                                                ->from('documents')
                                                ->where(function ($query) {
                                                    $query->whereNull('sale_note_id')
                                                        ->orWhere('sale_note_id', '=', '')
                                                        ->where(function ($query) {
                                                            $query->whereNull('order_note_id')
                                                                ->orWhere('order_note_id', '=', '');
                                                        });
                                                });
                                        });
                                })->orWhere(function ($query) {
                                    $query->where('inventory_kardexable_type', 'App\Models\Tenant\SaleNote')
                                        ->whereIn('inventory_kardexable_id', function ($query) {
                                            $query->select('id')
                                                ->from('sale_notes')
                                                ->where(function ($query) {
                                                    $query->whereNull('order_note_id')
                                                        ->orWhere('order_note_id', '=', '');
                                                });
                                        });
                                });
                        })
                        ->sum("quantity");
                    if ($key != 0) {
                        $stocks[$date] = $stocks[$result[$key - 1]] + $sum;
                        $initStock = new InitStock([
                            'item_id' => $item_id,
                            'warehouse_id' => $warehouse_id,
                            'init_date' => $date,
                            'stock' => floatval($stocks[$result[$key - 1]]),
                        ]);
                        $initStock->save();
                    } else {
                        $stocks[$date] = floatval($sum) + floatval($lastStock->stock);
                        // $initStock = new InitStock([
                        //     'item_id' => $item_id,
                        //     'warehouse_id' => $warehouse_id,
                        //     'init_date' => $date,
                        //     'stock' => $stocks[$date],
                        // ]);
                        // $initStock->save();
                    }
                }
            }
        }
    }
    public function filter()
    {
        $warehouses = [];
        $user = User::query()->find(auth()->id());
        if ($user->type === 'admin'|| $user->type === 'superadmin') {
            $warehouses[] = [
                'id' => 'all',
                'name' => 'Todos'
            ];
            $records = Warehouse::query()
                ->get();
        } else {
            $records = Warehouse::query()
                ->where('establishment_id', $user->establishment_id)
                ->get();
        }

        foreach ($records as $record) {
            $warehouses[] = [
                'id' => $record->id,
                'name' => $record->description,
            ];
        }

        return [
            'warehouses' => $warehouses
        ];
    }

    public function filterByWarehouse($warehouse_id)
    {
        $query = Item::query()->whereNotIsSet()
            ->with('warehouses')
            ->where([['item_type_id', '01'], ['unit_type_id', '!=', 'ZZ']]);

        if ($warehouse_id !== 'all') {
            $query->whereHas('warehouses', function ($query) use ($warehouse_id) {
                return $query->where('warehouse_id', $warehouse_id);
            });
        }

        $items = $query->latest()
            ->get()
            ->transform(function ($row) {
                $full_description = $this->getFullDescription($row);
                return [
                    'id' => $row->id,
                    'full_description' => $full_description,
                    'internal_id' => $row->internal_id,
                    'description' => $row->description,
                    'warehouses' => $row->warehouses
                ];
            });

        return [
            'items' => $items
        ];
    }

    public function records(Request $request)
    {
        $records = $this->getRecords($request->all());

        return new ReportKardexCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function records_lots()
    {
        $records = ItemWarehouse::with(['item'])->whereHas('item', function ($q) {
            $q->where([['item_type_id', '01'], ['unit_type_id', '!=', 'ZZ'], ['lot_code', '!=', null]]);
            $q->whereNotIsSet();
        });

        return new ReportKardexLotsCollection($records->paginate(config('tenant.items_per_page')));
    }


    /**
     * @param $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|InventoryKardex
     */
    public function getRecords($request)
    {
        $this->restoreFromProX();
        $warehouse_id = $request['warehouse_id'];
        $item_id = $request['item_id'];
        $date_start = $request['date_start'];
        $date_end = $request['date_end'];
        
        $records = $this->data($item_id, $warehouse_id, $date_start, $date_end);

        return $records;
    }
    function restoreFromProX(){
         InventoryKardex::where('inventory_kardexable_type', 'Modules\Document\Models\Document')
        ->update(['inventory_kardexable_type' => 'App\Models\Tenant\Document']);
        InventoryKardex::where('inventory_kardexable_type', 'Modules\Purchase\Models\Purchase')
        ->update(['inventory_kardexable_type' => 'App\Models\Tenant\Purchase']);
        InventoryKardex::where('inventory_kardexable_type', 'Modules\SaleNote\Models\SaleNote')
        ->update(['inventory_kardexable_type' => 'App\Models\Tenant\SaleNote']);
    }

    /**
     * @param $item_id
     * @param $date_start
     * @param $date_end
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|InventoryKardex
     */
    private function data($item_id, $warehouse_id, $date_start, $date_end)
    {
        //$warehouse = Warehouse::where('establishment_id', auth()->user()->establishment_id)->first();

        $data = InventoryKardex::with(['inventory_kardexable']);
        if ($warehouse_id !== 'all') {
            $data->where('warehouse_id', $warehouse_id);
        }
        if ($date_start) {
            $data->where('date_of_issue', '>=', $date_start);
        }
        if ($date_end) {
            $data->where('date_of_issue', '<=', $date_end);
        }
        if ($item_id) {
            $data->where('item_id', $item_id);
        }
        $data
            ->orderBy('item_id')
            ->orderBy('id')
            ->get()->transform(function ($row) {
                return $row->getCollectionData();
            });


        return $data;
    }

    private function formatItems($item){
        $quantity = isset($item->quantity) ? $item->quantity : 0;
        $unit_price = isset($item->unit_price) ? $item->unit_price : 0;
        $description = isset($item->item->description) ? $item->item->description : '';
        return [
            'quantity' => number_format($quantity, 2, '.', ''),
            'unit_price' => number_format($unit_price, 2, '.', ''),
            'description' => $description
        ];
    }

    public function delete($id)
    {
        $inventory_kardex = InventoryKardex::where('id', $id)->first();
        $inventory_kardex->delete();
        return [
            'success' => true,
            'message' => 'Registro eliminado correctamente'
        ];
    }

    public function getItems($inventoryKardexId)
    {
        $inventory_kardex = InventoryKardex::where('id', $inventoryKardexId)->first();
        $inventory_kardex_type = $inventory_kardex->inventory_kardexable_type;
        $item_id = $inventory_kardex->item_id;
        $items = [];
        switch ($inventory_kardex_type) {
            case Document::class:
                $items = DocumentItem::where('document_id', $inventory_kardex->inventory_kardexable_id)->get()->transform(function ($item) {
                    return $this->formatItems($item);
                });
                break;
            case SaleNote::class:
                $items = SaleNoteItem::where('sale_note_id', $inventory_kardex->inventory_kardexable_id)->get()->transform(function ($item) {
                    return $this->formatItems($item);
                });
                break;
            case Purchase::class:
                $items = PurchaseItem::where('purchase_id', $inventory_kardex->inventory_kardexable_id)->get()->transform(function ($item) {
                    return $this->formatItems($item);
                });
                break;  
            case Inventory::class:
                $inventory = Inventory::where('id', $inventory_kardex->inventory_kardexable_id)->first();
                $inventories_transfer_id = $inventory->inventories_transfer_id;
                if($inventories_transfer_id){
                    $inventories = Inventory::where('inventories_transfer_id', $inventories_transfer_id)->get()->transform(function ($item) {
                        return $this->formatItems($item);
                    });
                    $items = $inventories;
                }else{
                    $item = Item::select('description')->where('id', $item_id)->first();
                    $items = [
                        [
                            'quantity' => $inventory->quantity,
                            'unit_price' => 0,
                            'description' => $item->description
                        ]
                    ];
                }
                // $items = InventoryItem::where('inventory_id', $items->inventory_kardexable_id)->first();
                break;
            case OrderNote::class:
                $items = OrderNoteItem::where('order_note_id', $inventory_kardex->inventory_kardexable_id)->get()->transform(function ($item) {
                    return $this->formatItems($item);
                });
                break;
            case Devolution::class:
                $items = DevolutionItem::where('devolution_id', $inventory_kardex->inventory_kardexable_id)->get()->transform(function ($item) {
                    return $this->formatItems($item);
                });
                break;
            case Dispatch::class:
                $items = DispatchItem::where('dispatch_id', $inventory_kardex->inventory_kardexable_id)->get()->transform(function ($item) {
                    return $this->formatItems($item);
                });
                break;
            case PurchaseSettlement::class:
                $items = PurchaseSettlementItem::where('purchase_settlement_id', $inventory_kardex->inventory_kardexable_id)->get()->transform(function ($item) {
                    return $this->formatItems($item);
                });
                break;
            default:
                $items = [];
                break;
        }
        return $items;
    }

    public function getFullDescription($row)
    {
        $desc = ($row->internal_id) ? $row->internal_id . ' - ' . $row->description : $row->description;
        $category = ($row->category) ? " - {$row->category->name}" : "";
        $brand = ($row->brand) ? " - {$row->brand->name}" : "";

        $desc = "{$desc} {$category} {$brand}";

        return $desc;
    }


    private function getData($request)
    {
        $company = Company::query()->first();
        $warehouse_id = $request->warehouse_id;
        if ($warehouse_id && $warehouse_id != 'all') {
            $establishment =  Establishment::find($request->warehouse_id);
        } else {
            $establishment =  Establishment::query()->find(auth()->user()->establishment_id);
        }
        $date_start = $request->input('date_start');
        $date_end = $request->input('date_end');
        $item_id = $request->input('item_id');
        $item = Item::query()->findOrFail($request->input('item_id'));

        $warehouse = Warehouse::query()
            ->where('establishment_id', $establishment->id)
            ->first();

        $query = InventoryKardex::query()
            ->with(['inventory_kardexable']);
        if ($warehouse_id && $warehouse_id != 'all') {
            $query = $query->where('warehouse_id', $warehouse->id);
        }

        if ($date_start && $date_end) {
            $query->whereBetween('date_of_issue', [$date_start, $date_end])
                ->orderBy('item_id')->orderBy('id');
        }

        if ($item_id) {
            $query->where('item_id', $item_id);
        }

        $records = $query->orderBy('item_id')
            ->orderBy('id')
            ->get();

        return [
            'company' => $company,
            'establishment' => $establishment,
            'warehouse' => $warehouse,
            'item_id' => $item_id,
            'item' => $item,
            'models' => $this->models,
            'date_start' => $date_start,
            'date_end' => $date_end,
            'records' => $records,
            'balance' => 0,
        ];
    }

    /**
     * PDF
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function pdf(Request $request)
    {
        $data = $this->getData($request);

        $pdf = PDF::loadView('inventory::reports.kardex.report_pdf', $data)
        ->setPaper('a4', 'landscape');
        $filename = 'Reporte_Kardex' . date('YmdHis');

        return $pdf->stream($filename . '.pdf');
    }

    /**
     * Excel
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function excel(Request $request)
    {

        $date_start = Carbon::parse($request->date_start)->startOfMonth()->format('Y-m-d');
        $init_balance = 0;
        $init_stock = InitStock::where('item_id', $request->item_id)
            ->where('warehouse_id', $request->warehouse_id)
            ->where('init_date', $date_start)->first();
        if ($init_stock) {
            if ($date_start == $request->date_start) {
                $init_balance = $init_stock->stock;
            } else {
                $sum = InventoryKardex::where('item_id', $request->item_id)
                    ->where('warehouse_id', $request->warehouse_id)
                    ->whereBetween('date_of_issue', [$date_start, Carbon::parse($request->date_start)->subDay()->format('Y-m-d')])
                    ->where(function ($query) {
                        $query->where(function ($query) {
                            $query->where('inventory_kardexable_type', '<>', 'App\Models\Tenant\Document')
                            ->where('inventory_kardexable_type', '<>', 'App\Models\Tenant\SaleNote');
                        })
                            ->orWhere(function ($query) {
                                $query->where('inventory_kardexable_type', 'App\Models\Tenant\Document')
                                    ->whereIn('inventory_kardexable_id', function ($query) {
                                        $query->select('id')
                                            ->from('documents')
                                            ->where(function ($query) {
                                                $query->whereNull('sale_note_id')
                                                    ->orWhere('sale_note_id', '=', '')
                                                    ->orWhere(function ($query) {
                                                        $query->whereNull('order_note_id')
                                                            ->orWhere('order_note_id', '=', '');
                                                    });
                                            });
                                    });
                            })->orWhere(function ($query) {
                                $query->where('inventory_kardexable_type', 'App\Models\Tenant\SaleNote')
                                    ->whereIn('inventory_kardexable_id', function ($query) {
                                        $query->select('id')
                                            ->from('sale_notes')
                                            ->where(function ($query) {
                                                $query->whereNull('order_note_id')
                                                    ->orWhere('order_note_id', '=', '');
                                            });
                                    });
                            });
                    })
                    ->sum("quantity");

                $init_balance = $init_stock->stock + $sum;
            }
        }
        $data = $this->getData($request);
        $kardexExport = new KardexExport();
        $kardexExport
            ->init_balance($init_balance)
            ->balance($data['balance'])
            ->item_id($data['item_id'])
            ->records($data['records'])
            ->models($data['models'])
            ->company($data['company'])
            ->establishment($data['establishment'])
            ->item($data['item']);

        return $kardexExport->download('ReporteKar' . Carbon::now() . '.xlsx');
    }

    public function getRecords2($request)
    {
        $item_id = $request['item_id'];
        $date_start = $request['date_start'];
        $date_end = $request['date_end'];
        $warehouse_id = $request['warehouse_id'];
        $records = $this->data2($item_id, $date_start, $date_end,$warehouse_id);

        return $records;
    }

    private function data2($item_id, $date_start, $date_end,$warehouse_id)
    {

        // $warehouse = Warehouse::where('establishment_id', auth()->user()->establishment_id)->first();

        if ($date_start && $date_end) {

            $data = ItemLotsGroup::whereBetween('date_of_due', [$date_start, $date_end])
                ->orderBy('item_id')->orderBy('id');
        } else {

            $data = ItemLotsGroup::orderBy('item_id')->orderBy('id');
        }

        if ($item_id) {
            $data = $data->where('item_id', $item_id);
        }

        if ($warehouse_id != 'all' && $warehouse_id != null) {
            $data = $data->where('warehouse_id', $warehouse_id);
        }


        return $data;
    }

    public function records_lots_kardex(Request $request)
    {
        $records = $this->getRecords2($request->all());

        return new ReportKardexLotsGroupCollection($records->paginate(config('tenant.items_per_page')));
    }
    public function records_attributes_kardex(Request $request)
    {
        $records = $this->getRecords4($request->all());

        return new ReportKardexAttributesCollection($records->paginate(config('tenant.items_per_page')));
    }
    private function data4($item_id, $has_sale,$warehouse_id, $date_start, $date_end,$chassis)
    {
        $data = ItemProperty::query();
        if ($item_id) {
            $data->where('item_id', $item_id);
        }
        if($has_sale!="all"){
            $data->where('has_sale', $has_sale);
        } 
        if($warehouse_id!='all'){
            $data->where('warehouse_id', $warehouse_id);
        }
      
        if ($date_start && $date_end) {
            $data->whereBetween('updated_at', [$date_start, $date_end]);
        }

        if ($chassis) {
            $data = $data->where('item_id', $item_id);
        }

       
    
        if ($warehouse_id!='all'){ 
            $data = $data->where('warehouse_id', $warehouse_id);
        }
        return $data->orderBy('id');
             
    }
    public function getRecords4($request)
    {
        $item_id = $request['item_id'];
        $has_sale = $request['has_sale'];
        $warehouse_id = $request['warehouse_id'];
        $date_start = $request['date_start'];
        $date_end = $request['date_end'];
        $chassis = $request['chassis'];
        $records = $this->data4($item_id, $has_sale, $warehouse_id, $date_start, $date_end,$chassis);

        return $records;
    }
    public function export_attributes_pdf (Request $request ){
        $data = $this->getData($request);
        $item = $data['item'];
        $company = $data['company'];
        $establishment =$data['establishment'];
        $records = $this->getRecords4($request->all())->get();
 
        $pdf = PDF::loadView('inventory::reports.kardex.report_attributes_pdf', compact('records','company','establishment','item'));
        $filename = 'Reporte_Kardex_Atributos' . date('YmdHis');
        return $pdf->stream($filename . '.pdf');
    }
    public function export_attributes_excel(Request $request){
        $data = $this->getData($request);
        $kardexExport = new KardexAttributesExport();
        $records = $this->getRecords4($request->all())->get();
        $kardexExport
            ->records($records)
            ->company($data['company'])
            ->establishment($data['establishment'])
            ->item($data['item']);

        return $kardexExport->download('ReporteKar' . Carbon::now() . '.xlsx');
     }
    public function getRecords3($request)
    {

        $item_id = $request['item_id'];
        $date_start = $request['date_start'];
        $date_end = $request['date_end'];

        $records = $this->data3($item_id, $date_start, $date_end);

        return $records;
    }


    private function data3($item_id, $date_start, $date_end)
    {

        // $warehouse = Warehouse::where('establishment_id', auth()->user()->establishment_id)->first();

        if ($date_start && $date_end) {

            $data = ItemLot::whereBetween('date', [$date_start, $date_end])
                ->orderBy('item_id')->orderBy('id');
        } else {

            $data = ItemLot::orderBy('item_id')->orderBy('id');
        }

        if ($item_id) {
            $data = $data->where('item_id', $item_id);
        }


        return $data;
    }

    public function records_series_kardex(Request $request)
    {

        $records = $this->getRecords3($request->all());

        return new ReportKardexItemLotCollection($records->paginate(config('tenant.items_per_page')));

        /*$records = [];

        if($item)
        {
            $records  =  ItemLot::where('item_id', $item)->get();

        }
        else{
            $records  = ItemLot::all();
        }

       // $records  =  ItemLot::all();
        return new ReportKardexItemLotCollection($records);*/
    }




    // public function search(Request $request) {
    //     //return $request->item_selected;
    //     $balance = 0;
    //     $d = $request->d;
    //     $a = $request->a;
    //     $item_selected = $request->item_selected;

    //     $items = Item::query()->whereNotIsSet()
    //         ->where([['item_type_id', '01'], ['unit_type_id', '!=','ZZ']])
    //         ->latest()
    //         ->get();

    //     $warehouse = Warehouse::where('establishment_id', auth()->user()->establishment_id)->first();

    //     if($d && $a){

    //         $reports = InventoryKardex::with(['inventory_kardexable'])
    //                     ->where([['item_id', $request->item_selected],['warehouse_id', $warehouse->id]])
    //                     ->whereBetween('date_of_issue', [$d, $a])
    //                     ->orderBy('id')
    //                     ->paginate(config('tenant.items_per_page'));

    //     }else{

    //         $reports = InventoryKardex::with(['inventory_kardexable'])
    //                     ->where([['item_id', $request->item_selected],['warehouse_id', $warehouse->id]])
    //                     ->orderBy('id')
    //                     ->paginate(config('tenant.items_per_page'));

    //     }

    //     //return json_encode($reports);

    //     $models = $this->models;

    //     return view('inventory::reports.kardex.index', compact('items', 'reports', 'balance','models', 'a', 'd','item_selected'));
    // }
    public function getPdfGuide($guide_id)
    {
        $company = Company::query()->first();

        $record = Guide::query()
            ->with('inventory_transaction', 'warehouse', 'document_type', 'items', 'items.item')
            ->find($guide_id);


        $items = [];
        foreach ($record->items as $item) {
            $lot = ItemLotsGroup::where('item_id', $item->item_id)->where('created_at', $record->created_at)->first();
            $items[] = [
                'item_internal_id' => $item->item->internal_id,
                'item_name' => $item->item_name,
                'unit_type_id' => $item->item->unit_type_id,
                'unit_type_symbol' => $item->item->unit_type->symbol,
                'quantity' => $item->quantity,
                'lot' => $lot ? $lot->code : null,
            ];
        }

        $reference = null;
        if($record->inventory){
            $inventory = $record->inventory;
            $reference = optional($inventory->inventory_reference)->description;
        }
        $data = [
            'reference' => $reference,
            'company_number' => $company->number,
            'company_name' => $company->name,
            'document_type_name' => $record->document_type->description,
            'document_number' => $record->series . '-' . $record->number,
            'document_date_of_issue' => $record->date_of_issue->format('d/m/Y'),
            'document_time_of_issue' => $record->time_of_issue,
            'warehouse_name' => $record->warehouse->description,
            'transaction_name' => $record->inventory_transaction->name,
            'items' => $items
        ];

        $pdf = PDF::loadView('inventory::reports.kardex.guide', $data);
        $pdf->setPaper('A4', 'portrait');
        // $pdf->setPaper('A4', 'landscape');
        $filename = 'Guia_' . date('YmdHis');

        return $pdf->stream($filename . '.pdf');
    }

    /**
     * Método optimizado para ajuste de inventario de item
     * Basado en item_adjustment pero con consultas optimizadas usando DB::connection
     */
    public function item_adjustment_optimized(Request $request)
    {
        try {
            $item_id = $request->item_id;
            $warehouse_id = $request->warehouse_id;

            // Validar parámetros requeridos
            if (!$item_id || !$warehouse_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'item_id y warehouse_id son requeridos'
                ], 400);
            }

            // Obtener warehouse_item y stock inicial con consultas optimizadas
            $warehouse_item = DB::connection('tenant')
                ->table('item_warehouse as iw')
                ->join('warehouses as w', 'iw.warehouse_id', '=', 'w.id')
                ->where(['iw.item_id' => $item_id, 'iw.warehouse_id' => $warehouse_id])
                ->select('iw.stock', 'w.description as warehouse_description')
                ->first();

            if (!$warehouse_item) {
                return ['success' => false, 'message' => 'Item no encontrado en almacén'];
            }

            $init_stock = DB::connection('tenant')
                ->table('init_stock')
                ->where(['item_id' => $item_id, 'warehouse_id' => $warehouse_id])
                ->orderByDesc('init_date')
                ->select('stock', 'init_date')
                ->first();

            if (!$init_stock) {
                return ['success' => false, 'message' => 'Stock inicial no encontrado'];
            }

            // Verificar si es el único registro de stock inicial
            $count = DB::connection('tenant')
                ->table('init_stock')
                ->where(['item_id' => $item_id, 'warehouse_id' => $warehouse_id])
                ->count();

            $one_register = ($count == 1);
            $current_stock = $warehouse_item->stock;
            $month_stock = (float) $init_stock->stock;
            $today = Carbon::now()->format('Y-m-d');

            // Consulta optimizada para sumar movimientos de kardex
            $sum = DB::connection('tenant')
                ->table('inventory_kardex as ik')
                ->where('ik.item_id', $item_id)
                ->where('ik.warehouse_id', $warehouse_id)
                ->whereBetween('ik.date_of_issue', [$init_stock->init_date, $today])
                ->where(function ($query) {
                    // Excluir documentos de venta (Documents y SaleNotes)
                    $query->where(function ($query) {
                        $query->where('ik.inventory_kardexable_type', '<>', 'App\Models\Tenant\Document')
                              ->where('ik.inventory_kardexable_type', '<>', 'App\Models\Tenant\SaleNote');
                    })
                    // Incluir Documents que NO tengan sale_note_id ni order_note_id
                    ->orWhere(function ($query) {
                        $query->where('ik.inventory_kardexable_type', 'App\Models\Tenant\Document')
                              ->whereExists(function ($subQuery) {
                                  $subQuery->select(DB::raw(1))
                                          ->from('documents as d')
                                          ->whereColumn('d.id', 'ik.inventory_kardexable_id')
                                          ->where(function ($innerQuery) {
                                              $innerQuery->whereNull('d.sale_note_id')
                                                        ->orWhere('d.sale_note_id', '')
                                                        ->where(function ($deepQuery) {
                                                            $deepQuery->whereNull('d.order_note_id')
                                                                     ->orWhere('d.order_note_id', '');
                                                        });
                                          });
                              });
                    })
                    // Incluir SaleNotes que NO tengan order_note_id
                    ->orWhere(function ($query) {
                        $query->where('ik.inventory_kardexable_type', 'App\Models\Tenant\SaleNote')
                              ->whereExists(function ($subQuery) {
                                  $subQuery->select(DB::raw(1))
                                          ->from('sale_notes as sn')
                                          ->whereColumn('sn.id', 'ik.inventory_kardexable_id')
                                          ->where(function ($innerQuery) {
                                              $innerQuery->whereNull('sn.order_note_id')
                                                        ->orWhere('sn.order_note_id', '');
                                          });
                              });
                    });
                })
                ->sum('ik.quantity');

            // Ajustar stock inicial si es necesario
            if ($month_stock < 0 && $one_register) {
                $month_stock = 0;
            } else if ($one_register) {
                $month_stock = 0;
            }

            $correct_stock = $month_stock + $sum;

            return response()->json([
                'success' => $correct_stock == $current_stock,
                'warehouse_description' => $warehouse_item->warehouse_description,
                'stock' => $current_stock,
                'correct_stock' => $correct_stock,
                'month_stock' => $month_stock,
                'movements_sum' => $sum,
                'one_register' => $one_register,
                'count_init_stocks' => $count,
                'date_range' => [
                    'from' => $init_stock->init_date,
                    'to' => $today
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en ajuste de item: ' . $e->getMessage()
            ], 500);
        }
    }
}
