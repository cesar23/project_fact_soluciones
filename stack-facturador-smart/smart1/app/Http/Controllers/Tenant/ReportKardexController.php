<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\KardexExport;
use Illuminate\Http\Request;
use App\Models\Tenant\{
    Establishment,
    Company,
    Kardex,
    Item,
    ItemProperty
};
use Carbon\Carbon;
use Modules\Inventory\Models\Guide;

class ReportKardexController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = Item::query()
            ->where('item_type_id', '01')
            ->latest()
            ->get();

        return view('tenant.reports.kardex.index', compact('items'));
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

    public function filter_attributes(Request $request){
        $data = ItemProperty::where('item_id',$request->item_id)->where('chassis',$request->chassis)->get();
        return response()->json([
            "chasis" => $data
        ]);
    }

    /**
     * Search
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $balance = 0;

        $items = Item::query()
            ->where('item_type_id', '01')
            ->latest()
            ->get();

        $reports = Kardex::query()
            ->with(['document', 'purchase', 'item' => function ($queryItem) {
                return $queryItem->where('item_type_id', '01');
            }])
            ->where('item_id', $request->item_id)
            ->orderBy('id')
            ->get();


        return view('tenant.reports.kardex.index', compact('items', 'reports', 'balance'));
    }

    /**
     * PDF
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function pdf(Request $request)
    {
        $balance = 0;
        $company = Company::first();
        $establishment = Establishment::first();

        $reports = Kardex::query()
            ->with(['document', 'purchase', 'item' => function ($queryItem) {
                return $queryItem->where('item_type_id', '01');
            }])
            ->where('item_id', $request->item_id)
            ->orderBy('id')
            ->get();

        $pdf = PDF::loadView('tenant.reports.kardex.report_pdf', compact("reports", "company", "establishment", "balance"));
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
        $balance = 0;
        $company = Company::first();
        $establishment = Establishment::first();

        $records = Kardex::query()
            ->with(['document', 'purchase', 'item' => function ($queryItem) {
                return $queryItem->where('item_type_id', '01');
            }])
            ->where('item_id', $request->item_id)
            ->orderBy('id')
            ->get();

        return (new KardexExport)
            ->balance($balance)
            ->records($records)
            ->company($company)
            ->establishment($establishment)
            ->download('ReporteKar' . Carbon::now() . '.xlsx');
    }
}
