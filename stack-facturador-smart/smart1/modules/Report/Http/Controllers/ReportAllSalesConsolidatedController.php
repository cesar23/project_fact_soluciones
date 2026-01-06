<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\Company;
use App\Models\Tenant\Document;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\PersonRegModel;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\Zone;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Item\Models\Category;
use Modules\Report\Exports\ReportAllSalesConsolidatedExport;
use Modules\Report\Exports\StatusClientExport;
use Modules\Report\Http\Resources\DocumentCollection;
use Modules\Report\Http\Resources\ReportAllSalesConsolidatedCollection;
use Modules\Report\Http\Resources\SaleNoteCollection;
use Modules\Report\Traits\ReportTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Report\Http\Resources\StateAccountCollection;



class ReportAllSalesConsolidatedController extends Controller
{
    use ReportTrait;



    public function filter()
    {

        $document_types = DocumentType::whereIn('id', [
            '01', // factura
            '03', // boleta
            //'07', // nota de credito
            //'08',// nota de debito
            '80', // nota de venta
        ])->get();

        $persons = $this->getPersons('customers');
        $sellers = $this->getSellers();
        $zones = Zone::all();
        $establishments = Establishment::whereActive()->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'name' => $row->description
            ];
        });
        $users = $this->getUsers();

        return compact('document_types', 'establishments', 'persons', 'sellers', 'users', 'zones');
    }



    public function index()
    {
        return view('report::all_sales_consolidated.index');
    }
    public function getOwnRecords(Request $request)
    {
        $date_start = $request->date_start;
        $date_end = $request->date_end;
        $document_type_id = $request->document_type_id;
        $customer_id = $request->person_id;
        $establishment_id = $request->establishment_id;
        $document_payments = DB::connection('tenant')
            ->table('document_payments')
            ->select(
                'document_id',
                DB::raw('SUM(payment) as total_payment'),
                DB::raw('MAX(date_of_payment) as last_payment_date'),
                DB::raw('(SELECT payment_method_type_id 
                          FROM document_payments dp2 
                          WHERE dp2.document_id = document_payments.document_id 
                          ORDER BY date_of_payment DESC 
                          LIMIT 1) as last_payment_method_type_id')
            )
            ->groupBy('document_id');
        $sale_note_payments = DB::connection('tenant')
            ->table('sale_note_payments')
            ->select(
                'sale_note_id',
                DB::raw('SUM(payment) as total_payment'),
                DB::raw('MAX(date_of_payment) as last_payment_date'),
                DB::raw('(SELECT payment_method_type_id 
                          FROM sale_note_payments snp2 
                          WHERE snp2.sale_note_id = sale_note_payments.sale_note_id 
                          ORDER BY date_of_payment DESC 
                          LIMIT 1) as last_payment_method_type_id')
            )
            ->groupBy('sale_note_id');

        $document_select = "documents.id as id, " .
            "DATE_FORMAT(documents.date_of_issue, '%Y-%m-%d') as date_of_issue, " .
            "IFNULL(DATE_FORMAT(invoices.date_of_due, '%Y-%m-%d'), null) as date_of_due, " .
            "IFNULL(DATE_FORMAT(payments.last_payment_date, '%Y-%m-%d'), null) as last_payment_date, " .
            "payments.last_payment_method_type_id as last_payment_method_type_id, " .
            "persons.name as customer_name," .
            "persons.number as customer_number," .
            "persons.person_reg_id as customer_reg_id," .
            "persons.id as customer_id," .
            "documents.document_type_id," .
            "CONCAT(documents.series,'-',documents.number) AS number_full, " .
            "documents.total as total, " .
            "documents.total_discount as total_discount, " .
            "documents.detraction as detraction, " .
            "IFNULL(payments.total_payment, 0) as total_payment, " .
            "IFNULL(credit_notes.total_credit_notes, 0) as total_credit_notes, " .
            "documents.total - IFNULL(total_payment, 0)  - IFNULL(total_credit_notes, 0)  as total_subtraction, " .
            "'document' AS 'type', " .
            "documents.currency_type_id, " .
            "documents.exchange_rate_sale, " .
            "documents.user_id, " .
            "documents.additional_information AS observation, " .
            "sellers.name as seller_name, " .
            "users.name as username";

        $sale_note_select = "sale_notes.id as id, " .
            "DATE_FORMAT(sale_notes.date_of_issue, '%Y-%m-%d') as date_of_issue, " .
            "IFNULL(DATE_FORMAT(sale_notes.due_date, '%Y-%m-%d'), null) as date_of_due, " .
            "IFNULL(DATE_FORMAT(payments.last_payment_date, '%Y-%m-%d'), null) as last_payment_date, " .
            "payments.last_payment_method_type_id as last_payment_method_type_id, " .
            "persons.name as customer_name," .
            "persons.number as customer_number," .
            "persons.person_reg_id as customer_reg_id," .
            "persons.id as customer_id," .
            "null as document_type_id," .
            "sale_notes.filename as number_full, " .
            "sale_notes.total as total, " .
            "sale_notes.total_discount as total_discount, " .
            "sale_notes.detraction as detraction, " .
            "IFNULL(payments.total_payment, 0) as total_payment, " .
            "null as total_credit_notes," .
            "sale_notes.total - IFNULL(total_payment, 0)  as total_subtraction, " .
            "'sale_note' AS 'type', " .
            "sale_notes.currency_type_id, " .
            "sale_notes.exchange_rate_sale, " .
            "sale_notes.user_id, " .
            "sale_notes.observation AS observation, " .
            "sellers.name as seller_name, " .
            "users.name as username";

        $documents = DB::connection('tenant')
            ->table('documents')
            ->join('persons', 'persons.id', '=', 'documents.customer_id')
            ->join('users', 'users.id', '=', 'documents.user_id')
            ->join('users as sellers', 'sellers.id', '=', 'documents.seller_id')
            ->leftJoinSub($document_payments, 'payments', function ($join) {
                $join->on('documents.id', '=', 'payments.document_id');
            })
            ->leftJoin('invoices', 'invoices.document_id', '=', 'documents.id')
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
            ->join('users as sellers', 'sellers.id', '=', 'sale_notes.seller_id')
            ->leftJoinSub($sale_note_payments, 'payments', function ($join) {
                $join->on('sale_notes.id', '=', 'payments.sale_note_id');
            })
            ->whereIn('state_type_id', ['01', '03', '05', '07', '13'])
            ->select(DB::raw($sale_note_select));
            // ->where('sale_notes.changed', false)
            // ->where('sale_notes.total_canceled', false);
        if ($date_start && $date_end) {
            $documents->whereBetween('documents.date_of_issue', [$date_start, $date_end]);
            $sale_notes->whereBetween('sale_notes.date_of_issue', [$date_start, $date_end]);
        }

        if ($establishment_id) {
            $documents->where('documents.establishment_id', $establishment_id);
            $sale_notes->where('sale_notes.establishment_id', $establishment_id);
        }

        if ($customer_id) {
            $documents->where('documents.customer_id', $customer_id);
            $sale_notes->where('sale_notes.customer_id', $customer_id);
        }
        if ($document_type_id === null) {
            return $documents->union($sale_notes)->orderBy('date_of_issue', 'desc');
        } else if ($document_type_id == '80') {
            return $sale_notes->orderBy('date_of_issue', 'desc');
        } else {
            return $documents->where('documents.document_type_id', $document_type_id)
                ->orderBy('date_of_issue', 'desc');
        }
    }
    public function records(Request $request)
    {
        $records = $this->getOwnRecords($request);
        return new ReportAllSalesConsolidatedCollection($records->paginate(20));
    }

    public function excel(Request $request)
    {

        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : auth()->user()->establishment;

        $records = $this->getOwnRecords($request);
        $reportAllSalesConsolidatedExport = new ReportAllSalesConsolidatedExport();
        $reportAllSalesConsolidatedExport
            ->records(new ReportAllSalesConsolidatedCollection($records->get()))
            ->company($company)
            ->establishment($establishment);
        return $reportAllSalesConsolidatedExport->download('Reporte_ventas_consolidado' . Carbon::now() . '.xlsx');
    }
    public function pdf(Request $request)
    {

        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : auth()->user()->establishment;
        $company = Company::first();
        $records = $this->formartSalesConsolidated($this->getOwnRecords($request)->get());
        $pdf = PDF::loadView('report::all_sales_consolidated.report_pdf', compact("records", "company", "establishment"))
            ->setPaper('a4', 'landscape');
        return $pdf->stream('Reporte_ventas_consolidado_' . Carbon::now() . '.pdf');
    }
    private function formartSalesConsolidated($records)
    {
        $payment_methods = PaymentMethodType::all();
        $person_reg = PersonRegModel::all();
        $records->transform(function ($row) use ($payment_methods, $person_reg) {
            $detraction = 0;
            if ($row->detraction) {
                $detraction = json_decode($row->detraction);
                $detraction = $detraction->amount;
            }
            $now = Carbon::now();
            $date_of_issue = Carbon::parse($row->date_of_issue);
            $days_of_delay = $now->diffInDays($date_of_issue);
            return (object) [
                'id' => $row->id,
                'date_of_issue' => $row->date_of_issue,
                'year_of_issue' => Carbon::parse($row->date_of_issue)->year,
                'date_of_due' => $row->date_of_due,
                'customer_name' => $row->customer_name,
                'customer_number' => $row->customer_number,
                'customer_id' => $row->customer_id,
                'customer_reg' => $row->customer_reg_id ? $person_reg->find($row->customer_reg_id)->description : null,
                'document_type_id' => $row->document_type_id,
                'number_full' => $row->number_full,
                'total' => $row->total,
                'total_payment' => $row->total_payment,
                'total_credit_notes' => $row->total_credit_notes,
                'total_subtraction' => $row->total_subtraction,
                'type' => $row->type,
                'currency_type_id' => $row->currency_type_id,
                'exchange_rate_sale' => $row->exchange_rate_sale,
                'user_id' => $row->user_id,
                'username' => $row->username,
                'total_discount' => $row->total_discount,
                'detraction' => number_format($detraction, 2, '.', ''),
                'observation' => $row->observation,
                'total_without_detraction' => number_format($row->total - $detraction, 2, '.', ''),
                'pending' => number_format($row->total - $row->total_payment, 2, '.', ''),
                'days_of_delay' => $days_of_delay,
                'status_due' => $row->total_subtraction > 0 ? $this->statusDue($days_of_delay) : null,
                'last_payment_date' => $row->last_payment_date,
                'last_payment_method_type_id' => $row->last_payment_method_type_id ? $payment_methods->find($row->last_payment_method_type_id)->description : null,
                'seller_name' => $row->seller_name ?? $row->username,
                'total_subtraction' => $row->total_subtraction,
            ];
        });
        return $records;
    }
    private function statusDue($days_of_delay)
    {
        if ($days_of_delay >= 0 && $days_of_delay <= 15) {
            return "Por vencer";
        } else if ($days_of_delay >= 16 && $days_of_delay <= 45) {
            return "Atrasado";
        } else if ($days_of_delay > 45) {
            return "Muy atrasado";
        }
    }
    public function getCategories($records, $is_service)
    {

        $aux_categories = collect([]);

        foreach ($records as $document) {

            $id_categories = $document->items->filter(function ($row) use ($is_service) {
                return (($is_service) ? (!is_null($row->relation_item->category_id) && $row->item->unit_type_id === 'ZZ') : !is_null($row->relation_item->category_id));
            })->pluck('relation_item.category_id');

            foreach ($id_categories as $value) {
                $aux_categories->push($value);
            }
        }

        return Category::whereIn('id', $aux_categories->unique()->toArray())->get();
    }
}
