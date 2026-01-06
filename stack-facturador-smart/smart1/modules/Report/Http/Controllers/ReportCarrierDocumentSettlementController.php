<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Company;
use App\Models\Tenant\Document;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Dispatch\Models\Dispatcher;
use Modules\Report\Exports\ReportCarrierDocumentSettlemenExport;
use Modules\Report\Http\Resources\ReportCarrierDocumentSettlementCollection;
use Modules\Report\Traits\ReportTrait;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportCarrierDocumentSettlementController extends Controller
{

    use ReportTrait;

    public function index()
    {
        return view('report::carrier_document_settlement.index');
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
        $date_of_issue = $request->date_of_issue;
        $dispatcher_id = $request->dispatcher_id;
        $document_payments = DB::connection('tenant')
            ->table('document_payments')
            ->select(
                'document_id',
                DB::raw('SUM(payment) as total_payment'),
                DB::raw('CONCAT_WS("", GROUP_CONCAT(payment_method_type_id)) as payment_method_type_id')
            )
            ->groupBy('document_id');
        $sale_note_payments = DB::connection('tenant')
            ->table('sale_note_payments')
            ->select(
                'sale_note_id',
                DB::raw('SUM(payment) as total_payment'),
                DB::raw('CONCAT_WS("", GROUP_CONCAT(payment_method_type_id)) as payment_method_type_id')
            )
            ->groupBy('sale_note_id');
        $document_select = "documents.id as id, " .
        "documents.payment_condition_id as payment_condition_id, " .
            "payments.payment_method_type_id as payment_method_type_id, " .
            "DATE_FORMAT(documents.date_of_issue, '%Y/%m/%d') as date_of_issue, " .
            "persons.name as customer_name," .
            "persons.internal_code as customer_internal_id," .
            "persons.id as customer_id," .
            "documents.document_type_id," .
            "CONCAT(documents.series,'-',documents.number) AS number_full, " .
            "documents.total as total, " .
            "IFNULL(payments.total_payment, 0) as total_payment, " .
            "IFNULL(credit_notes.total_credit_notes, 0) as total_credit_notes, " .
            "documents.total - IFNULL(total_payment, 0)  - IFNULL(total_credit_notes, 0)  as total_subtraction, " .
            "'document' AS 'type', " .
            "documents.currency_type_id, " .
            "documents.exchange_rate_sale, " .
            " documents.user_id, " .
            "users.name as username, " .
            "users2.name as seller_name, " .
            "users2.number as seller_number";
        $sale_note_select = "sale_notes.id as id, " .
            "sale_notes.payment_condition_id as payment_condition_id, " .
            "payments.payment_method_type_id as payment_method_type_id, " .
            "DATE_FORMAT(sale_notes.date_of_issue, '%Y/%m/%d') as date_of_issue, " .
            "persons.name as customer_name," .
            "persons.internal_code as customer_internal_id," .
            "persons.id as customer_id," .
            "'80' as document_type_id," .
            "CONCAT(sale_notes.series,'-',sale_notes.number) as number_full, " .
            "sale_notes.total as total, " .
            "IFNULL(payments.total_payment, 0) as total_payment, " .
            "null as total_credit_notes," .
            "sale_notes.total - IFNULL(total_payment, 0)  as total_subtraction, " .
            "'sale_note' AS 'type', " .
            "sale_notes.currency_type_id, " .
            "sale_notes.exchange_rate_sale, " .
            " sale_notes.user_id, " .
            "users.name as username, " .
            "users2.name as seller_name, " .
            "users2.number as seller_number";
        $documents  = DB::connection('tenant')
            ->table('documents')
            ->join('persons', 'persons.id', '=', 'documents.customer_id')
            ->join('users', 'users.id', '=', 'documents.user_id')
            ->join('users as users2', 'users2.id', '=', 'documents.seller_id')
            ->leftJoinSub($document_payments, 'payments', function ($join) {
                $join->on('documents.id', '=', 'payments.document_id');
            })
            ->leftJoinSub(Document::getQueryCreditNotes(), 'credit_notes', function ($join) {
                $join->on('documents.id', '=', 'credit_notes.affected_document_id');
            })
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
            ->whereIn('document_type_id', ['01', '03', '08'])
            ->select(DB::raw($document_select));
        $sale_notes = DB::connection('tenant')
            ->table('sale_notes')
            ->join('persons', 'persons.id', '=', 'sale_notes.customer_id')
            ->join('users', 'users.id', '=', 'sale_notes.user_id')
            ->join('users as users2', 'users2.id', '=', 'sale_notes.seller_id')
            ->whereIn('state_type_id', ['01', '03', '05', '07', '13'])
            ->leftJoinSub($sale_note_payments, 'payments', function ($join) {
                $join->on('sale_notes.id', '=', 'payments.sale_note_id');
            })
            ->select(DB::raw($sale_note_select))
            ->where('sale_notes.changed', false);
            // ->where('sale_notes.total_canceled', false);

        $sale_notes->where('sale_notes.date_of_issue', $date_of_issue)->where('sale_notes.dispatcher_id', $dispatcher_id);
        $documents->where('documents.date_of_issue', $date_of_issue)->where('documents.dispatcher_id', $dispatcher_id);

        $records = $documents->union($sale_notes);

        return $records;
    }
    public function records(Request $request)
    {
        $records = $this->getRecords($request);


        return new ReportCarrierDocumentSettlementCollection($records->paginate(config('tenant.items_per_page')));
    }
    public function pdf(Request $request)
    {
        $records = $this->getRecords($request)->get();
        $carrier_documents = new ReportCarrierDocumentSettlemenExport();
        $company = Company::first();
        $date_of_issue = $request->date_of_issue;
        $dispatcher = Dispatcher::find($request->dispatcher_id);
        $pdf = PDF::loadView('tenant.reports.carrier_document_settlement.report_pdf', compact("records", "company", "dispatcher","date_of_issue"))
            ->setPaper('a4', 'portrait');

        $filename = 'Reporte_Liquidacion_Transportista_' . date('YmdHis');

        return $pdf->stream($filename . '.pdf');
    }
    public function excel(Request $request)
    {
        $records = $this->getRecords($request)->get();
        $carrier_documents = new ReportCarrierDocumentSettlemenExport();
        $dispatcher = Dispatcher::find($request->dispatcher_id);
        $company = Company::first();
        $date_of_issue = $request->date_of_issue;
        $carrier_documents
            ->company($company)
            ->date_of_issue($date_of_issue)
            ->records($records)
            ->dispatcher($dispatcher);
        return $carrier_documents->download('Reporte_Liquidacion_Transportista_' . Carbon::now() . '.xlsx');
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
