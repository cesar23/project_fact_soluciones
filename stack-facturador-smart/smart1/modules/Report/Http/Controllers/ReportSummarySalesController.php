<?php

namespace Modules\Report\Http\Controllers;

use App\Exports\SummarySalesExport;
use App\Models\Tenant\Catalogs\DocumentType;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FunctionController;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Item\Models\WebPlatform;
use Modules\Report\Exports\SaleNoteExport;
use Illuminate\Http\Request;
use Modules\Report\Traits\ReportTrait;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\Company;
use Carbon\Carbon;
use App\Http\Resources\Tenant\SaleNoteCollection;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Document;
use App\Models\Tenant\Zone;
use Illuminate\Support\Facades\DB;

class ReportSummarySalesController extends Controller
{
    protected $d_start;
    protected $d_end;

    use ReportTrait;

    public function filter()
    {

        $document_types = DocumentType::whereIn('id', ['01', '03'])->where('active',true)->get();

        $establishments = Establishment::whereActive()->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'name' => $row->description
            ];
        });
        $zones = Zone::all()->transform(function ($row) {
            return [
                'id' => $row->id,
                'name' => $row->name
            ];
        });
        $sellers = $this->getSellers();

        $persons = $this->getPersons('customers');
        return compact(
            'zones',
            'persons',
            'document_types',
            'establishments',
            'sellers',
        );
    }


    public function index()
    {

        return view('report::summary_sales.index');
    }

    /**
     * @param Request $request
     * @return SaleNoteCollection
     */
    public function records(Request $request)
    {

        $records = $this->getSummaryRecords($request);
        // $records = $this->getRecords($request->all(), SaleNote::class);

        // return new SaleNoteCollection($records->paginate(config('tenant.items_per_page')));
        $has_records = $records->count() > 0;



        return [
            'success' => true,
            'has_records' => $has_records,
        ];
    }

    private  function getSummaryRecords(Request $request)
    {
        $onlyToPay = FunctionController::InArray($request, 'onlyToPay', false) == 'true';
        $period = FunctionController::InArray($request, 'period');
        $date_start = FunctionController::InArray($request, 'date_start');
        $date_end = FunctionController::InArray($request, 'date_end');
        $month_start = FunctionController::InArray($request, 'month_start');
        $month_end = FunctionController::InArray($request, 'month_end');
        $document_type_id = $request->input('document_type_id');
        $seller_id = $request->input('seller_id');
        $person_id = $request->input('person_id');
        $zone_id = $request->input('zone_id');
        $establishment_id = $request->input('establishment_id');
        $zones = json_decode($zone_id) ?? [];
        $sellers = json_decode($seller_id) ?? [];
        switch ($period) {
            case 'month':
                $d_start = Carbon::parse($month_start . '-01')->format('Y-m-d');
                $d_end = Carbon::parse($month_start . '-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'between_months':
                $d_start = Carbon::parse($month_start . '-01')->format('Y-m-d');
                $d_end = Carbon::parse($month_end . '-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'date':
                $d_start = $date_start;
                $d_end = $date_start;
                break;
            case 'between_dates':
                $d_start = $date_start;
                $d_end = $date_end;
                break;
        }
        $this->d_start = $d_start;
        $this->d_end = $d_end;


        // $records = Document::select(['']);
        $records = DB::connection('tenant')->table('documents')
            ->whereBetween('date_of_issue', [$d_start, $d_end]);

            if($person_id){
                $records->where('customer_id', $person_id);
            }
           $records = $records->join('users', 'documents.seller_id', '=', 'users.id')
            ->join('persons', 'documents.customer_id', '=', 'persons.id')
            ->leftJoin('zones', 'persons.zone_id', '=', 'zones.id');

        if ($document_type_id) {
            $records->where('document_type_id', $document_type_id);
        }
        if (count($zones) > 0) {
            $records->whereIn('zones.id', $zones);
        }
        if (count($sellers) > 0) {
            $records->whereIn('users.id', $sellers);
        }
        if ($establishment_id) {
            $records->where('documents.establishment_id', $establishment_id);
        }
        if($onlyToPay){
            $records->whereRaw('NOT EXISTS (SELECT * FROM document_payments WHERE document_payments.document_id = documents.id) OR (SELECT SUM(payment) FROM document_payments WHERE document_payments.document_id = documents.id) < documents.total');
        }

        $documentsQuery = $records
            ->join('invoices', 'documents.id', '=', 'invoices.document_id')
            ->orderBy('zones.name')
            ->orderBy('persons.name')
            ->orderBy('documents.seller_id')
            ->select(
                'documents.total',
                'documents.id',
                DB::raw('CONCAT(persons.name, IFNULL(CONCAT("|", persons.address), "|-")) as name_and_address'),
                DB::raw('IFNULL((SELECT SUM(payment) FROM document_payments WHERE document_id = documents.id),0) as pending'),
                'documents.currency_type_id',
                'documents.exchange_rate_sale',
                'documents.date_of_issue as date_of_issue',
                'invoices.date_of_due as date_of_due',
                DB::raw('CONCAT(documents.series, "-", documents.number) as document_number'),
                DB::raw('COALESCE(zones.name, "DESCONOCIDA") as zone_name'),
                'users.name as seller_name'
            );

        $saleNotesQuery = DB::connection('tenant')->table('sale_notes')
            ->whereBetween('date_of_issue', [$d_start, $d_end])
            ->join('users', 'sale_notes.seller_id', '=', 'users.id')->join('persons', 'sale_notes.customer_id', '=', 'persons.id')
            ->leftJoin('zones', 'persons.zone_id', '=', 'zones.id');

        if (count($zones) > 0) {
            $saleNotesQuery->whereIn('zones.id', $zones);
        }
        if (count($sellers) > 0) {
            $saleNotesQuery->whereIn('users.id', $sellers);
        }
        if ($establishment_id) {
            $saleNotesQuery->where('sale_notes.establishment_id', $establishment_id);
        }
        if($person_id){
            $saleNotesQuery->where('customer_id', $person_id);
        }
        $saleNotesQuery->whereRaw('NOT EXISTS (SELECT * FROM sale_note_payments WHERE sale_note_payments.sale_note_id = sale_notes.id) OR (SELECT SUM(payment) FROM sale_note_payments WHERE sale_note_payments.sale_note_id = sale_notes.id) < sale_notes.total');

        $saleNotesQuery = $saleNotesQuery
            // ->join('invoices', 'sale_notes.id', '=', 'invoices.document_id')
            ->orderBy('zones.name')
            ->orderBy('persons.name')
            ->orderBy('sale_notes.seller_id')
            ->select(
                'sale_notes.total',
                'sale_notes.id',
                DB::raw('CONCAT(persons.name, IFNULL(CONCAT("|", persons.address), "|-")) as name_and_address'),

                DB::raw('IFNULL((SELECT SUM(payment) FROM sale_note_payments WHERE sale_note_id = sale_notes.id),0) as pending'),
                'sale_notes.currency_type_id',
                'sale_notes.exchange_rate_sale',
                'sale_notes.date_of_issue as date_of_issue',
                'sale_notes.date_of_issue as date_of_due', // Set date_of_due to date_of_issue
                DB::raw('CONCAT(sale_notes.series, "-", sale_notes.number) as document_number'),
                DB::raw('COALESCE(zones.name, "DESCONOCIDA") as zone_name'),
                'users.name as seller_name'
            );
        $combinedQuery = $documentsQuery->union($saleNotesQuery);
        // $combinedQuery = $saleNotesQuery;
        return $combinedQuery;
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function pdf(Request $request)
    {

        $company = Company::first();
        // $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : auth()->user()->establishment;


        $records = $this->getSummaryRecords($request)->orderBy('zone_name')
            ->get()
            ->groupBy(['zone_name', 'name_and_address', 'seller_name']);
        $d_start = $this->d_start;
        $d_end = $this->d_end;
        $pdf = PDF::loadView('report::summary_sales.report_pdf', compact("records", "company", "d_end", "d_start"))->setPaper('a4');
        $filename = 'Reporte_Ventas_Resumidas_' . date('YmdHis');
        return $pdf->stream($filename . '.pdf');
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */

    public function excel(Request $request)
    {

        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : auth()->user()->establishment;

        $records = $this->getSummaryRecords($request)->orderBy('zone_name')
            ->get()
            ->groupBy(['zone_name', 'name_and_address', 'seller_name']);

        // $filters = $request->all();
        $summary_sales_export = new SummarySalesExport();
        $summary_sales_export
            ->records($records)
            ->company($company)
            ->d_start($this->d_start)
            ->d_end($this->d_end);

        return $summary_sales_export->download('Reporte_Ventas_Resumidas_' . Carbon::now() . '.xlsx');
    }
}
