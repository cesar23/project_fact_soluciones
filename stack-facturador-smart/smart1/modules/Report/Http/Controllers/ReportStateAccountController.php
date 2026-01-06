<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FunctionController;
use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\Company;
use App\Models\Tenant\Document;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\Zone;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Item\Models\Category;
use Modules\Report\Exports\StatusClientExport;
use Modules\Report\Http\Resources\DocumentCollection;
use Modules\Report\Http\Resources\SaleNoteCollection;
use Modules\Report\Traits\ReportTrait;
use Modules\Report\Traits\StateAccountPdfTrait;
use Modules\Report\Exports\RelationSalesExport;

use Modules\Report\Http\Resources\StateAccountCollection;

use DB;


class ReportStateAccountController extends Controller
{
    use ReportTrait, StateAccountPdfTrait;

    private function getPeriod(Request $request){
        $period = FunctionController::InArray($request, 'period');
        $date_start = FunctionController::InArray($request, 'date_start');
        $date_end = FunctionController::InArray($request, 'date_end');
        $month_start = FunctionController::InArray($request, 'month_start');
        $month_end = FunctionController::InArray($request, 'month_end');
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
        return [
            'd_start' => $d_start,
            'd_end' => $d_end
        ];
    }

    public function relationSalesPdf(Request $request)
    {
        $establishment_id = $request->establishment_id;
        $establishment = null;
        if($establishment_id){
            $establishment = Establishment::find($establishment_id);
        }
        $company = Company::first();
        $period = $this->getPeriod($request);
        
        $request->merge(['to_export' => 'true']);
        $groupedRecords = $this->getRelationSalesForPdf($request);
        $formattedRecords = $this->formatRecordsForPdf($groupedRecords);
        
        $pdf = Pdf::loadView('report::relation_sales.report_pdf', compact(
            "formattedRecords",
            "company",
            "establishment",
            "period"
        ))
        ->setPaper('a4', 'portrait');

        $filename = "Estado_de_cuenta_".date('YmdHis');

        return $pdf->stream($filename . '.pdf');
    }
    public function relationSalesExcel(Request $request)
    {
        $establishment_id = $request->establishment_id;
        $establishment = null;
        if($establishment_id){
            $establishment = Establishment::find($establishment_id);
        }
        $company = Company::first();
        $period = $this->getPeriod($request);
        
        $request->merge(['to_export' => 'true']);
        $groupedRecords = $this->getRelationSalesForPdf($request);
        $formattedRecords = $this->formatRecordsForPdf($groupedRecords);
        
        $filename = "Relacion_ventas_" . date('YmdHis') . '.xlsx';
        
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \Modules\Report\Exports\RelationSalesExport(
                $formattedRecords,
                $company,
                $establishment,
                $period
            ),
            $filename
        );
    }

    private function getRelationSales($request, $type)
    {
        return $this->records($request);
    }


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

        return compact('document_types', 'establishments', 'persons', 'sellers', 'users','zones');
    }



    public function index()
    {
        return view('report::state_account.index');
    }

    public function records(Request $request)
    {
        $documentTypeId = null;
        if ($request->has('document_type_id')) {
            $documentTypeId = str_replace('"', '', $request->document_type_id);
        };

        $include_cash_sales = $request->include_cash_sales == 'true' ? true : false;
        $include_credit_sales = $request->include_credit_sales == 'true' ? true : false;
        $to_export = $request->to_export == 'true' ? true : false;
        $documentType = DocumentType::find($documentTypeId);

        if ($documentType != null) {
            $classType = $documentType->getCurrentRelatiomClass();

            $records = $this->getRecords($request->all(), $classType);

            if(!$include_cash_sales || !$include_credit_sales){
                $payment_conditions = [];
                if($include_cash_sales) $payment_conditions[] = '01';
                if($include_credit_sales) $payment_conditions[] = '02';
                
                if(!empty($payment_conditions)){
                    $records = $records
                        ->whereIn('payment_condition_id', $payment_conditions)
                        ->with('payments');
                }
            }

            if ($classType == SaleNote::class) {
                return new SaleNoteCollection( $to_export ? $records->get() : $records->paginate(config('tenant.items_per_page')));
            } else {
                return new DocumentCollection( $to_export ? $records->get() : $records->paginate(config('tenant.items_per_page')));
            };
        } else {
            $records_documents = $this->getRecords($request->all(), Document::class)->select(
                'id',
                'state_type_id',
                'payment_condition_id',
                'soap_type_id',
                'date_of_issue',
                'currency_type_id',
                'series',
                'establishment_id',
                'number',
                'purchase_order',
                'seller_id',
                'customer_id',
                'total_exportation',
                'total_exonerated',
                'total_unaffected',
                'total_free',
                'total_taxed',
                'total_igv',
                'total',
                'total_isc'
            )->with(['person' => function ($query) {
                $query->select('id', 'name', 'number', 'address','contact');
            }])->with(['soap_type' => function ($q) {
                $q->select('id', 'description');
            }])->with(['state_type' => function ($y) {
                $y->select('id', 'description');
            }])->with(['user' => function ($y) {
                $y->select('id', 'name');
            }])->with('items');
            // Aplicar filtros de condición de pago solo si no se incluyen ambos tipos
            if(!$include_cash_sales || !$include_credit_sales){
                $payment_conditions = [];
                if($include_cash_sales) $payment_conditions[] = '01';
                if($include_credit_sales) $payment_conditions[] = '02';
                
                if(!empty($payment_conditions)){
                    $records_documents = $records_documents
                        ->whereIn('payment_condition_id', $payment_conditions)
                        ->with('payments');
                }
            }

            $records_sales = $this->getRecords($request->all(), SaleNote::class)->select(
                'id',
                'state_type_id',
                'payment_condition_id',
                'soap_type_id',
                'date_of_issue',
                'currency_type_id',
                'series',
                'establishment_id',
                'number',
                'purchase_order',
                'seller_id',
                'customer_id',
                'total_exportation',
                'total_exonerated',
                'total_unaffected',
                'total_free',
                'total_taxed',
                'total_igv',
                'total',
                'total_isc'
            )->with(['customer' => function ($query) {
                $query->select('id', 'name', 'number');
            }])->with(['soap_type' => function ($q) {
                $q->select('id', 'description');
            }])->with(['state_type' => function ($y) {
                $y->select('id', 'description');
            }])->with(['user' => function ($y) {
                $y->select('id', 'name');
            }])->with('items');
            if(!$include_cash_sales || !$include_credit_sales){
                if($include_cash_sales){
                    $records_sales = $records_sales->where('payment_condition_id', '01');
                }
                if($include_credit_sales){
                    $records_sales = $records_sales->where('payment_condition_id', '02');
                }
            }
            //$records_documents = $records_documents->put('class', 'Document');
            //$records_documents = $records_documents->put('class', 'SaleNote');
            $records_all = $records_documents->unionAll($records_sales);


            return new StateAccountCollection( $to_export ? $records_all->get() : $records_all->paginate(config('tenant.items_per_page')));
        }
    }

    public function excel(Request $request)
    {

        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : auth()->user()->establishment;

        $documentTypeId = null;
        if ($request->has('document_type_id')) {
            $documentTypeId = str_replace('"', '', $request->document_type_id);
        }
        $documentType = DocumentType::find($documentTypeId);
        if ($documentType != null) {
            $classType = $documentType->getCurrentRelatiomClass();
            $records = $this->getRecords($request->all(), $classType);
            $records = $records->get();
        } else {
            $records_documents = $this->getRecords($request->all(), Document::class)->select(
                'id',
                'document_type_id',
                'group_id',
                'soap_type_id',
                'date_of_issue',
                'currency_type_id',
                'series',
                'establishment_id',
                'number',
                'purchase_order',
                'state_type_id',
                'total_exportation',
                'total_exonerated',
                'total_unaffected',
                'total_free',
                'total_taxed',
                'total_igv',
                'total',
                'total_isc',
                'total_charge',
                'plate_number',
                'customer_id',
                'payment_condition_id',
                'user_id',
                'seller_id'
            )->with(['person' => function ($query) {
                $query->select('id', 'name', 'number');
            }])->with(['soap_type' => function ($q) {
                $q->select('id', 'description');
            }])->with(['state_type' => function ($y) {
                $y->select('id', 'description');
            }])->with(['user' => function ($y) {
                $y->select('id', 'name');
            }])->get();

            $records_sales = $this->getRecords($request->all(), SaleNote::class)->select(
                'id',
                'state_type_id',
                'soap_type_id',
                'date_of_issue',
                'due_date',
                'currency_type_id',
                'series',
                'establishment_id',
                'number',
                'purchase_order',
                'total_exportation',
                'total_exonerated',
                'total_unaffected',
                'total_free',
                'total_taxed',
                'total_igv',
                'total',
                'total_isc',
                'plate_number',
                'observation',
                'document_id',
                'customer_id',
                'user_id',
                'seller_id'
            )->with(['customer' => function ($query) {
                $query->select('id', 'name', 'number');
            }])->with(['soap_type' => function ($q) {
                $q->select('id', 'description');
            }])->with(['state_type' => function ($y) {
                $y->select('id', 'description');
            }])->with(['user' => function ($y) {
                $y->select('id', 'name');
            }])->get();

            //$records_documents = $records_documents->put('class', 'Document');
            //$records_documents = $records_documents->put('class', 'SaleNote');
            $records = $records_documents->concat($records_sales);
        }


        $filters = $request->all();

        //get categories
        $categories = [];
        $categories_services = [];

        if ($request->include_categories == "true") {
            $categories = $this->getCategories($records, false);
            $categories_services = $this->getCategories($records, true);
        }

        $documentExport = new StatusClientExport();
        $documentExport
            ->records($records)
            ->company($company)
            ->establishment($establishment)
            ->filters($filters)
            ->categories($categories)
            ->categories_services($categories_services);
        // return $documentExport->view();
        return $documentExport->download('Reporte_estado_de_cuenta' . Carbon::now() . '.xlsx');
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
