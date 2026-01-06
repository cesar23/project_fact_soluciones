<?php

namespace Modules\Report\Http\Controllers;

use App\Models\Tenant\Catalogs\DocumentType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\CreditNoteReportCollection;
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
use App\Models\Tenant\Note;
use App\Models\Tenant\StateType;
use Illuminate\Support\Facades\DB;
use Modules\Report\Exports\NoteCreditExport;

class ReportCreditNoteController extends Controller
{
    use ReportTrait;


    public function filter()
    {


        $establishments = Establishment::whereActive()->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'name' => $row->description
            ];
        });

        $sellers = $this->getSellers();
        $users = $this->getUsers();



        return compact(
            'users',
            'establishments',
            'sellers',
        );
    }


    public function index()
    {

        return view('report::credit_notes.index');
    }

    /**
     * @param Request $request
     * @return SaleNoteCollection
     */
    public function records(Request $request)
    {
        $records = $this->getRecordsNotes($request);

        return new CreditNoteReportCollection($records->paginate(config('tenant.items_per_page')));
    }
    private function getRecordsNotes($request){
        $states_internal = ["55","56"];
        $d_start = null;
        $d_end = null;
        $period = $request->period;
        $month_start = $request->month_start;
        $month_end = $request->month_end;
        $date_start = $request->date_start;
        $date_end = $request->date_end;
        $establishment_id = $request->establishment_id;
        $user_id = $request->user_id;
        $user_type = $request->user_type;
        $state = $request->state_type_id;
        $customer_id = $request->person_id;
        $note_number = $request->guides;
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

        $records = Document::where('document_type_id', '07');

        if($d_start && $d_end){
            $records->whereBetween('date_of_issue', [$d_start, $d_end]);
        }
        if($establishment_id){
            $records->where('establishment_id', $establishment_id);
        }
        if($user_id){
            if($user_type == 'CREADOR'){
                $records->where('user_id', $user_id);
            }else{
                $records->where('seller_id', $user_id);
            }
        }
        if($customer_id){
            $records->where('customer_id', $customer_id);
        }
        if($note_number){
            $records->where('number', 'like', '%'.$note_number.'%');
        }
        if($state){
            if($state == '01'){
                $records->whereNotIn('state_type_id', $states_internal);
            }else{
                $records->whereIn('state_type_id', $states_internal);
            }
        }
        
        return $records;
    }

    

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function pdf(Request $request)
    {

        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : auth()->user()->establishment;
        $records = $this->getRecordsNotes($request)->get();
        $records = $this->transformRecords($records);

        $pdf = PDF::loadView('report::credit_notes.report_pdf', compact("records", "company", "establishment"))->setPaper('a4', 'landscape');
        $filename = 'Reporte_Notas_de_Credito_' . date('YmdHis');
        return $pdf->stream($filename . '.pdf');
    }

    private function transformRecords($records){
        $credit_notes_ids = $records->pluck('id')->toArray();
        $connection = DB::connection('tenant');
        $notes_data = $connection->table('notes')->whereIn('notes.document_id', $credit_notes_ids)
            ->leftJoin('documents', 'documents.id', '=', 'notes.affected_document_id')
            ->leftJoin('sale_notes', 'sale_notes.id', '=', 'notes.affected_sale_note_id')
            ->select(
                'notes.id as note_id',
                'notes.*',
                'documents.series as document_series',
                'documents.number as document_number',
                'sale_notes.number as sale_note_number',
                'sale_notes.series as sale_note_series'
            )
            ->get()->keyBy('document_id');
        $notes_data_ids = $connection->table('notes')->whereIn('notes.document_id', $credit_notes_ids)
        ->pluck('id')->toArray();
    
        $used_data = $connection->table('payments_with_credit_note')->whereIn('note_id', $notes_data_ids)
        ->join('notes', 'notes.id', '=', 'payments_with_credit_note.note_id')
        ->leftJoin('document_payments', 'document_payments.id', '=', 'payments_with_credit_note.document_payment_id')
        ->leftJoin('documents', 'documents.id', '=', 'document_payments.document_id')
        ->leftjoin('sale_note_payments', 'sale_note_payments.id', '=', 'payments_with_credit_note.sale_note_payment_id')
        ->leftjoin('sale_notes', 'sale_notes.id', '=', 'sale_note_payments.sale_note_id')
        ->leftjoin('expense_payments', 'expense_payments.id', '=', 'payments_with_credit_note.expense_payment_id')
        ->leftjoin('expenses', 'expenses.id', '=', 'expense_payments.expense_id')
        ->select(
            'notes.id as note_id',
            'notes.document_id as note_document_id',
            'payments_with_credit_note.*',
            'document_payments.payment as document_payment_amount',
            'sale_note_payments.payment as sale_note_payment_amount',
            'expense_payments.payment as expense_payment_amount',
            'expenses.number as expense_number',
            DB::raw("'GASTO' as expense_series"),
            'documents.number as document_number',
            'documents.series as document_series',
            'sale_notes.number as sale_note_number',
            'sale_notes.series as sale_note_series'
        )
        ->get()->keyBy('note_document_id');
        $users_data = $connection->table('users')->whereIn('id', $records->pluck('user_id')->toArray())->get()->keyBy('id');
        $states_internal = ["55", "56"];
        return $records->transform(function ($row, $key) use ($states_internal, $notes_data, $used_data, $users_data) {
            $is_internal = in_array($row->state_type_id, $states_internal);
            
            // Usar get() para evitar errores de índice indefinido
            $document_affected = $notes_data->get($row->id, null);
            $document_affected_number = null;
            if($document_affected){
                if($document_affected->sale_note_number){
                    $document_affected_number = $document_affected->sale_note_series . '-' . $document_affected->sale_note_number;
                }else{
                    $document_affected_number = $document_affected->document_series . '-' . $document_affected->document_number;
                }
            }
            $used_data_item = $used_data->get($row->id, null);
            $used_affected_number = null;
            if($used_data_item){
                if($used_data_item->sale_note_number){
                    $used_affected_number = $used_data_item->sale_note_series . '-' . $used_data_item->sale_note_number;
                }else if($used_data_item->expense_number){
                    $used_affected_number = $used_data_item->expense_series . '-' . $used_data_item->expense_number;
                }else{
                    $used_affected_number = $used_data_item->document_series . '-' . $used_data_item->document_number;
                }
            }
            $date_of_issue = $row->date_of_issue;
            if(is_string($date_of_issue)){
                $date_of_issue = Carbon::parse($date_of_issue);
            }
            $date_of_issue = $date_of_issue->format('Y-m-d');
            return [
                'id' => $row->id,
                'date_of_issue' => $date_of_issue,
                'time_of_issue' => $row->time_of_issue,
                'user_name' => $users_data->get($row->user_id, null)->name,
                'customer_name' => $row->customer->name,
                'number_full' => $row->number_full,
                'internal' => (bool) $is_internal,
                'currency_type_id' => $row->currency_type_id,
                'region' => 'Lima',
                'document_affected' => $document_affected_number,
                'document_used' => $used_affected_number,
                'total' => number_format($row->total, 2, '.', ''),
            ];
        });
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function excel(Request $request)
    {

        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : auth()->user()->establishment;

        $records = $this->getRecordsNotes($request)->get();
        $records = $this->transformRecords($records);
        $filters = $request->all();
        $NoteCreditExport = new NoteCreditExport();
        $NoteCreditExport
            ->records($records)
            ->company($company)
            ->establishment($establishment);

        return $NoteCreditExport->download('Reporte_Nota_de_Credito_' . Carbon::now() . '.xlsx');
    }
}
