<?php

namespace Modules\Report\Http\Controllers;

use App\Exports\PackerDispatcherSalesExport;
use App\Exports\SellerSalesExport;
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
use App\Models\Tenant\Document;
use App\Models\Tenant\Zone;
use Illuminate\Support\Facades\DB;

class ReportPackerDispatcherController extends Controller
{
    protected $d_start;
    protected $d_end;

    use ReportTrait;

    public function filter()
    {

        $document_types = DocumentType::whereIn('id', ['01', '03'])->where('active', true)->get();

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

        return view('report::packer_dispatcher.index');
    }

    /**
     * @param Request $request
     * @return SaleNoteCollection
     */
    public function records(Request $request)
    {

        $records = $this->getPackerDispatcherRecords($request);
        // $records = $this->getRecords($request->all(), SaleNote::class);

        // return new SaleNoteCollection($records->paginate(config('tenant.items_per_page')));
        $has_records = $records->count() > 0;



        return [
            'success' => true,
            'has_records' => $has_records,
        ];
    }

    private  function getPackerDispatcherRecords(Request $request)
    {
        $period = FunctionController::InArray($request, 'period');
        $date_start = FunctionController::InArray($request, 'date_start');
        $date_end = FunctionController::InArray($request, 'date_end');
        $month_start = FunctionController::InArray($request, 'month_start');
        $month_end = FunctionController::InArray($request, 'month_end');
        $type = FunctionController::InArray($request, 'type');
        $establishment_id = $request->input('establishment_id');
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
        $records = DB::connection('tenant')->table('documents')
            ->whereBetween('date_of_issue', [$d_start, $d_end]);

        $records = $records->join('users', 'documents.seller_id', '=', 'users.id')
            ->join('persons', 'documents.customer_id', '=', 'persons.id')
            ->leftJoin('person_dispatchers', 'documents.person_dispatcher_id', '=', 'person_dispatchers.id')
            ->leftJoin('person_packers', 'documents.person_packer_id', '=', 'person_packers.id')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw('sale_notes.id'))
                    ->from('sale_notes')
                    ->whereRaw('sale_notes.id = documents.sale_note_id')
                    ->where(function ($query) {
                        $query->where('sale_notes.total_canceled', true)
                            ->orWhere('sale_notes.paid', true);
                    });
            });
        
        if ($establishment_id) {
            $records->where('documents.establishment_id', $establishment_id);
        }

        if($type == 'dispatcher'){
            $records->whereNotNull('documents.person_dispatcher_id');
        }else{
            $records->whereNotNull('documents.person_packer_id');
        }

        $documentsQuery = $records
            ->orderBy('persons.name')
            ->orderBy('documents.seller_id')
            ->select(
                'documents.total',
                'documents.id',
                DB::raw('CONCAT(persons.name, IFNULL(CONCAT("|", persons.address), "|-"), IFNULL(CONCAT("|", persons.telephone), "|-")) as customer'),
                DB::raw('IFNULL((SELECT SUM(payment) FROM document_payments WHERE document_id = documents.id),0) as pending'),
                'documents.date_of_issue as date_of_issue',
                'documents.date_of_issue as date_of_due',
                'documents.total_canceled as total_canceled',
                DB::raw('CONCAT(documents.series, "-", documents.number) as document_number'),
                'users.name as seller_name',
                'person_dispatchers.name as dispatcher_name',
                'person_packers.name as packer_name'
            );

        $saleNotesQuery = DB::connection('tenant')->table('sale_notes')
            ->whereBetween('date_of_issue', [$d_start, $d_end])
            ->join('users', 'sale_notes.seller_id', '=', 'users.id')->join('persons', 'sale_notes.customer_id', '=', 'persons.id')
            ->leftJoin('person_dispatchers', 'sale_notes.person_dispatcher_id', '=', 'person_dispatchers.id')
            ->leftJoin('person_packers', 'sale_notes.person_packer_id', '=', 'person_packers.id');
        if ($establishment_id) {
            $saleNotesQuery->where('sale_notes.establishment_id', $establishment_id);
        }
        if($type == 'dispatcher'){
            $saleNotesQuery->whereNotNull('sale_notes.person_dispatcher_id');
        }else{
            $saleNotesQuery->whereNotNull('sale_notes.person_packer_id');
        }


        $saleNotesQuery = $saleNotesQuery
            ->orderBy('persons.name')
            ->orderBy('sale_notes.seller_id')
            ->select(
                'sale_notes.total',
                'sale_notes.id',
                DB::raw('CONCAT(persons.name, IFNULL(CONCAT("|", persons.address), "|-"), IFNULL(CONCAT("|", persons.telephone), "|-")) as customer'),
                DB::raw('IFNULL((SELECT SUM(payment) FROM sale_note_payments WHERE sale_note_id = sale_notes.id),0) as pending'),
                'sale_notes.date_of_issue as date_of_issue',
                'sale_notes.date_of_issue as date_of_due', // Set date_of_due to date_of_issue
                'sale_notes.total_canceled as total_canceled',
                DB::raw('CONCAT(sale_notes.series, "-", sale_notes.number) as document_number'),
                'users.name as seller_name',
                'person_dispatchers.name as dispatcher_name',
                'person_packers.name as packer_name'
            );

        $combinedQuery = $documentsQuery->union($saleNotesQuery);

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
        $type = $request->type;
        $order = "";
        if($type == 'dispatcher'){
            $order = 'dispatcher_name';
        }else{
            $order = 'packer_name';
        }

        $records = $this->getPackerDispatcherRecords($request)->orderBy($order)
        ->get()
        ->groupBy([$order]);
        $d_start = $this->d_start;
        $d_end = $this->d_end;
        $pdf = PDF::loadView('report::packer_dispatcher.report_pdf', compact(
            "type",
            "records", "company", "d_end", "d_start"))->setPaper('a4', 'landscape');
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
        $type = $request->type;
        $order = "";
        if($type == 'dispatcher'){
            $order = 'dispatcher_name';
        }else{
            $order = 'packer_name';
        }
        $records = $this->getPackerDispatcherRecords($request)->orderBy($order)
            ->get()
            ->groupBy([$order]);

        // $filters = $request->all();
        $summary_sales_export = new PackerDispatcherSalesExport();
        $summary_sales_export
            ->records($records)
            ->company($company)
            ->type($type)
            ->d_start($this->d_start)
            ->d_end($this->d_end);

        return $summary_sales_export->download('Reporte_Ventas_Resumidas_' . Carbon::now() . '.xlsx');
    }
}
