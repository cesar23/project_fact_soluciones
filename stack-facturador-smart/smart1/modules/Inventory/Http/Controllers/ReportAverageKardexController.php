<?php

namespace Modules\Inventory\Http\Controllers;

use Carbon\Carbon;
use App\Models\Tenant\Item;
use Illuminate\Http\Request;
use App\Models\Tenant\Kardex;
use App\Models\Tenant\Company;
use App\Models\Tenant\Dispatch;
use Modules\Item\Models\ItemLot;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\AverageHistory;
use Modules\Item\Models\ItemLotsGroup;
use Modules\Inventory\Models\Warehouse;

use Modules\Inventory\Models\Devolution;
use Modules\Inventory\Exports\KardexExport;
use Modules\Inventory\Models\ItemWarehouse;

use Modules\Inventory\Models\InventoryKardex;
use Modules\Inventory\Http\Resources\ReportKardexCollection;
use Modules\Inventory\Http\Resources\ReportKardexLotsCollection;
use Modules\Inventory\Http\Resources\ReportKardexItemLotCollection;
use Modules\Inventory\Http\Resources\ReportKardexInventoryCollection;
use Modules\Inventory\Http\Resources\ReportKardexLotsGroupCollection;

class ReportAverageKardexController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    protected $models = [
        "App\Models\Tenant\Document",
        "App\Models\Tenant\Purchase",
        "App\Models\Tenant\SaleNote",
        "Modules\Inventory\Models\Inventory",
        "Modules\Order\Models\OrderNote",
        Devolution::class,
        Dispatch::class
    ];

    public function index()
    {


        return view('inventory::reports.average_kardex.index');
    }

    public function filter()
    {

        $items = Item::query()->whereNotIsSet()
            ->where([['item_type_id', '01'], ['unit_type_id', '!=', 'ZZ']])
            ->latest()
            ->get()->transform(function ($row) {
                $full_description = $this->getFullDescription($row);
                return [
                    'id' => $row->id,
                    'full_description' => $full_description,
                    'internal_id' => $row->internal_id,
                    'description' => $row->description,
                ];
            });

        return compact('items');
    }


    public function records(Request $request)
    {
        $records = $this->getRecords($request->all());
        if ($request->page == 1) {
            session(['total_saldo' => 0]);
            session(['cost_purchase' => 0]);
            session(['total_saldo' => 0]);
            $registros = new ReportKardexInventoryCollection($records->get());
            $data = collect($registros);
            $count = count($data);
            $ii = 0;
            foreach ($data as $key =>  $rows) {
                $ii++;
                if ($rows['type_transaction'] == "Compra" || $rows['type_transaction'] == "Venta") {
                    if ($rows['type_transaction'] == "Compra") {
                        //         dd($rows);
                        
                        $this->save_average_history(null, null, $rows['purchase_id'], $rows['purchase_cost'], $rows['total_purchase_cost'], $rows['price_balance'], $rows['input'], $rows['output'], $rows['balance'], $rows['type_transaction'], $rows['total_balance'], $rows['total_sales'], $rows['sales_cost'], $rows['number']);
                    } else if ($rows['type_transaction'] == "Venta") {
                        if ($rows['document_id'] != null || $rows['sale_note_id'] == null) {
                            $this->save_average_history($rows['document_id'], null, null, $rows['purchase_cost'], $rows['total_purchase_cost'], $rows['price_balance'], $rows['input'], $rows['output'], $rows['balance'], $rows['type_transaction'], $rows['total_balance'], $rows['total_sales'], $rows['sales_cost'], $rows['number']);
                        } else if ($rows['sale_note_id'] == null && $rows['document_id'] != null) {
                            $this->save_average_history(null, null, $rows['sale_note_id'], $rows['purchase_cost'], $rows['total_purchase_cost'], $rows['price_balance'], $rows['input'], $rows['output'], $rows['balance'], $rows['type_transaction'], $rows['total_balance'], $rows['total_sales'], $rows['sales_cost'], $rows['number']);
                        }
                    }
                }
            }
        }

        if ($request->page == 1) {
            session(['total_saldo' => 0]);
            session(['cost_purchase' => 0]);
            session(['total_saldo' => 0]);
        }
        return new ReportKardexCollection($records->paginate(config('tenant.items_per_page')));
    }
    public function save_average_history($id_document = null, $sale_note_id = null, $id_purchase = null, $purchase_cost, $total_purchase_cost, $price_balance, $input, $output, $balance, $type_transaction, $total_balance, $total_sales, $sales_cost, $number_full = null)
    {
        if ($number_full != null) {
            $number_full = explode("-", $number_full);
            $serie = $number_full[0];
            $number = $number_full[1];
        } else {
            $serie = null;
            $number = null;
        }

        if ($id_document != null) {
            AverageHistory::updateOrCreate(['id_document' => $id_document], [
                'id_document' => $id_document,
                'sale_note_id' => null,
                'id_purchase' => null,
                'purchase_cost' => $purchase_cost,
                'total_purchase_cost' => $total_purchase_cost,
                'price_balance' => $price_balance,
                'input' => ($input > 0) ? $input : 0,
                'output' => ($output > 0) ? $output : 0,
                'balance' => ($balance > 0) ? $balance : 0,
                'total_sales' => $total_sales,
                'sales_cost' => $sales_cost,
                'total_balance' => $total_balance,
                'type_transaction' => $type_transaction,
                'serie' => $serie,
                'number' => $number
            ]);
        }
        if ($sale_note_id != null) {
            AverageHistory::updateOrCreate(['sale_note_id' => $sale_note_id], [
                'id_document' => null,
                'sale_note_id' => $sale_note_id,
                'id_purchase' => null,
                'purchase_cost' => $purchase_cost,
                'total_purchase_cost' => $total_purchase_cost,
                'price_balance' => $price_balance,
                'input' => ($input > 0) ? $input : 0,
                'output' => ($output > 0) ? $output : 0,
                'balance' => ($balance > 0) ? $balance : 0,
                'total_sales' => $total_sales,
                'sales_cost' => $sales_cost,
                'total_balance' => $total_balance,
                'type_transaction' => $type_transaction,
                'series' => $serie,
                'number' => $number

            ]);
        }

        if ($id_purchase != null) {
            if ($number_full != null) {
                $serie_purchase = $number_full[0];
                $number_purchase = $number_full[1];
            }
            //dd($serie_purchase,$number_purchase);
            AverageHistory::updateOrCreate(['id_purchase' => $id_purchase], [
                'id_document' => null,
                'sale_note_id' => null,
                'id_purchase' => $id_purchase,
                'purchase_cost' => $purchase_cost,
                'total_purchase_cost' => $total_purchase_cost,
                'price_balance' => $price_balance,
                'input' => ($input > 0) ? $input : 0,
                'output' => ($output > 0) ? $output : 0,
                'balance' => ($balance > 0) ? $balance : 0,
                'total_balance' => $total_balance,
                'total_sales' => $total_sales,
                'sales_cost' => $sales_cost,
                'type_transaction' => $type_transaction,
                'serie' => $serie_purchase,
                'number' => $number_purchase

            ]);
        }
    }
    public function records_lots()
    {
        $records = ItemWarehouse::with(['item'])->whereHas('item', function ($q) {
            $q->where([['item_type_id', '01'], ['unit_type_id', '!=', 'ZZ'], ['lot_code', '!=', null]]);
            $q->whereNotIsSet();
        });

        return new ReportKardexLotsCollection($records->paginate(config('tenant.items_per_page')));
    }
    public function getRecords($request)
    {

        $item_id = $request['item_id'];
        $date_start = $request['date_start'];
        $date_end = $request['date_end'];

        $records = $this->data($item_id, $date_start, $date_end);

        return $records;
    }


    /**
     * @param $item_id
     * @param $date_start
     * @param $date_end
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|InventoryKardex
     */
    private function data($item_id, $date_start, $date_end)
    {

        $warehouse = Warehouse::where('establishment_id', auth()->user()->establishment_id)->first();

        $data = InventoryKardex::with(['inventory_kardexable'])
            ->where('warehouse_id', $warehouse->id);
        if ($date_start) {
            $data->where('date_of_issue', '>=', $date_start);
        }
        if ($date_end) {
            $data->where('date_of_issue', '<=', $date_end);
        }
        if ($item_id) {
            $data->where('item_id', $item_id);
        }
        


        $data->orderBy('item_id')
            ->orderBy('id');
        return $data;
    }


    public function getFullDescription($row)
    {

        $desc = ($row->internal_id) ? $row->internal_id . ' - ' . $row->description : $row->description;
        $category = ($row->category) ? " - {$row->category->name}" : "";
        $brand = ($row->brand) ? " - {$row->brand->name}" : "";

        $desc = "{$desc} {$category} {$brand}";

        return $desc;
    }

    public function pdf(Request $request)
    {
        // session(['cost_purchase' => 0]);
        $records = $this->getRecords($request->all());

        $reports = new ReportKardexCollection($records->get());

        $company = Company::first();
        $establishment = Establishment::first();
        $d = $request->date_start;
        $a = $request->date_end;

        $item_id = $request->item_id;

        $item = Item::findOrFail($request->item_id);

        $warehouse = Warehouse::where('establishment_id', auth()->user()->establishment_id)->first();
        $balance = 0;
        $pdf = PDF::loadView('inventory::reports.average_kardex.report_pdf', compact("reports", "company", "establishment", 'item', "balance"))->setPaper('a4', 'landscape');
        $filename = 'Reporte_Kardex' . date('YmdHis');

        return $pdf->stream($filename . '.pdf');
        /*
        $balance = 0;
        $company = Company::first();
        $establishment = Establishment::first();
        $d = $request->date_start;
        $a = $request->date_end;
        $item_id = $request->item_id;
        $item = Item::findOrFail($request->item_id);

        $warehouse = Warehouse::where('establishment_id', auth()->user()->establishment_id)->first();

        if ($d && $a) {

            $reports = InventoryKardex::with(['inventory_kardexable'])
                ->where([['warehouse_id', $warehouse->id]])
                ->whereBetween('date_of_issue', [$d, $a])
                ->orderBy('item_id')->orderBy('id')
                ->get();

        } else {

            $reports = InventoryKardex::with(['inventory_kardexable'])
                ->where([['warehouse_id', $warehouse->id]])
                ->orderBy('item_id')->orderBy('id')
                ->get();
        }

        if ($item_id) {
            $reports = $reports->where('item_id', $item_id);
        }
       
        $models = $this->models;
        $userWarehouse = auth()->user()->establishment_id;
        
        $pdf = PDF::loadView('inventory::reports.kardex.report_pdf', compact("reports", "company", "establishment", "balance", "models", 'a', 'd', "item_id", 'userWarehouse', 'item'));
        $filename = 'Reporte_Kardex' . date('YmdHis');

        return $pdf->download($filename . '.pdf');*/
    }

    /**
     * Excel
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function excel(Request $request)
    {

        $balance = 0;
        $company = Company::first();
        $establishment = Establishment::first();
        $d = $request->date_start;
        $a = $request->date_end;
        $item_id = $request->item_id;
        $item = Item::findOrFail($request->item_id);

        $warehouse = Warehouse::where('establishment_id', auth()->user()->establishment_id)->first();

        if ($d && $a) {

            $records = InventoryKardex::with(['inventory_kardexable'])
                ->where([['warehouse_id', $warehouse->id]])
                ->whereBetween('date_of_issue', [$d, $a])
                ->orderBy('item_id')->orderBy('id')
                ->get();
        } else {

            $records = InventoryKardex::with(['inventory_kardexable'])
                ->where([['warehouse_id', $warehouse->id]])
                ->orderBy('item_id')->orderBy('id')
                ->get();
        }

        if ($item_id) {
            $records = $records->where('item_id', $item_id);
        }

        $models = $this->models;
        $kardexExport = new KardexExport();
        $kardexExport
            ->balance($balance)
            ->item_id($item_id)
            ->records($records)
            ->models($models)
            ->company($company)
            ->establishment($establishment)
            ->item($item);

        return $kardexExport->download('ReporteKar' . Carbon::now() . '.xlsx');
    }

    public function getRecords2($request)
    {

        $item_id = $request['item_id'];
        $date_start = $request['date_start'];
        $date_end = $request['date_end'];

        $records = $this->data2($item_id, $date_start, $date_end);

        return $records;
    }


    private function data2($item_id, $date_start, $date_end)
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


        return $data;
    }

    public function records_lots_kardex(Request $request)
    {
        $records = $this->getRecords2($request->all());

        return new ReportKardexLotsGroupCollection($records->paginate(config('tenant.items_per_page')));
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

}
