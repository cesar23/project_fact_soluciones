<?php

namespace Modules\Inventory\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Hyn\Tenancy\Models\Website;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Models\Tenant\DownloadTray;
use Hyn\Tenancy\Environment;
use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\Models\Tenant\Company;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\User;
use App\Models\Tenant\Warehouse;
use Modules\Inventory\Exports\InventoryExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\ApiPeruDev\Data\ServiceData;
use Modules\Inventory\Models\ItemWarehouse;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

class ProcessInventoryReport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use StorageDocument;

    public $website_id;
    public $tray_id;
    public $warehouse_id;
    public $filter;
    public $params;
    public $user_type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $website_id, int $tray_id, int $warehouse_id, string $filter, array $params, string $user_type)
    {
        $this->website_id = $website_id;
        $this->tray_id = $tray_id;
        $this->warehouse_id = $warehouse_id;
        $this->filter = $filter;
        $this->params = $params;
        $this->user_type = $user_type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::debug("ProcessInventoryReport Start WebsiteId => " . $this->website_id);
        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", "3600");
        $website = Website::find($this->website_id);
        $tenancy = app(Environment::class);
        $tenancy->tenant($website);

        $tray = DownloadTray::find($this->tray_id);
        $user_id = $tray->user_id;
        $path = null;
        $currency = $this->params['currency'] ?? 'PEN';
        $website_id = $this->website_id;
        $tray_id = $this->tray_id;
        $warehouse_id = $this->warehouse_id;
        $filter = $this->filter;
        if (empty($tray)) {

            \Log::debug("No hay datos
                    $ website_id        =>" . var_export($website_id, true) . "
                    $ tray_id       =>" . var_export($tray_id, true) . "
                    $ warehouse_id      =>" . var_export($warehouse_id, true) . "
                    $ filter        =>" . var_export($filter, true) . "
                    ");
        } else {
            try {
                $company = Company::query()->first();
                $establishment = null;

                if ($this->warehouse_id != 0) {
                    $establishment = Establishment::find($this->warehouse_id);
                } else {
                    $establishment = Establishment::query()->first();
                }
                //ini_set('max_execution_time', 0);
                $records = $this->getRecordsTranform($this->warehouse_id, $this->filter);

                if (!is_object($tray)) {
                    //Log::debug('DE ' . var_export($tray, true));
                }
                $format = $tray->format;
                $totals = $this->totals($this->warehouse_id, $this->filter);

                if ($format === 'pdf') {

                    ini_set("pcre.backtrack_limit", "50000000");

                    Log::debug("Render pdf init");
                    $showSomeColumns = $this->user_type == 'admin' || $this->user_type == 'superadmin';
                    $html = view('inventory::reports.inventory.report', compact(
                        'user_id',
                        'totals',
                        'records',
                        'company',
                        'establishment',
                        'format',
                        'currency',
                        'showSomeColumns'
                    ))->render();

                    ////////////////////////////////

                    $base_template = $establishment->template_pdf;


                    $defaultConfig = (new ConfigVariables())->getDefaults();
                    $fontDirs = $defaultConfig['fontDir'];

                    $defaultFontConfig = (new FontVariables())->getDefaults();
                    $fontData = $defaultFontConfig['fontdata'];

                    $pdf_font_regular = config('tenant.pdf_name_regular');
                    $pdf_font_bold = config('tenant.pdf_name_bold');

                    $pdf = new Mpdf([
                        'format' => 'A4-L',
                        'fontDir' => array_merge($fontDirs, [
                            app_path('CoreFacturalo' . DIRECTORY_SEPARATOR . 'Templates' .
                                DIRECTORY_SEPARATOR . 'pdf' .
                                DIRECTORY_SEPARATOR . $base_template .
                                DIRECTORY_SEPARATOR . 'font')
                        ]),
                        'fontdata' => $fontData + [
                            'custom_bold' => [
                                'R' => $pdf_font_bold . '.ttf',
                            ],
                            'custom_regular' => [
                                'R' => $pdf_font_regular . '.ttf',
                            ],
                        ]
                    ]);

                    $path_css = app_path('CoreFacturalo' . DIRECTORY_SEPARATOR . 'Templates' .
                        DIRECTORY_SEPARATOR . 'pdf' .
                        DIRECTORY_SEPARATOR . 'default' .
                        DIRECTORY_SEPARATOR . 'style.css');

                    $stylesheet = file_get_contents($path_css);

                    $pdf->WriteHTML($stylesheet, HTMLParserMode::HEADER_CSS);
                    $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);

                    $filename = 'INVENTORY_ReporteInv_' . date('YmdHis') . ($currency == 'PEN' ? ' en_soles_' : ($currency == 'USD' ? ' en_dolares_' : '')) . '-' . $tray->user_id;
                    Log::debug("Render pdf finish");

                    Log::debug("Upload pdf init");


                    $this->uploadStorage($filename, $pdf->output('', 'S'), 'download_tray_pdf');
                    Log::debug("Upload pdf finish");

                    $tray->file_name = $filename;
                    $path = 'download_tray_pdf';
                } else {

                    // Log::debug($records);
                    $filename = 'INVENTORY_ReporteInv_' . date('YmdHis') . ($currency == 'PEN' ? ' en_soles_' : ($currency == 'USD' ? ' en_dolares_' : '')) . '-' . $tray->user_id;
                    Log::debug("Render excel init");
                    $inventoryExport = new InventoryExport();
                    $inventoryExport
                        ->records($records)
                        ->company($company)
                        ->user_id($user_id)
                        ->establishment($establishment)
                        ->currency($currency)
                        ->showSomeColumns($this->user_type == 'admin' || $this->user_type == 'superadmin')
                        ->totals($totals)
                        ->format($format);
                    Log::debug("Render excel finish");

                    Log::debug("Upload excel init");

                    $inventoryExport->store(DIRECTORY_SEPARATOR . "download_tray_xlsx" . DIRECTORY_SEPARATOR . $filename . '.xlsx', 'tenant');

                    Log::debug("Upload excel finish");
                    $tray->file_name = $filename;
                    $path = 'download_tray_xlsx';
                }

                $tray->date_end = date('Y-m-d H:i:s');
                $tray->status = 'FINISHED';
                $tray->path = $path;
                $tray->save();
            } catch (Exception $e) {
                Log::debug("ProcessInventoryReport Error transaction" . $e);
            }
        }

        Log::debug("ProcessInventoryReport Finish transaction");
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


    public function getRecordsTranform($warehouse_id, $filter)
    {
        Log::debug("warehouse_id" . $warehouse_id);

        Log::debug("getRecordsTranform init" . date('H:i:s'));
        $must_sales = array_key_exists('must_sales', $this->params) ? ($this->params['must_sales'] == "true" ? true : false) : false;
        $currency = $this->params['currency'] ?? 'PEN';
        $exchange_rate_response = (new ServiceData())->exchange(Carbon::now()->format('Y-m-d'));
        $exchange_rate = $exchange_rate_response['sale'];

        $records = $this->getRecords($warehouse_id, $filter);
        if ($must_sales) {
            $records->orderBy('kardex_quantity', 'desc');
        }
        $w_id = isset($this->params['warehouse_id']) ? $this->params['warehouse_id'] : 'all';
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
                ->whereIn('purchase_orders.state_type_id', ['01', '03', '05', '07'])
                ->whereNull('purchases.id')
                ->when($establishment_id, function ($query) use ($establishment_id) {
                    $query->where('purchase_orders.establishment_id', $establishment_id);
                })
                ->get()->groupBy(['item_id', 'establishment_id']);
            foreach ($items as $row) {
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
                $sale_unit_price = $item->sale_unit_price;
                $purchase_unit_price = $item->purchase_unit_price;
                $currency_type_id = $item->currency_type_id;
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
                    'stock' => $row->stock,
                    'purchase_unit_price' => $purchase_unit_price,
                    'profit' => number_format($sale_unit_price - $purchase_unit_price, 2, '.', ''),
                    'barcode' => $item->barcode,
                    'internal_id' => $item->internal_id,
                    'currency_type_id' => $currency_type_id,
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

        Log::debug("getRecordsTranform finish" . date('H:i:s'));

        return $data;
    }
    public function totals($warehouse_id, $filter)
    {
        $response_exchange_rate = (new ServiceData())->exchange(Carbon::now()->format('Y-m-d'));
        $exchange_rate = $response_exchange_rate['sale'];
        $currency = $this->params['currency'] ?? 'PEN';
        $warehouse_id = $warehouse_id;
        $filter = $filter;
        $totals = [
            'total_profit' => 0,
            'total_all_profit' => 0,
            'purchase_unit_price' => 0,
            'sale_unit_price' => 0,
            'total' => 0,
            'total_profit_usd' => 0,
            'total_usd' => 0,
            'total_profit_pen' => 0,
            'total_pen' => 0,
            'total_all_profit_usd' => 0,
            'total_all_profit_pen' => 0,
            'purchase_unit_price_usd' => 0,
            'purchase_unit_price_pen' => 0,
            'sale_unit_price_usd' => 0,
            'sale_unit_price_pen' => 0,
        ];
        $this->getRecords($warehouse_id, $filter)->chunk(1000, function ($items) use ($exchange_rate, $currency, &$totals) {
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
        return $totals;
    }
    private function getRecords($warehouse_id = 0, $filter)
    {

        $query = ItemWarehouse::with(['warehouse', 'item' => function ($query) {
            $query->select('id', 'barcode', 'internal_id', 'description', 'name', 'category_id', 'brand_id', 'stock_min', 'sale_unit_price', 'purchase_unit_price', 'model', 'date_of_due', 'currency_type_id');
            $query->with(['category', 'brand', 'cat_digemid', 'lots_group']);
            $query->without(['item_type', 'unit_type', 'currency_type', 'warehouses', 'item_unit_types', 'tags']);
        }])
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

        if ($filter === '04') {
            //$add = ($stock > 0 && $stock <= $item->stock_min);
            //$query->where('stock', 0);

            $query = ItemWarehouse::with(['warehouse', 'item' => function ($query) {
                $query->select('id', 'barcode', 'internal_id', 'description', 'name', 'category_id', 'brand_id', 'stock_min', 'sale_unit_price', 'purchase_unit_price', 'model', 'date_of_due', 'currency_type_id');
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
                })->where('stock', '>', 0);
        }


        if ($filter === '05') {
            //$add = ($stock > $item->stock_min);

            $query = ItemWarehouse::with(['warehouse', 'item' => function ($query) {
                $query->select('id', 'barcode', 'internal_id', 'description', 'name', 'category_id', 'brand_id', 'stock_min', 'sale_unit_price', 'purchase_unit_price', 'model', 'date_of_due', 'currency_type_id');
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
                        ->whereStockMinValidate();
                });
        }


        if ($warehouse_id != 0) {
            $query->where('item_warehouse.warehouse_id', $warehouse_id);
        }

        if ($this->params['category_id'] ?? null) $query->whereItemCategory($this->params['category_id']);

        if ($this->params['brand_id'] ?? null) $query->whereItemBrand($this->params['brand_id']);

        return $query;
    }

    /**
     * The job failed to process.
     *
     * @param \Throwable $exception
     *
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error($exception->getMessage());
    }
}
