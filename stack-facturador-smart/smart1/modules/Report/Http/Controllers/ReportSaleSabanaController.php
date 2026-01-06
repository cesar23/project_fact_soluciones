<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Company;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Dispatch\Models\Dispatcher;
use Modules\Report\Exports\ReportCarrierDocumentSettlemenExport;
use Modules\Report\Http\Resources\ReportCarrierDocumentSettlementCollection;
use Modules\Report\Traits\ReportTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Report\Exports\ReportCarrierDocumentExport;
use Modules\Report\Exports\ReportSalesSabanaExport;
use Modules\Report\Http\Resources\ReportCarrierDocumentCollection;
use Modules\Report\Http\Resources\ReportSalesSabanaCollection;

class ReportSaleSabanaController extends Controller
{

    use ReportTrait;

    public function index()
    {
        return view('report::sales_sabana.index');
    }
    public function filter()
    {
        $dispatchers =  Dispatcher::all();
        return compact('dispatchers');
    }
    public function columns()
    {
        return [
            'document_type_id' => 'Tipo de documento',
            'document_number' => 'Número de documento',
            'customer_internal_id' => 'Código interno',
            'customer_name' => 'Nombre del cliente',
            'sale_price' => 'Precio de venta',
            'payment_method' => 'Método de pago',
            'due' => 'Deuda',
        ];
    }
    private function getRecords($request)
    {
        $date_start = $request->date_start;
        $date_end = $request->date_end;

        $document_item_select = "document_items.id as id, " .
            "document_items.document_id as document_id, " .
            "document_items.item_id as item_id, " .
            "document_items.quantity as quantity, " .
            "document_items.unit_price as unit_price, " .
            "document_items.unit_value as unit_value, " .
            "document_items.total as total," .
            "document_items.item as item, " .
            "documents.document_type_id as document_type_id, " .
            "documents.customer_id as customer_id," .
            "documents.date_of_issue as date_of_issue, " .
            "CONCAT(documents.series,'-',documents.number) as number_full,".
            "documents.payment_condition_id as payment_condition_id," .
            "documents.order_note_id as order_note_id," .
            "documents.seller_id as seller_id";
        $sale_note_item_select = "sale_note_items.id as id, " .
            "sale_note_items.sale_note_id as document_id, " .
            "sale_note_items.item_id as item_id, " .
            "sale_note_items.quantity as quantity, " .
            "sale_note_items.unit_price as unit_price, " .
            "sale_note_items.unit_value as unit_value, " .
            "sale_note_items.total as total," .
            "sale_note_items.item as item, " .
            "'80' as document_type_id, " .
            "sale_notes.customer_id as customer_id," .
            "sale_notes.date_of_issue as date_of_issue," .
            "CONCAT(sale_notes.series,'-',sale_notes.number) as number_full,".
            "sale_notes.payment_condition_id as payment_condition_id," .
            "sale_notes.order_note_id as order_note_id," .
            "sale_notes.seller_id as seller_id";
        $document_items  = DB::connection('tenant')
            ->table('document_items')
            ->join('documents', function ($join) {
                $join->on('documents.id', '=', 'document_items.document_id')
                    ->whereNotExists(function ($query) {
                        $query->select(DB::raw('sale_notes.id'))
                            ->from('sale_notes')
                            ->whereRaw('sale_notes.id = documents.sale_note_id')
                            ->where(function ($query) {
                                $query->where('sale_notes.total_canceled', true)
                                    ->orWhere('sale_notes.paid', true);
                            });
                    })
                    ->whereIn('state_type_id', ['01', '03', '05', '07', '13'])
                    ->whereIn('document_type_id', ['01', '03', '08']);
            })
            ->select(DB::raw($document_item_select));
        $sale_note_items = DB::connection('tenant')
            ->table('sale_note_items')
            ->join('sale_notes', function ($join) {
                $join->on('sale_notes.id', '=', 'sale_note_items.sale_note_id')
                    ->where('sale_notes.changed', false)
                    ->whereIn('state_type_id', ['01', '03', '05', '07', '13']);
            })
            ->select(DB::raw($sale_note_item_select));
        $document_items
            ->whereBetween('documents.date_of_issue', [$date_start, $date_end])
            ->whereIn('documents.state_type_id',  ['01', '03', '05', '07', '13'])
            ->whereIn('documents.document_type_id', ['01', '03', '08']);

        $sale_note_items->whereBetween('sale_notes.date_of_issue', [$date_start, $date_end]);
        $records = $document_items->union($sale_note_items);

        return $records;


    }
    public function records(Request $request)
    {
        $records = $this->getRecords($request);

        // return compact('records');
        return new ReportSalesSabanaCollection($records->paginate(config('tenant.items_per_page')));
    }
    public function pdf(Request $request)
    {
        $records = $this->getRecords($request)->get();
        $company = Company::first();
        $dispatcher = Dispatcher::find($request->dispatcher_id);
        $pdf = PDF::loadView('tenant.reports.sales_sabana.report_pdf', compact("records", "company", "dispatcher"))
            ->setPaper('a4', 'landscape');

        $filename = 'Reporte_ventas_sabana_' . date('YmdHis');

        return $pdf->stream($filename . '.pdf');
    }
    public function excel(Request $request)
    {
        $records = $this->getRecords($request)->get();
        $carrier_documents = new ReportSalesSabanaExport();
        $dispatcher = Dispatcher::find($request->dispatcher_id);
        $company = Company::first();
        $carrier_documents
            ->company($company)
            ->records($records)
            ->dispatcher($dispatcher);
        return $carrier_documents->download('Reporte_ventas_sabana_' . Carbon::now() . '.xlsx');
    }
    public function dataTablePerson($type, Request $request)
    {

        $persons = $this->getDataTablePerson($type, $request);

        return compact('persons');
    }


    public function dataTableItem(Request $request)
    {

        $items = $this->getDataTableItem($request);

        return compact('items');
    }
}
