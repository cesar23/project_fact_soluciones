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
use Illuminate\Support\Facades\DB;
use Modules\Item\Models\Category;
use Modules\Report\Exports\ReportDocumentsPaidExport;
use Modules\Report\Exports\StatusClientExport;
use Modules\Report\Http\Resources\DocumentCollection;
use Modules\Report\Http\Resources\ReportDocumentsPaidCollection;
use Modules\Report\Http\Resources\SaleNoteCollection;
use Modules\Report\Traits\ReportTrait;

use Modules\Report\Http\Resources\StateAccountCollection;



class ReportDocumentsPaidController extends Controller
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
        return view('report::documents_paid.index');
    }

    public function records(Request $request)
    {
        $documentTypeId = null;
        if ($request->has('document_type_id')) {
            $documentTypeId = str_replace('"', '', $request->document_type_id);
        };


        $documentType = DocumentType::find($documentTypeId);



        $records_documents = $this->getRecordsDocumentsPaid($request->all(), 'documents');

        $records_sales = $this->getRecordsDocumentsPaid($request->all(), 'sale_notes');
        //$records_documents = $records_documents->put('class', 'Document');
        //$records_documents = $records_documents->put('class', 'SaleNote');
        $records_all = $records_documents->unionAll($records_sales);

        // Verificar si existen registros sin obtenerlos
        // return $records_all->get();
        $hasRecords = $records_all->limit(1)->count() > 0;

        return [
            'success' => true,
            'has_records' => $hasRecords
        ];
        // return new ReportDocumentsPaidCollection($records_all->paginate(config('tenant.items_per_page')));
    }
    private function getRecordsDocumentsPaid($request, $table)
    {
        $document_type_id = FunctionController::InArray($request, 'document_type_id');
        $establishment_id = FunctionController::InArray($request, 'establishment_id');
        $period = FunctionController::InArray($request, 'period');
        $date_start = FunctionController::InArray($request, 'date_start');
        $date_end = FunctionController::InArray($request, 'date_end');
        $month_start = FunctionController::InArray($request, 'month_start');
        $month_end = FunctionController::InArray($request, 'month_end');
        $person_id = FunctionController::InArray($request, 'person_id');
        $user_id = FunctionController::InArray($request, 'user_id');
        $user_type = FunctionController::InArray($request, 'user_type');
        $state_type_id = FunctionController::InArray($request, 'state_type_id');
        $has_payment = FunctionController::InArray($request, 'has_payment', false);
        $zone_id = FunctionController::InArray($request, 'zone_id');
        $item_id = FunctionController::InArray($request, 'item_id');
        $has_payment = ($has_payment == 'true') ? true : false;
        $table_payments = $table == 'documents' ? 'document_payments' : 'sale_note_payments';
        $table_payments_id = $table == 'documents' ? 'document_payments.document_id' : 'sale_note_payments.sale_note_id';

        /** @todo: Eliminar periodo, fechas y cambiar por

        $date_start = $request['date_start'];
        $date_end = $request['date_end'];
        \App\CoreFacturalo\Helpers\Functions\FunctionsHelper\FunctionsHelper::setDateInPeriod($request, $date_start, $date_end);
         */
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
        $connection = DB::connection('tenant');
        if ($table == 'documents') {
            $table_items = 'document_items';
            $records = $connection->table($table)
                ->select(
                    $table . '.id',
                    $table . '.currency_type_id',
                    $table . '.total_charge',
                    $table . '.total_exonerated',
                    $table . '.total_unaffected',
                    $table . '.total_free',
                    $table . '.total_taxed',
                    $table . '.total_discount',
                    $table . '.total_igv',
                    $table . '.total_isc',
                    $table . '.document_type_id',
                    $table . '.date_of_issue',
                    $table . '.series',
                    $table . '.number',
                    $table . '.customer_id',
                    $table . '.total',
                    $table . '.state_type_id',
                    $table . '.establishment_id',
                    $table . '.user_id',
                    $table . '.seller_id',
                    DB::raw("CONCAT('[', GROUP_CONCAT(DISTINCT JSON_OBJECT('item_id', document_items.item_id, 'quantity', document_items.quantity, 'total', document_items.total, 'item', document_items.item)), ']') as items"),
                    DB::raw('COALESCE(p.total_paid_sum, 0) as total_paid'),
                    DB::raw('COALESCE(SUM(DISTINCT credit_notes.total), 0) as total_credit_notes'),
                    DB::raw('p.payment_method_types_str as payment_method_types'),
                    DB::raw('COALESCE(MAX(invoices.date_of_due), ' . $table . '.date_of_issue) as date_of_due'),
                    DB::raw('MAX(dispatches.series) as dispatch_series'),
                    DB::raw('MAX(dispatches.number) as dispatch_number'),
                    DB::raw('MAX(credit_notes.series) as credit_note_series'),
                    DB::raw('MAX(credit_notes.number) as credit_note_number'),
                    DB::raw('MAX(credit_notes.total) as credit_note_total')
                )
                ->join($table_items, $table . '.id', '=', $table_items . '.document_id')
                ->leftJoin('invoices', $table . '.id', '=', 'invoices.document_id')
                ->leftJoin('dispatches', $table . '.id', '=', 'dispatches.reference_document_id')
                ->leftJoin(DB::raw('(SELECT document_id, SUM(payment) as total_paid_sum, GROUP_CONCAT(DISTINCT payment_method_type_id) as payment_method_types_str FROM document_payments GROUP BY document_id) as p'), 'p.document_id', '=', $table . '.id')
                ->leftJoin('notes', function ($join) use ($table) {
                    $join->on('notes.affected_document_id', '=', $table . '.id')
                        ->where('notes.affected_document_id', '>', 0);
                })
                ->leftJoin('documents as credit_notes', function ($join) {
                    $join->on('credit_notes.id', '=', 'notes.document_id')
                        ->where('credit_notes.document_type_id', '07')
                        ->whereIn('credit_notes.state_type_id', ['01', '03', '05', '07', '13']);
                })
                ->groupBy(
                    'documents.id',
                    'documents.currency_type_id',
                    'documents.total_charge',
                    'documents.total_exonerated',
                    'documents.total_unaffected',
                    'documents.total_free',
                    'documents.total_taxed',
                    'documents.total_discount',
                    'documents.total_igv',
                    'documents.total_isc',
                    'documents.document_type_id',
                    'documents.date_of_issue',
                    'documents.series',
                    'documents.number',
                    'documents.customer_id',
                    'documents.total',
                    'documents.state_type_id',
                    'documents.establishment_id',
                    'documents.user_id',
                    'documents.seller_id',
                    'p.total_paid_sum',
                    'p.payment_method_types_str'
                )
                ->havingRaw('(documents.total - COALESCE(p.total_paid_sum, 0) - COALESCE(SUM(DISTINCT credit_notes.total), 0)) <= 0');

        } else {
            $table_items = 'sale_note_items';
            $records = $connection->table($table)
                ->select(
                    $table . '.id',
                    $table . '.currency_type_id',
                    $table . '.total_charge',
                    $table . '.total_exonerated',
                    $table . '.total_unaffected',
                    $table . '.total_free',
                    $table . '.total_taxed',
                    $table . '.total_discount',
                    $table . '.total_igv',
                    $table . '.total_isc',
                    DB::raw("'80' as document_type_id"),
                    $table . '.date_of_issue',
                    $table . '.series',
                    $table . '.number',
                    $table . '.customer_id',
                    $table . '.total',
                    $table . '.state_type_id',
                    $table . '.establishment_id',
                    $table . '.user_id',
                    $table . '.seller_id',
                    DB::raw("CONCAT('[', GROUP_CONCAT(DISTINCT JSON_OBJECT('item_id', sale_note_items.item_id, 'quantity', sale_note_items.quantity, 'total', sale_note_items.total, 'item', sale_note_items.item)), ']') as items"),
                    DB::raw('COALESCE(p.total_paid_sum, 0) as total_paid'),
                    DB::raw('COALESCE(SUM(DISTINCT credit_notes.total), 0) as total_credit_notes'),
                    DB::raw('p.payment_method_types_str as payment_method_types'),
                    DB::raw($table . '.date_of_issue as date_of_due'),
                    DB::raw('MAX(dispatches.series) as dispatch_series'),
                    DB::raw('MAX(dispatches.number) as dispatch_number'),
                    DB::raw('MAX(credit_notes.series) as credit_note_series'),
                    DB::raw('MAX(credit_notes.number) as credit_note_number'),
                    DB::raw('MAX(credit_notes.total) as credit_note_total')
                )
                ->join($table_items, $table . '.id', '=', $table_items . '.sale_note_id')
                ->leftJoin('dispatches', $table . '.id', '=', 'dispatches.reference_sale_note_id')
                ->leftJoin(DB::raw('(SELECT sale_note_id, SUM(payment) as total_paid_sum, GROUP_CONCAT(DISTINCT payment_method_type_id) as payment_method_types_str FROM sale_note_payments GROUP BY sale_note_id) as p'), 'p.sale_note_id', '=', $table . '.id')
                ->leftJoin('notes', function ($join) use ($table) {
                    $join->on('notes.affected_sale_note_id', '=', $table . '.id')
                        ->where('notes.affected_sale_note_id', '>', 0);
                })
                ->leftJoin('documents as credit_notes', function ($join) {
                    $join->on('credit_notes.id', '=', 'notes.document_id')
                        ->where('credit_notes.document_type_id', '07')
                        ->whereIn('credit_notes.state_type_id', ['01', '03', '05', '07', '13']);
                })
                ->groupBy(
                    'sale_notes.id',
                    'sale_notes.currency_type_id',
                    'sale_notes.total_charge',
                    'sale_notes.total_exonerated',
                    'sale_notes.total_unaffected',
                    'sale_notes.total_free',
                    'sale_notes.total_taxed',
                    'sale_notes.total_discount',
                    'sale_notes.total_igv',
                    'sale_notes.total_isc',
                    'sale_notes.date_of_issue',
                    'sale_notes.series',
                    'sale_notes.number',
                    'sale_notes.customer_id',
                    'sale_notes.total',
                    'sale_notes.state_type_id',
                    'sale_notes.establishment_id',
                    'sale_notes.user_id',
                    'sale_notes.seller_id',
                    'p.total_paid_sum',
                    'p.payment_method_types_str'
                )
                ->havingRaw('(sale_notes.total - COALESCE(p.total_paid_sum, 0) - COALESCE(SUM(DISTINCT credit_notes.total), 0)) <= 0');
        }
        if ($d_start && $d_end) {
            $records->whereBetween($table . '.date_of_issue', [$d_start, $d_end]);
        }
        if ($zone_id) {
            $records->join('persons', $table . '.customer_id', '=', 'persons.id')
                   ->where('persons.zone_id', $zone_id);
        }
        if ($user_id) {
            if ($user_type == 'VENDEDOR') {
                $records->where($table.'.seller_id', $user_id);
            } else {
                $records->where($table.'.user_id', $user_id);
            }
        }
        if ($item_id) {
        
            $records->where($table_items.'.item_id', $item_id);
        }
        if ($state_type_id) {
            $records->where($table.'.state_type_id', $state_type_id);
        }
        if ($person_id) {
            $records->where($table.'.customer_id', $person_id);
        }
        if ($document_type_id && $table === 'documents') {
            $records->where($table.'.document_type_id', $document_type_id);
        }
        if ($establishment_id) {
            $records->where($table.'.establishment_id', $establishment_id);
        }




        return $records;
    }

    private function transformRecords($records)
    {
        $connection = DB::connection('tenant');
        $stateTypesData = DB::connection('tenant')->table('state_types')->get()->mapWithKeys(function ($item) {
            return [$item->id => $item];
        });
        $paymentMethodTypesData = DB::connection('tenant')->table('payment_method_types')->get();
        $customers_id = $records->pluck('customer_id')->unique()->toArray();
        $customersData = $connection->table('persons')->whereIn('id', $customers_id)->get()->mapWithKeys(function ($item) {
            return [$item->id => $item];
        });

        $sellers_id = $records->pluck('seller_id')->unique()->toArray();
        $users_id = $records->pluck('user_id')->unique()->toArray();
        $sellersData = $connection->table('users')->whereIn('id', $sellers_id)->get()->mapWithKeys(function ($item) {
            return [$item->id => $item];
        });
        $usersData = $connection->table('users')->whereIn('id', $users_id)->get()->mapWithKeys(function ($item) {
            return [$item->id => $item];
        });

        $zonesData = DB::connection('tenant')->table('zones')->get();

        $items_commission = collect([]);

        return $records->transform(function ($row) use ($customersData, $sellersData, $paymentMethodTypesData, $stateTypesData, $zonesData, $items_commission, $usersData) {
            $customer_name = $customersData[$row->customer_id]->name;
            $contact_phone = optional($customersData[$row->customer_id]->contact)->phone ?? '';
            $zone_id = $customersData[$row->customer_id]->zone_id;
            $customer_number = $customersData[$row->customer_id]->number;
            $customer_address = $customersData[$row->customer_id]->address;
            $seller_name = $sellersData[$row->seller_id]->name;
            $user_name = $usersData[$row->user_id]->name;
            $total_commission_items = 0;
            $total_sale_by_major = 0;
            if ($row->dispatch_series && $row->dispatch_number) {
                $dispatch_series = $row->dispatch_series . '-' . $row->dispatch_number;
            } else {
                $dispatch_series = '';
            }
            if ($row->credit_note_series && $row->credit_note_number) {
                $credit_note_series = $row->credit_note_series . '-' . $row->credit_note_number;
            } else {
                $credit_note_series = '';
            }
            if ($row->credit_note_total) {
                $credit_note_total = $row->credit_note_total;
            } else {
                $credit_note_total = 0;
            }
            $zone = null;
            if ($zone_id) {
                $zone = $zonesData->where('id', $zone_id)->first()->name;
            }
            $items_temp = $row->items ? json_decode($row->items, true) : [];
            if(json_last_error() !== JSON_ERROR_NONE) {
                $items_temp = [];
            }

            $items = array_map(function ($item) use ($items_commission, &$total_commission_items, &$total_sale_by_major) {
                if(is_array($item['item'])){
                    $item_data = $item['item'];
                }else{
                    $item_data = json_decode($item['item'], true);
                }
                $total_item = $item['total'];
                $quantity_item = $item['quantity'];
                $total_item_commission = 0;
                $exist_item = $items_commission->where('item_id', $item['item_id'])->first();
                if (!$exist_item) {
                    $item_commission = DB::connection('tenant')->table('items')->select('id', 'commission_amount', 'commission_type')->where('id', $item['item_id'])->first();
                    $items_commission->push((object)[
                        'item_id' => $item['item_id'],
                        'commission_amount' => $item_commission->commission_amount,
                        'commission_type' => $item_commission->commission_type
                    ]);

                    $exist_item = $items_commission->where('item_id', $item['item_id'])->first();
                }

                if ($exist_item->commission_type == 'percentage') {
                    $total_item_commission = $total_item * $exist_item->commission_amount / 100;
                    $total_commission_items += $total_item_commission;
                } else if ($exist_item->commission_type == 'amount') {
                    $total_item_commission = $exist_item->commission_amount;
                    $total_commission_items += $total_item_commission;
                }
                if(isset($item_data['presentation'])){
                    $quantity_unit = isset($item_data['presentation']['quantity_unit']) ? $item_data['presentation']['quantity_unit'] : 1;
                    $quantity_item = $quantity_item * $quantity_unit;
                    $description = isset($item_data['presentation']['description']) ? $item_data['presentation']['description'] : '';
                    if(strtolower($description) == 'mayor'){
                        $total_sale_by_major += $total_item;
                    }
                }

                return [
                    'item_id' => $item['item_id'],
                    'quantity' => $quantity_item,
                    'total' => $total_item,
                    'item' => $item_data
                ];
            }, $items_temp);

            $quantity_items = array_sum(array_map(function ($item) {
                $quantity = $item['quantity'];
                return $quantity;
            }, $items));

            return  (object)[
                'id' => $row->id,
                'total_commission_items' => $total_commission_items,
                'total_sale_by_major' => $total_sale_by_major,
                'credit_note_series' => $credit_note_series,
                'credit_note_total' => $credit_note_total,
                'contact_phone' => $contact_phone,
                'currency_type_id' => $row->currency_type_id,
                'total_charge' => $row->total_charge,
                'total_exonerated' => $row->total_exonerated,
                'total_unaffected' => $row->total_unaffected,
                'total_free' => $row->total_free,
                'total_taxed' => $row->total_taxed,
                'total_discount' => $row->total_discount,
                'total_igv' => $row->total_igv,
                'total_isc' => $row->total_isc,
                'document_type_id' => $row->document_type_id,
                'date_of_issue' => $row->date_of_issue,
                'date_of_due' => $row->date_of_due,
                'dispatch_series' => $dispatch_series,
                'customer_name' => $customer_name,
                'customer_number' => $customer_number,
                'customer_address' => $customer_address,
                'seller_name' => $seller_name,
                'user_name' => $user_name,
                'series' => $row->series,
                'number' => $row->number,
                'zone' => $zone,
                'customer_id' => $row->customer_id,
                'total' => $row->total,
                'state_type_id' => $row->state_type_id,
                'state_type_description' => $stateTypesData[$row->state_type_id]->description,
                'establishment_id' => $row->establishment_id,
                'seller_id' => $row->seller_id,
                'total_paid' => $row->total_paid,
                'payment_method_types' => $row->payment_method_types ? implode(', ', $paymentMethodTypesData->whereIn('id', explode(',', $row->payment_method_types))->pluck('description')->toArray()) : '',
                'items' => $items,
                'quantity_items' => $quantity_items
            ];
        });
    }

    public function excel(Request $request)
    {
        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : auth()->user()->establishment;
        $document_type_id = $request->document_type_id;
        $user_id = $request->user_id;
        $user_name = '';
        if($user_id){
            $user = DB::connection('tenant')->table('users')->where('id', $user_id)->first();
            $user_name = $user->name;
        }
        if(!$document_type_id){
            $documents = $this->getRecordsDocumentsPaid($request->all(), 'documents');
            $sale_notes = $this->getRecordsDocumentsPaid($request->all(), 'sale_notes');
            $records = $this->transformRecords($documents->unionAll($sale_notes)->get());
        }else if ($document_type_id == '80'){
            $sale_notes = $this->getRecordsDocumentsPaid($request->all(), 'sale_notes');
            $records = $this->transformRecords($sale_notes->get());
        }else{
            $documents = $this->getRecordsDocumentsPaid($request->all(), 'documents');
            $records = $this->transformRecords($documents->get());
        }


        $filters = $request->all();



        $documentExport = new ReportDocumentsPaidExport();
        $documentExport
            ->records($records)
            ->company($company)
            ->filters($filters)
            ->seller_name($user_name)
            ->establishment($establishment);
        // return $documentExport->view();
        return $documentExport->download('Reporte_documentos_pagados' . Carbon::now() . '.xlsx');
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
