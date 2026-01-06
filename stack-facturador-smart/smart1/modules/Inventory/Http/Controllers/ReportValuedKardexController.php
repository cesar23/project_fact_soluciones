<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Inventory\Exports\ValuedKardexExport;
use Illuminate\Http\Request;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Company;
use App\Models\Tenant\DocumentItem;
use App\Models\Tenant\InitStock;
use App\Models\Tenant\Item;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\PurchaseItem;
use App\Models\Tenant\SaleNoteItem;
use App\Models\Tenant\SunatStock;
use App\Services\SunatStockService;
use App\Traits\CostAverageTrait;
use Carbon\Carbon;
use Modules\Inventory\Exports\StockHistoryExport;
use Modules\Inventory\Http\Resources\ReportValuedKardexCollection;
use Modules\Report\Traits\ReportTrait;
use Modules\Inventory\Helpers\InventoryValuedKardex;
use Modules\Inventory\Exports\ValuedKardexFormatSunatExport;
use Modules\Inventory\Models\Inventory;
use Modules\Inventory\Models\InventoryKardex;
use Modules\Order\Models\OrderNoteItem;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportValuedKardexController extends Controller
{

    use ReportTrait, CostAverageTrait;

    public function filter()
    {

        $establishments = Establishment::whereActive()->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'name' => $row->description
            ];
        });

        return compact('establishments');
    }


    public function stock_date()
    {
        return view('inventory::reports.valued_kardex.stock_date');
    }
    public function index()
    {

        return view('inventory::reports.valued_kardex.index');
    }


    public function records(Request $request)
    {
        $records = $this->getRecords($request->all());

        return new ReportValuedKardexCollection($records->paginate(config('tenant.items_per_page')));
    }


    public function getRecords($request)
    {

        $data_of_period = $this->getDataOfPeriod($request);

        $params = (object)[
            'establishment_id' => $request['establishment_id'] ?? 0,
            'date_start' => $data_of_period['d_start'],
            'date_end' => $data_of_period['d_end'],
        ];
        if (isset($request['stablishmentKardexAll']) && $request['stablishmentKardexAll'] == 1) {
            $params = (object)[
                'date_start' => $data_of_period['d_start'],
                'date_end' => $data_of_period['d_end'],
            ];
        }
        $records = $this->data($params);

        return $records;
    }


    /**
     * @param object $params
     * @return Item|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    private function data($params)
    {
        return Item::whereFilterValuedKardex($params)
            ->whereNotService()
            ->orderBy('description');
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
    function get_stock_between_dates($date, $last_date, $item_id, $warehouse_id = null)
    {
        // $sum = InventoryKardex::where('item_id', $item_id);
        // if ($warehouse_id) {
        //     $sum = $sum->where('warehouse_id', $warehouse_id);
        // } else {
        //     $sum = $sum->whereNull('warehouse_id');
        // }
        $sum = $this->sum_quantity($item_id, $date, $last_date);

        return $sum;
    }
    function sum_quantity($item_id, $date, $last_date)
    {

        if (gettype($last_date) == 'string') {
            $last_date = Carbon::parse($last_date);
        }
        if (gettype($date) == 'string') {
            $date = Carbon::parse($date);
        }
        //sale_notes
        $sum_sale_notes = SaleNoteItem::where('item_id', $item_id)
            ->whereHas('sale_note', function ($query) use ($date, $last_date) {
                $query->where('state_type_id', '01')
                    ->whereNull('order_note_id')
                    ->whereBetween('date_of_issue', [$date, $last_date]);
            })
            ->sum('quantity');
        //order_notes
        $sum_order_notes = OrderNoteItem::where('item_id', $item_id)
            ->whereHas('order_note', function ($query) use ($date, $last_date) {
                $query->where('state_type_id', '01')
                    ->whereBetween('date_of_issue', [$date, $last_date]);
            })
            ->sum('quantity');
        //documents
        $sum_documents = DocumentItem::where('item_id', $item_id)
            ->whereHas('document', function ($query) use ($date, $last_date) {
                $query->where('state_type_id', '01')
                    ->whereNull('order_note_id')
                    ->whereNull('sale_note_id')
                    ->whereBetween('date_of_issue', [$date, $last_date]);
            })
            ->sum('quantity');
        //purchase
        $sum_purchases = PurchaseItem::where('item_id', $item_id)
            ->whereHas('purchase', function ($query) use ($date, $last_date) {
                $query->where('state_type_id', '01')
                    ->whereBetween('date_of_issue', [$date, $last_date]);
            })
            ->sum('quantity');


        $sum = $sum_sale_notes + $sum_order_notes + $sum_documents - $sum_purchases;

        return $sum;
    }
    function get_init_stock_by_months($item_id, $warehouse_id)
    {
        $lastStock = InitStock::where('item_id', $item_id);

        if ($warehouse_id) {
            $lastStock = $lastStock->where('warehouse_id', $warehouse_id);
        } else {
            $lastStock = $lastStock->whereNull('warehouse_id');
        }
        $lastStock =   $lastStock->latest('init_date')->first();
        $lastDate = null;
        if ($lastStock) {
            $lastDate = $lastStock->init_date;
        }
        $stocks = [];
        if (!isset($lastDate)) {
            $inital_stock = 0;
            $inventory = Inventory::where('description', 'Stock inicial')->first();
            if ($inventory) {
                $inital_stock = $inventory->quantity;
            }
            // $init_purchase = Purchase::whereHas('items', function ($query) use ($item_id) {
            //     $query->where('item_id', $item_id);
            // })->where('state_type_id', '01')->orderBy('date_of_issue')->first();

            $init_purchase = PurchaseItem::where('item_id', $item_id)
                ->whereHas('purchase', function ($query) {
                    $query->where('state_type_id', '01');
                })->first();



            if (!isset($init_purchase)) {
                return ["success" => false, "msg" => "Error"];
            }
            $date = $init_purchase->purchase->date_of_issue;
            $init_stock = $init_purchase->quantity;

            $result = $this->get_all_dates($date->format('Y-m-d'));
            if (count($result) != 0) {
                foreach ($result as $key => $date) {
                    $last_date = $this->get_last_day($date);
                    $sum = InventoryKardex::where('item_id', $item_id);
                    if ($warehouse_id && $warehouse_id != 'all') {
                        $sum = $sum->where('warehouse_id', $warehouse_id);
                    }

                    $sum = $this->sum_quantity($item_id, $date, $last_date);
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
                            // 'stock' => $sum,
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

                    $sum = $this->sum_quantity($item_id, $date, $last_date);
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
                    }
                }
            }
        }
    }
    function get_last_day($date)
    {

        $carbonFecha = Carbon::createFromFormat('Y-m-d', $date);

        $ultimoDiaMes = $carbonFecha->endOfMonth();

        return $ultimoDiaMes->format('Y-m-d'); // muestra '20
    }
    public function excel(Request $request)
    {

        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : auth()->user()->establishment;

        $records = InventoryValuedKardex::getTransformRecords($this->getRecords($request->all())->get());
        $valuedKardexExport = new ValuedKardexExport();



        $valuedKardexExport
            ->records($records)
            ->company($company)
            ->establishment($establishment);

        return $valuedKardexExport->download('Reporte_Kardex_Valorizado_' . Carbon::now() . '.xlsx');
    }
    function get_init_stock($item_id, $lastDate, $start, $warehouse_id = null)
    {

        if ($lastDate) {
            $init_date = InventoryKardex::where('item_id', $item_id);
            $init_date = $init_date->first();
            if (!isset($init_date)) {
                return ["success" => false, "msg" => "Error"];
            }
            $past = InventoryKardex::where('item_id', $item_id)
                ->where('date_of_issue', '<', $start);
            if ($warehouse_id) {
                $past = $past->where('warehouse_id', $warehouse_id);
            }
            $past = $past->where(function ($query) {
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
                    })
                    ->orWhere(function ($query) {
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
                ->selectRaw('SUM(CASE WHEN quantity >= 0 THEN quantity ELSE 0 END) as purchases')
                ->selectRaw('SUM(CASE WHEN quantity < 0 THEN quantity ELSE 0 END) as sales')
                ->first();
            $stocks = InventoryKardex::where('item_id', $item_id)
                ->whereBetween('date_of_issue', [$start, $lastDate]);
            if ($warehouse_id) {
                $stocks = $stocks->where('warehouse_id', $warehouse_id);
            }
            $stocks = $stocks->where(function ($query) {
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
                    })
                    ->orWhere(function ($query) {
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
                ->selectRaw('SUM(CASE WHEN quantity >= 0 THEN quantity ELSE 0 END) as purchases')
                ->selectRaw('SUM(CASE WHEN quantity < 0 THEN quantity ELSE 0 END) as sales')
                ->first();
        }


        return ["stocks" => $stocks, "past" => $past];
    }

    public function excel_stock_months_export(Request $request)
    {
        $date = $request->date;
        $start = $request->start;
        $total = [];
        Item::select('id', 'description', 'purchase_unit_price')->where('unit_type_id', '<>', 'ZZ')->chunk(50, function ($items) use ($date, &$total, $start) {
            foreach ($items as $item) {
                $arr = $this->get_init_stock($item->id, $date, $start);

                if (isset($arr["stocks"])) {
                    $stocks = $arr["stocks"];
                    $past = $arr["past"];
                    $purchase_price = $item->purchase_unit_price;
                    $sale_past_stock = $past["sales"] ?? "0.00";
                    $purchase_past_stock = $past["purchases"] ?? "0.00";
                    $sale_stock = floatval($stocks["sales"] ?? "0.00");
                    $purchase_stock = floatval($stocks["purchases"] ?? "0.00");
                    $purchase_val = floatval($stocks["purchases"]) * floatval($purchase_price);
                    $sale_val = floatval($stocks["sales"]) * floatval($purchase_price);
                    $past_stock = floatval($purchase_past_stock) + floatval($sale_past_stock);
                    $past_val = $past_stock * floatval($purchase_price);
                    $total[] = [
                        "id" => $item->id,
                        "description" => $item->description,
                        "purchase_price" => number_format($item->purchase_unit_price, 2, ".", ""),
                        "sale_stock" => $sale_stock,
                        "purchase_stock" => $purchase_stock,
                        "purchase_val" => $purchase_val,
                        "sale_val" => $sale_val,
                        "past_val" => $past_val,
                        "past_stock" => $past_stock,
                    ];
                }
            }
        });

        // dd($request->all());
        $company = Company::first();




        $records = $total;
        $additionalData = collect(["date" => $date]);
        $valuedKardexFormatSunatExport = new StockHistoryExport();
        $valuedKardexFormatSunatExport
            ->additionalData($additionalData)
            ->records($records)
            ->company($company);

        return $valuedKardexFormatSunatExport->download('Reporte_Historico_Stock_Hasta_' . $date . '.xlsx');
    }
    public function excel_stock_months(Request $request)
    {
        $date = $request->date;
        $start = $request->start;
        $total = [];
        Item::select('id', 'description', 'purchase_unit_price')->where('unit_type_id', '<>', 'ZZ')->chunk(50, function ($items) use ($date, &$total, $start) {
            foreach ($items as $item) {
                $arr = $this->get_init_stock($item->id, $date, $start);
                if (isset($arr["stocks"])) {

                    $stocks = $arr["stocks"];
                    $past = $arr["past"];
                    $purchase_price = $item->purchase_unit_price;
                    $sale_past_stock = $past["sales"] ?? "0.00";
                    $purchase_past_stock = $past["purchases"] ?? "0.00";
                    $sale_stock = $stocks["sales"] ?? "0.00";
                    $purchase_stock = $stocks["purchases"] ?? "0.00";
                    $purchase_val = floatval($stocks["purchases"]) * floatval($purchase_price);
                    $sale_val = floatval($stocks["sales"]) * floatval($purchase_price);
                    $past_stock = floatval($purchase_past_stock) + floatval($sale_past_stock);
                    $past_val = $past_stock * floatval($purchase_price);
                    $total[] = [
                        // "past" => $past,
                        "id" => $item->id,
                        "description" => $item->description,
                        "purchase_price" => number_format($item->purchase_unit_price, 2, ".", ""),
                        "sale_stock" => $sale_stock,
                        "purchase_stock" => $purchase_stock,
                        "purchase_val" => $purchase_val,
                        "sale_val" => $sale_val,
                        "past_val" => $past_val,
                        "past_stock" => $past_stock,
                    ];
                }
            }
        });

        $perPage = 20;
        $currentPage = $request->page ?? 1;
        $records = collect($total)->forPage($currentPage, $perPage)->values();

        $pagination = [
            'total' => count($total),
            'per_page' => $perPage,
            'current_page' => intval($currentPage),
            'last_page' => ceil(count($total) / $perPage),
        ];

        return compact('records', 'pagination');
    }



    public function pdfFormatSunat(Request $request)
    {
        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : null;
        $item_id = $request->item_id;
        $establishment_id = $request->establishment_id;
        $data_of_period = $this->getDataOfPeriod($request);
        $start_date = $data_of_period['d_start'];
        $sunat_stock = new SunatStockService($item_id, $establishment_id, $start_date);
        $sunat_stock->sunat_stock();


        $this->get_init_stock_by_months($request->item_id,  $request->establishment_id);

        $start_date = Carbon::parse($start_date)->startOfMonth()->format('Y-m-d');


        $initial_stock =  SunatStock::where('item_id', $request->item_id)
            ->where('warehouse_id', $request->establishment_id)
            ->first();
 
        $init_date = $initial_stock ?  $initial_stock->period : $start_date;
        // $stock = $init_stock ? $init_stock->stock : 0;
        $stock = 0;
        $is_start_month = $this->is_start_month($data_of_period['d_start']);
        $stock = $sunat_stock->get_sunat_stock();
        if (!$is_start_month) {
            $sunat_stock_rest = new SunatStockService($item_id, $establishment_id, $init_date);
            $date_start = Carbon::parse($start_date)->format('Y-m-d');
            $date_end = Carbon::parse($data_of_period['d_start'])->format('Y-m-d');
            // $rest_stock = $this->get_stock_between_dates($start_date, $data_of_period['d_start'], $request->item_id, $request->establishment_id);
            $rest_stock = $sunat_stock_rest->get_sunat_stock_between_dates($date_start, $date_end);
            $stock += $rest_stock;
        }
        $params = (object)[
            'item_id' => $request['item_id'],
            'establishment_id' => $request['establishment_id'],
            'date_start' => $data_of_period['d_start'],
            'date_end' => $data_of_period['d_end'],
        ];
        $item = Item::findOrFail($request->item_id);
        $purchase_price = $item->purchase_unit_price;
        $cost_unit = $purchase_price;
        $cost_total = $cost_unit * $stock;
        $init = [
            "stock" => $stock,
            "cost_unit" => $cost_unit,
            "cost_total" => $cost_total,
        ];

        //check if start_date and init_date are objets if not parse with carbon
        if (!is_object($start_date)) {
            $start_date = Carbon::parse($start_date);
        }
        if (!is_object($init_date)) {
            $init_date = Carbon::parse($init_date);
        }
        //dump start_date nd init date

        //if start_date is equal to init_date with carbon
   
        if ($start_date->eq($init_date)) {

            $data = InventoryValuedKardex::getDataFormatSunat($params);
        } else {
            $data = InventoryValuedKardex::getDataFormatSunat($params, $init);
        }

        $additionalData = InventoryValuedKardex::getDataAdditional($request, $params, $data['item']);
        $records = $data['records'];

        // usort($records, function ($a, $b) {
        //     $a_date = Carbon::parse($a["date_of_issue"]);
        //     $b_date = Carbon::parse($b["date_of_issue"]);
        //     if ($a_date->eq($b_date)) {
        //         return 0;
        //     }
        //     return ($a_date->isBefore($b_date)) ? -1 : 1;
        // });
        $show_init = !$start_date->eq($init_date);
        $init_cost_unit = $cost_unit;
        $init_cost_total = $cost_total;
        $init_stock = $stock;
        $pdf = PDF::loadView('inventory::reports.valued_kardex.report_pdf_sunat', compact(
            'records',
            'company',
            'establishment',
            'additionalData',
            'init_stock',
            'show_init',
            'init_cost_unit',
            'init_cost_total',
        ));

        $pdf->setPaper('A4', 'landscape');
        $filename = 'Reporte_Inventario' . date('YmdHis');

        return $pdf->stream($filename . '.pdf');
    }
    public function excelFormatSunat(Request $request)
    {

        // dd($request->all());
        
        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : null;
        $item_id = $request->item_id;
        $establishment_id = $request->establishment_id;
        $data_of_period = $this->getDataOfPeriod($request);
        $start_date = $data_of_period['d_start'];
        $sunat_stock = new SunatStockService($item_id, $establishment_id, $start_date);
        $sunat_stock->sunat_stock();


        $this->get_init_stock_by_months($request->item_id,  $request->establishment_id);

        $start_date = Carbon::parse($start_date)->startOfMonth()->format('Y-m-d');


        $initial_stock =  SunatStock::where('item_id', $request->item_id)
            ->where('warehouse_id', $request->establishment_id)
            ->first();

        $init_date = $initial_stock ?  $initial_stock->period : $start_date;
        // $stock = $init_stock ? $init_stock->stock : 0;
        $stock = 0;
        $is_start_month = $this->is_start_month($data_of_period['d_start']);
        $stock = $sunat_stock->get_sunat_stock();
        if (!$is_start_month) {
            $sunat_stock_rest = new SunatStockService($item_id, $establishment_id, $init_date);
            $date_start = Carbon::parse($start_date)->format('Y-m-d');
            $date_end = Carbon::parse($data_of_period['d_start'])->format('Y-m-d');
            // $rest_stock = $this->get_stock_between_dates($start_date, $data_of_period['d_start'], $request->item_id, $request->establishment_id);
            $rest_stock = $sunat_stock_rest->get_sunat_stock_between_dates($date_start, $date_end);
            $stock += $rest_stock;
        }
        $params = (object)[
            'item_id' => $request['item_id'],
            'establishment_id' => $request['establishment_id'],
            'date_start' => $data_of_period['d_start'],
            'date_end' => $data_of_period['d_end'],
        ];
        $item = Item::findOrFail($request->item_id);
        $purchase_price = $item->purchase_unit_price;
        $cost_unit = $purchase_price;
        $cost_total = $cost_unit * $stock;
        $init = [
            "stock" => $stock,
            "cost_unit" => $cost_unit,
            "cost_total" => $cost_total,
        ];

        //check if start_date and init_date are objets if not parse with carbon
        if (!is_object($start_date)) {
            $start_date = Carbon::parse($start_date);
        }
        if (!is_object($init_date)) {
            $init_date = Carbon::parse($init_date);
        }
        //dump start_date nd init date

        //if start_date is equal to init_date with carbon
        if ($start_date->eq($init_date)) {

            $data = InventoryValuedKardex::getDataFormatSunat($params);
        } else {
            $data = InventoryValuedKardex::getDataFormatSunat($params, $init);
        }

        $additionalData = InventoryValuedKardex::getDataAdditional($request, $params, $data['item']);
        $records = $data['records'];

        // usort($records, function ($a, $b) {
        //     $a_date = Carbon::parse($a["date_of_issue"]);
        //     $b_date = Carbon::parse($b["date_of_issue"]);
        //     if ($a_date->eq($b_date)) {
        //         return 0;
        //     }
        //     return ($a_date->isBefore($b_date)) ? -1 : 1;
        // });




        $valuedKardexFormatSunatExport = new ValuedKardexFormatSunatExport();
        $valuedKardexFormatSunatExport
            ->additionalData($additionalData)
            ->records($records)
            ->company($company)
            ->establishment($establishment)
            ->show_init(!$start_date->eq($init_date))
            ->init_stock($stock)
            ->init_cost_unit($cost_unit)
            ->init_cost_total($cost_total);

        return $valuedKardexFormatSunatExport->download('Reporte_Kardex_Valorizado_Sunat_13_1' . Carbon::now() . '.xlsx');
    }
    //a function to check if a date is the first day of the month
    public function is_start_month($date)
    {
        $date = Carbon::parse($date);
        return $date->day == 1;
    }
}
