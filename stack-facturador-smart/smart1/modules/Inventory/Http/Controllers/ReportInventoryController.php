<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Company;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\DownloadTray;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Modules\Inventory\Exports\InventoryExport;
use Modules\Inventory\Models\ItemWarehouse;
use Modules\Inventory\Models\Warehouse;
use Modules\Item\Models\Brand;
use Modules\Item\Models\Category;
use Hyn\Tenancy\Models\Hostname;
use App\Models\System\Client;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\ApiPeruDev\Data\ServiceData;
use Modules\BusinessTurn\Models\BusinessTurn;
use Modules\Inventory\Exports\InventoryExportManual;
use Modules\Inventory\Jobs\ProcessInventoryReport;
use Modules\Inventory\Http\Resources\ReportInventoryCollection;



class ReportInventoryController extends Controller
{
    public function tables()
    {
        $exchange_rate = (new ServiceData())->exchange(Carbon::now()->format('Y-m-d'));
        return [
            'exchange_rate' => $exchange_rate,
            'is_majolica' => BusinessTurn::isMajolica(),
            'warehouses' => Warehouse::query()->select('id', 'description')->get(),
            'categories' => Category::query()->select('id', 'name')->get(),
            'brands' => Brand::query()->select('id', 'name')->get(),
        ];
    }

    public function index()
    {
        //        $warehouse_id = $request->input('warehouse_id');
        //        $reports = $this->getRecords($warehouse_id)->paginate(config('tenant.items_per_page'));
        //
        //        $warehouses = Warehouse::query()->select('id', 'description')->get();
        //
        //        return view('inventory::reports.inventory.index', compact('reports', 'warehouses'));
        return view('inventory::reports.inventory.index');
    }

    public function records(Request $request)
    {
        $must_sales = $request->must_sales == "true" ? true : false;
        $warehouse_id = $request->input('warehouse_id');
        //$brand_id = (int)$request->brand_id;
        //$category_id = (int)$request->category_id;
        //$active = $request->active;
        $filter = $request->input('filter');
        //$date_end = $request->has('date_end') ? $request->date_end : null;
        //$date_start = $request->has('date_start') ? $request->date_start : null;
        $records = $this->getRecords($warehouse_id, $filter, $request);

        if ($must_sales) {
            $records->orderBy('kardex_quantity', 'desc');
        }

        return new ReportInventoryCollection($records->paginate(50), $filter);
    }

    public function totals(Request $request)
    {
        $response_exchange_rate = (new ServiceData())->exchange(Carbon::now()->format('Y-m-d'));
        $exchange_rate = $response_exchange_rate['sale'];
        $currency = $request->currency;
        $warehouse_id = $request->input('warehouse_id');
        $filter = $request->input('filter');
        $totals = [
            'total_profit' => 0,
            'total' => 0,
            'total_usd' => 0,
            'total_pen' => 0,
            'total_all_profit' => 0,
            'purchase_unit_price' => 0,
            'sale_unit_price' => 0,
            'total_profit_usd' => 0,
            'total_profit_pen' => 0,
            'total_all_profit_usd' => 0,
            'total_all_profit_pen' => 0,
            'purchase_unit_price_usd' => 0,
            'purchase_unit_price_pen' => 0,
            'sale_unit_price_usd' => 0,
            'sale_unit_price_pen' => 0,
        ];
        $this->getRecords($warehouse_id, $filter, $request)->chunk(1000, function ($items) use ($exchange_rate, $currency, &$totals) {
            foreach ($items as $row) {
                $sale_unit_price = $row->item->sale_unit_price;
                $purchase_unit_price = $row->item->purchase_unit_price;
                $currency_type_id = $row->item->currency_type_id;
                if ($currency === 'MIX') {
                    $profit = number_format($sale_unit_price - $purchase_unit_price, 2, '.', '');
                    $total_profit = $profit * $row->stock;

                    if ($currency_type_id === 'USD') {
                        $totals['total_profit_usd'] += $profit;
                        $totals['total_all_profit_usd'] += $total_profit;
                        $totals['purchase_unit_price_usd'] += $purchase_unit_price;
                        $totals['sale_unit_price_usd'] += $sale_unit_price;
                        $totals['total_usd'] += $row->stock * $sale_unit_price;
                    } else {
                        $totals['total_profit_pen'] += $profit;
                        $totals['total_all_profit_pen'] += $total_profit;
                        $totals['purchase_unit_price_pen'] += $purchase_unit_price;
                        $totals['sale_unit_price_pen'] += $sale_unit_price;
                        $totals['total_pen'] += $row->stock * $purchase_unit_price;
                    }
                } else {
                    if ($currency_type_id == 'PEN' && $currency == 'USD') {
                        $sale_unit_price = number_format($sale_unit_price / $exchange_rate, 2, '.', '');
                        $purchase_unit_price = number_format($purchase_unit_price / $exchange_rate, 2, '.', '');
                    }
                    if ($currency_type_id == 'USD' && $currency == 'PEN') {
                        $sale_unit_price = number_format($sale_unit_price * $exchange_rate, 2, '.', '');
                        $purchase_unit_price = number_format($purchase_unit_price * $exchange_rate, 2, '.', '');
                    }
                    $profit = number_format($sale_unit_price - $purchase_unit_price, 2, '.', '');
                    $total_profit = $profit * $row->stock;
                    $totals['total_profit'] += $profit;
                    $totals['total_all_profit'] += $total_profit;
                    $totals['purchase_unit_price'] += $purchase_unit_price;
                    $totals['sale_unit_price'] += $sale_unit_price;
                    $totals['total'] += $row->stock * $sale_unit_price;
                }
            }
        });
        return response()->json(['totals' => $totals]);
    }

    /**
     * @param int $warehouse_id Id de almacen
     *
     * @return Builder
     */
    private function getRecords($warehouse_id = 0, $filter, $request)
    {
        $query = ItemWarehouse::with([
            'warehouse', 'item' => function ($query) {
                $query->select('id', 'barcode', 'internal_id', 'description', 'name', 'category_id', 'brand_id', 'currency_type_id', 'stock_min', 'sale_unit_price', 'purchase_unit_price', 'model', 'date_of_due');
                $query->with(['category', 'brand', 'cat_digemid', 'lots_group']);
                $query->without(['item_type', 'unit_type', 'currency_type', 'warehouses', 'item_unit_types', 'tags']);
                // $query->whereHas('cat_digemid', function ($q) {
                //     $q->select('nom_titular as laboratory');
                // });
            }

        ])
            ->select('*', \DB::raw('(SELECT SUM(quantity) FROM kardex WHERE kardex.item_id = item_warehouse.item_id AND type = "sale") as kardex_quantity'))


            ->whereHas('item', function ($q) {
                $q->where([
                    ['item_type_id', '01'],
                    ['unit_type_id', '!=', 'ZZ'],
                ])
                    ->whereNotIsSet();
            });
        if ($filter === '02') {
            //$add = ($stock < 0);
            $query->where('stock', '<=', 0);
        }

        if ($filter === '03') {
            //$add = ($stock == 0);
            $query->where('stock', 0);
        }

        if ($filter === '06') {
            $query->where('stock', '>', 0);
        }

        if ($filter === '04') {
            //$add = ($stock > 0 && $stock <= $item->stock_min);
            //$query->where('stock', 0);
            $query = ItemWarehouse::with(['warehouse', 'item' => function ($query) {
                $query->select('id', 'barcode', 'internal_id', 'description', 'category_id', 'brand_id', 'currency_type_id', 'stock_min', 'sale_unit_price', 'purchase_unit_price', 'model', 'date_of_due');
                $query->with(['category', 'brand', 'cat_digemid', 'lots_group']);
                $query->without(['item_type', 'unit_type', 'currency_type', 'warehouses', 'item_unit_types', 'tags']);
            }])
                ->select('*', \DB::raw('(SELECT SUM(quantity) FROM kardex WHERE kardex.item_id = item_warehouse.item_id AND type = "sale") as kardex_quantity'))
                ->whereHas('item', function ($q) {
                    $q->where([
                        ['item_type_id', '01'],
                        ['unit_type_id', '!=', 'ZZ'],
                    ])
                        ->whereNotIsSet()
                        ->whereStockMin();
                })->where('stock', '>', 0)
                ->whereRaw('stock < (SELECT stock_min FROM items WHERE items.id = item_warehouse.item_id)');
        }


        if ($filter === '05') {
            //$add = ($stock > $item->stock_min);
            $query = ItemWarehouse::with(['warehouse', 'item' => function ($query) {
                $query->select('id', 'barcode', 'internal_id', 'description', 'category_id', 'brand_id', 'currency_type_id', 'stock_min', 'sale_unit_price', 'purchase_unit_price', 'model', 'date_of_due');
                $query->with(['category', 'brand', 'cat_digemid', 'lots_group']);
                $query->without(['item_type', 'unit_type', 'currency_type', 'warehouses', 'item_unit_types', 'tags']);
            }])
                ->select('*', DB::raw('(SELECT SUM(quantity) FROM kardex WHERE kardex.item_id = item_warehouse.item_id AND type = "sale") as kardex_quantity'))
                ->whereHas('item', function ($q) {
                    $q->where([
                        ['item_type_id', '01'],
                        ['unit_type_id', '!=', 'ZZ'],
                    ])
                        ->whereNotIsSet()
                        ->whereStockMinValidate();
                });
        }


        if ($warehouse_id != 0) {
            $query->where('item_warehouse.warehouse_id', $warehouse_id);
        }

        if ($request->category_id) $query->whereItemCategory($request->category_id);

        if ($request->brand_id) $query->whereItemBrand($request->brand_id);

        return $query;
    }

    public function downLoadTrayReport(Request $request)
    {
        $tray = DownloadTray::create([
            'user_id' => auth()->user()->id,
            'module' => 'INVENTORY',
            'path' => $request->path,
            'format' => 'pdf',
            'type' => 'Reporte Inventario'
        ]);

        $company = Company::active();
        $client = Client::where('number', $company->number)->first();
        $website_id = $client->hostname->website_id;

        ProcessInventoryReport::dispatch($website_id, $tray->id)->onQueue('process_inventory_report');

        return  [
            'success' => true,
            'message' => 'El reporte se esta procesando; puede ver el proceso en bandeja de descargas.'
        ];
    }

    public function exportTicket(Request $request)
    {
        $company = Company::first();
        $establishment = Establishment::first();
        ini_set('max_execution_time', 0);

        $filter = $request->input('filter');
        $query = ItemWarehouse::with(['item', 'item.brand'])
            ->select('item_id', DB::raw('SUM(stock) as total_stock'))
            ->whereHas('item', function ($q) {
                $q->where([['item_type_id', '01'], ['unit_type_id', '!=', 'ZZ']]);
                $q->whereNotIsSet();
            })
            ->groupBy('item_id');

        if ($request->warehouse_id && $request->warehouse_id != 'all') {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($filter === '02') {
            $query->having('total_stock', '<=', 0);
        }
        if ($filter === '03') {
            $query->having('total_stock', 0);
        }
        if ($filter === '04') {
            $query->whereHas('item', function ($q) {
                $q->where([
                    ['item_type_id', '01'],
                    ['unit_type_id', '!=', 'ZZ'],
                ])
                    ->whereNotIsSet()
                    ->whereStockMin();
            })->having('total_stock', '>', 0);
        }
        if ($filter === '05') {
            $query->whereHas('item', function ($q) {
                $q->where([
                    ['item_type_id', '01'],
                    ['unit_type_id', '!=', 'ZZ'],
                ])
                    ->whereNotIsSet()
                    ->whereStockMinValidate();
            });
        }

        $reports = $query->latest('item_id')->get();

        $height = 250;
        $height = 8 * 40;
        $height = $height + $reports->count() * 20;

        $pdf = PDF::loadView(
            'inventory::reports.inventory.report_ticket_pdf',
            compact("reports", "company", "establishment")
        )
            ->setPaper(array(0, 0, 249.45, $height));

        $filename = 'Reporte_Inventario' . date('YmdHis');

        return $pdf->stream($filename . '.pdf');
    }
    public function getRecordsTranform($warehouse_id, $filter, $params)
    {

        $must_sales = array_key_exists('must_sales', $params) ? ($params['must_sales'] == "true" ? true : false) : false;
        $currency = array_key_exists('currency', $params) ? $params['currency'] : 'PEN';
        $exchange_rate_response = (new ServiceData())->exchange(Carbon::now()->format('Y-m-d'));
        $exchange_rate = $exchange_rate_response['sale'];
        $records = $this->getRecords($warehouse_id, $filter, (object) $params);
        if ($must_sales) {
            $records->orderBy('kardex_quantity', 'desc');
        }
        $w_id = isset($params['warehouse_id']) ? $params['warehouse_id'] : 'all';
        $establishment_id = null;
        if ($w_id !== 'all') {
            $establishment_id = Warehouse::find($w_id)->establishment_id;
        }
        $data = [];

        $records->chunk(1000, function ($items) use (&$data, $currency, $exchange_rate, $establishment_id) {
            $warehousesId = $items->pluck('warehouse_id')->unique();
            $warehouses = Warehouse::whereIn('id', $warehousesId)->get()->keyBy('id');
            $itemsId = $items->pluck('item_id')->toArray();
            $connection = DB::connection('tenant');
            $quotation_items = $connection->table('quotation_items')
                ->select([
                    'quotation_items.id',
                    'quotation_items.quotation_id',
                    'quotation_items.item_id',
                    'quotation_items.quantity',
                    'quotations.establishment_id',
                    DB::raw("COALESCE(JSON_UNQUOTE(JSON_EXTRACT(quotation_items.item, '$.presentation.quantity_unit')), '1') as quantity_unit"),

                ])
                ->join('quotations', 'quotation_items.quotation_id', '=', 'quotations.id')
                ->leftJoin('sale_notes', 'quotations.id', '=', 'sale_notes.quotation_id')
                ->leftJoin('documents', 'quotations.id', '=', 'documents.quotation_id')
                ->whereNull('sale_notes.quotation_id')
                ->whereNull('documents.quotation_id')
                ->whereIn('quotations.state_type_id', ['01', '03', '05', '07'])
                ->when($establishment_id, function ($query) use ($establishment_id) {
                    $query->where('quotations.establishment_id', $establishment_id);
                })
                ->get()->groupBy(['item_id', 'establishment_id']);

            $purchase_order_items = $connection->table('purchase_order_items')
                ->select([
                    'purchase_order_items.id',
                    'purchase_order_items.purchase_order_id',
                    'purchase_order_items.item_id',
                    'purchase_order_items.quantity',
                    'purchase_orders.establishment_id' // Asegurarte de incluir este campo
                ])
                ->whereIn('item_id', $itemsId)
                ->join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
                ->leftJoin('purchases', 'purchase_orders.id', '=', 'purchases.purchase_order_id')
                ->whereNull('purchases.id')
                ->whereIn('purchase_orders.state_type_id', ['01', '03', '05', '07'])
                ->when($establishment_id, function ($query) use ($establishment_id) {
                    $query->where('purchase_orders.establishment_id', $establishment_id);
                })
                ->get()->groupBy(['item_id', 'establishment_id']);
            foreach ($items as $row) {

                $sale_unit_price = $row->item->sale_unit_price;
                $purchase_unit_price = $row->item->purchase_unit_price;
                $currency_type_id = $row->item->currency_type_id;

                if ($currency !== 'MIX') {
                    if ($currency_type_id == 'PEN' && $currency == 'USD') {
                        $sale_unit_price = number_format($sale_unit_price / $exchange_rate, 2, '.', '');
                        $purchase_unit_price = number_format($purchase_unit_price / $exchange_rate, 2, '.', '');
                    }
                    if ($currency_type_id == 'USD' && $currency == 'PEN') {
                        $sale_unit_price = number_format($sale_unit_price * $exchange_rate, 2, '.', '');
                        $purchase_unit_price = number_format($purchase_unit_price * $exchange_rate, 2, '.', '');
                    }
                }

                $establishment_id = $warehouses[$row->warehouse_id]->establishment_id;
                $out_stock = $quotation_items->get($row->item_id, collect([]))
                    ->get($establishment_id, collect([]))->sum(function ($row) {
                        return $row->quantity * $row->quantity_unit;
                    });
                $in_stock = $purchase_order_items->get($row->item_id, collect([]))
                    ->get($establishment_id, collect([]))->sum('quantity');
                $stock = $row->stock;
                $future_stock = $in_stock + $stock - $out_stock;

                $item = $row->item;
                $data[] = [
                    'out_stock' => $out_stock ?? 0,
                    'in_stock' => $in_stock ?? 0,
                    'future_stock' => $future_stock ?? 0,
                    'laboratory' => optional($item->cat_digemid)->nom_titular,
                    'num_reg_san' => optional($item->cat_digemid)->num_reg_san,
                    'kardex_quantity' => (float) $row->kardex_quantity ?? 0,
                    'lots_group' => $item->lots_group->transform(function ($row, $key) {
                        if (is_array($row)) {
                            $row = (object) $row;
                        }
                        return [
                            'id' => $row->id,
                            'code' => $row->code,
                            'quantity' => $row->quantity,
                            'date_of_due' => $row->date_of_due,
                        ];
                    }),
                    'sale_unit_price' => $sale_unit_price,
                    'stock_min' => $item->stock_min,
                    'currency_type_id' => $currency_type_id,
                    'stock' => $row->stock ?? 0,
                    'purchase_unit_price' => $purchase_unit_price,
                    'profit' => number_format($sale_unit_price - $purchase_unit_price, 2, '.', ''),
                    'barcode' => $item->barcode,
                    'internal_id' => $item->internal_id,
                    'name' => $this->stripInvalidXml($item->description),
                    'description' => $this->stripInvalidXml($row->name),
                    'item_category_name' => optional($item->category)->name,
                    'model' => $item->model,
                    'brand_name' => optional($item->brand)->name,
                    'date_of_due' => optional($item->date_of_due)->format('d/m/Y'),
                    'warehouse_name' => $row->warehouse->description
                ];
            }
        });


        return $data;
    }
    function stripInvalidXml($value)
    {
        $ret = '';

        if (empty($value)) {
            return $ret;
        }

        $length = strlen($value);

        for ($i = 0; $i < $length; $i++) {
            $current = ord($value[$i]);

            if (
                ($current == 0x9) ||
                ($current == 0xA) ||
                ($current == 0xD) ||
                (($current >= 0x20) && ($current <= 0xD7FF)) ||
                (($current >= 0xE000) && ($current <= 0xFFFD)) ||
                (($current >= 0x10000) && ($current <= 0x10FFFF))
            ) {
                $ret .= chr($current);
            } else {
                $ret .= ' ';
            }
        }

        return $ret;
    }
    public function exportExcelManual(Request $request)
    {
        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", "3600");
        try {
            $company = Company::query()->first();
            $establishment = null;
            $warehouse_id = $request->warehouse_id;
            $filter = $request->filter;
            $currency = $request->currency;
            if ($warehouse_id != 0) {
                $establishment = Establishment::find($warehouse_id);
            } else {
                $establishment = Establishment::query()->first();
            }
            $response = $this->totals($request);
            $totals = $response->getData()->totals;
            $filename = 'INVENTORY_ReporteInv_' . date('YmdHis') . ($currency == 'PEN' ? ' en_soles_' : ($currency == 'USD' ? ' en_dolares_' : '')) . '-' . auth()->user()->id;

            $inventoryExport = new InventoryExportManual();
            $user_type = auth()->user()->type;
            return $inventoryExport
                ->records($this->getRecordsTranform($warehouse_id, $filter, $request->all()))
                ->company($company)
                ->showSomeColumns($user_type == 'admin' || $user_type == 'superadmin')
                ->establishment($establishment)
                ->format($filter)
                ->totals($totals)
                ->currency($currency)
                ->download($filename . '.xlsx');
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function export(Request $request)
    {
        $host = $request->getHost();
        $tray = DownloadTray::create([
            'user_id' => auth()->user()->id,
            'module' => 'INVENTORY',
            'format' => $request->input('format'),
            'date_init' => date('Y-m-d H:i:s'),
            'type' => 'Reporte Inventario'
        ]);
        $trayId = $tray->id;
        $hostname = Hostname::where('fqdn', $host)->first();
        if (empty($hostname)) {
            $company = Company::active();
            $number = $company->number;
            $client = Client::where('number', $number)->first();
            $website_id = $client->hostname->website_id;
        } else {
            $website_id = $hostname->website_id;
        }
        ProcessInventoryReport::dispatch($website_id, $trayId, ($request->warehouse_id == 'all' ? 0 :  $request->warehouse_id), $request->input('filter'), $request->all(), auth()->user()->type);

        return  [
            'success' => true,
            'message' => 'El reporte se esta procesando; puede ver el proceso en bandeja de descargas.'
        ];
    }

    /**
     * Search
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {

        $reports = ItemWarehouse::with(['item'])->whereHas('item', function ($q) {
            $q->where([['item_type_id', '01'], ['unit_type_id', '!=', 'ZZ']]);
            $q->whereNotIsSet();
        })->latest()->get();

        return view('inventory::reports.inventory.index', compact('reports'));
    }

    /**
     * PDF
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function pdf(Request $request)
    {

        $company = Company::first();
        $establishment = Establishment::first();
        ini_set('max_execution_time', 0);

        if ($request->warehouse_id && $request->warehouse_id != 'all') {
            $reports = ItemWarehouse::with(['item', 'item.brand'])->where('warehouse_id', $request->warehouse_id)->whereHas('item', function ($q) {
                $q->where([['item_type_id', '01'], ['unit_type_id', '!=', 'ZZ']]);
                $q->whereNotIsSet();
            })->latest()->get();
        } else {

            $reports = ItemWarehouse::with(['item', 'item.brand'])->whereHas('item', function ($q) {
                $q->where([['item_type_id', '01'], ['unit_type_id', '!=', 'ZZ']]);
                $q->whereNotIsSet();
            })->latest()->get();
        }


        $pdf = PDF::loadView('inventory::reports.inventory.report_pdf', compact("reports", "company", "establishment"));
        $pdf->setPaper('A4', 'landscape');
        $filename = 'Reporte_Inventario' . date('YmdHis');

        return $pdf->download($filename . '.pdf');
    }

    /**
     * Excel
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function excel(Request $request)
    {
        $company = Company::first();
        $establishment = Establishment::first();


        if ($request->warehouse_id && $request->warehouse_id != 'all') {
            $records = ItemWarehouse::with(['item', 'item.brand'])->where('warehouse_id', $request->warehouse_id)->whereHas('item', function ($q) {
                $q->where([['item_type_id', '01'], ['unit_type_id', '!=', 'ZZ']]);
                $q->whereNotIsSet();
            })->latest()->get();
        } else {
            $records = ItemWarehouse::with(['item', 'item.brand'])->whereHas('item', function ($q) {
                $q->where([['item_type_id', '01'], ['unit_type_id', '!=', 'ZZ']]);
                $q->whereNotIsSet();
            })->latest()->get();
        }


        return (new InventoryExport)
            ->records($records)
            ->company($company)
            ->establishment($establishment)
            ->download('ReporteInv' . Carbon::now() . '.xlsx');
    }
}
