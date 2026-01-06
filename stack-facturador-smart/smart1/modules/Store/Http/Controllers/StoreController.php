<?php

namespace Modules\Store\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant\Person;
use App\Models\Tenant\Series;
use App\Models\Tenant\Quotation;
use Modules\Item\Models\ItemLot;
use App\Models\Tenant\DocumentItem;
use App\Models\Tenant\NameDocument;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\NameQuotations;
use App\CoreFacturalo\Requests\Inputs\Functions;
use App\Models\Tenant\Cash;
use App\Models\Tenant\PlateNumber;
use App\Models\Tenant\PlateNumberDocument;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\SaleNotePayment;
use App\Traits\CacheTrait;
use Illuminate\Support\Facades\DB;
use Modules\BusinessTurn\Models\BusinessTurn;
use Modules\Document\Http\Resources\ItemLotCollection;
use Modules\Finance\Models\GlobalPayment;
use Modules\Suscription\Models\Tenant\SuscriptionNames;
use Modules\Inventory\Models\Warehouse as ModuleWarehouse;

class StoreController extends Controller
{
    use CacheTrait;
    public function tableToDocument(Request $request, $table, $table_id)
    {
        $type_quotation = $request->input('type_quotation');
        $configuration = Configuration::query()->first();
        $is_contingency = 0;
        $establishment = Establishment::whereActive()->get();
        $suscriptionames = SuscriptionNames::create_new();
        $establishment_auth = Establishment::where('id', auth()->user()->establishment_id)->get();
        $data = NameQuotations::first();
        $quotations_optional =  $data != null ? $data->quotations_optional : null;
        $api_token = \App\Models\Tenant\Configuration::getApiServiceToken();
        $is_integrate_system = BusinessTurn::isIntegrateSystem();
        return view('tenant.documents.form', [
            'is_integrate_system' => $is_integrate_system,
            'api_token' => $api_token,
            'configuration' => $configuration,
            'is_contingency' => $is_contingency,
            'table_id' => $table_id,
            'suscriptionames' => $suscriptionames,
            'table' => $table,
            'establishment' => $establishment,
            'establishment_auth' => $establishment_auth,
            'quotations_optional' => $quotations_optional,
            'quotations_optional_value' => $quotations_optional,
            'type_quotation' => $type_quotation,
        ]);
    }

    public function getRecord(Request $request, $table, $table_id)
    {
        $configuration = Configuration::first();
        $type_quotation = $request->input('type_quotation');
        $model = Quotation::query();
        $relation = "quotation_id";
        switch ($table) {
            case 'sale-notes':
                $relation = "sale_note_id";
                $model = SaleNote::query();
                break;
        }
        $record = $model->with(['person', 'payments', 'fee'])->find($table_id);
        $person = $record->person;

        $rec = $record->toArray();
        if ($relation === "sale_note_id") {
            $rec['quotation_id'] = null;
        }
        $document_type_id = $person->identity_document_type_id === '6' ? '01' : '03';
        $payments = Functions::valueKeyInArray($rec, 'payments', []);
        $fee = Functions::valueKeyInArray($rec, 'fee', []);
        //iterar payments para agregar otra propiedad
        foreach ($payments as &$payment) {
            $sale_note_payment_class_to_string = SaleNotePayment::class;
            if ($table === 'sale-notes') {
                $id = $payment['id'];
                $global_payment = GlobalPayment::where('payment_type', $sale_note_payment_class_to_string)->where('payment_id', $id)->first();
                if ($global_payment) {
                    if ($global_payment->destination_type == Cash::class) {
                        $payment['payment_destination_id'] = 'cash';
                    } else {
                        $payment['payment_destination_id'] = $global_payment->destination_id;
                    }
                }
            }
        }

        $series = Series::query()
            ->select('number');
        if (!$configuration->seller_establishments_all) {
            $series = $series->where('establishment_id', $rec['establishment_id']);
        }
        $series = $series->where('document_type_id', $document_type_id)
            ->first();

        $rec_items = $rec['items'];
        if($type_quotation === 'services'){
            $rec_items = array_values(array_filter($rec_items, function($item) {
                return $item['item']->unit_type_id == 'ZZ';
            }));
        }else if($type_quotation === 'not-services'){
            $rec_items = array_values(array_filter($rec_items, function($item) {
                return $item['item']->unit_type_id != 'ZZ';
            }));
        }

        foreach ($rec_items as &$item) {
            $item['total_plastic_bag_taxes'] = 0;
            $item['attributes'] = ($item['attributes']) ? (array)$item['attributes'] : [];
            $item['charges'] = ($item['charges']) ? (array)$item['charges'] : [];
            $item['discounts'] = ($item['discounts']) ? (array)$item['discounts'] : [];
        }
        $rec['items'] = $rec_items;
        $payment_condition_id = Functions::valueKeyInArray($rec, 'payment_condition_id', '01');
        $rec['document_type_id'] = $document_type_id;
        $rec['operation_type_id'] = '0101';
        $rec['number'] = '#';
        $rec['date_of_issue'] = now()->format('Y-m-d');
        $rec['fee'] = $fee;
        $rec['charges'] = [];
        $rec['discounts'] = [];
        $rec['payments'] = $payments;
        $rec['guides'] = [];
        $rec['payment_condition_id'] = $payment_condition_id;
        $rec['series'] = $series->number;
        $rec['ubl_version'] = '2.1';
        $rec['unique_filename'] = '';
        $rec['user_rel_suscription_plan_id'] = 0;
        $rec['was_deducted_prepayment'] = 0;
        $rec["$relation"] = $table_id;
        $rec['additional_information'] = Functions::valueKeyInArray($rec, 'additional_information', '');
        $this->setPaymentsFromQuotation($rec, $record);
        if ($configuration->plate_number_config) {
            $column = null;
            switch ($table) {
                case 'sale-notes':
                    $column = 'sale_note_id';
                    break;
                case 'quotations':
                    $column = 'quotation_id';
                    break;
                case 'documents':
                    $column = 'document_id';
                    break;
            }
            if ($column) {
                $plate_number_document = PlateNumberDocument::where($column, $table_id)->first();
                if ($plate_number_document) {
                    $rec['plate_number_document'] = $plate_number_document->plateNumber;
                }
            }
            $establishment_id = auth()->user()->establishment_id;
            $rec['establishment_id'] = $establishment_id;
        }
        return [
            'success' => true,
            'data' => $rec
        ];
    }


    /**
     *
     * Asignar valores relacionados a pago credito
     *
     * @param array $rec
     * @param Quotation $document
     * @return void
     */
    private function setPaymentsFromQuotation(&$rec, $document)
    {
        $payment_method_type = $document->payment_method_type;

        if ($payment_method_type) {
            if ($payment_method_type->isCredit()) {
                //credito o credito con cuotas
                $rec['payment_condition_id'] = ($payment_method_type->number_days) ? '02' : '03';
                $rec['data_payments_fee'] = $document->payments;
                $rec['document_payment_method_type'] = $payment_method_type;
            }
        }
    }


    public function getItems()
    {
    }

    public function getItemSeries(Request $request)
    {
        $warehouse = ModuleWarehouse::query()
            ->select('id')
            ->where('establishment_id', auth()->user()->establishment_id)
            ->first();

        $input = $request->input('input');
        $item_id = $request->input('item_id');
        $document_item_id = $request->input('document_item_id');
        $sale_note_item_id = $request->input('sale_note_item_id');

        return ItemLot::query()
            ->select('id', 'series', 'date', 'has_sale')
            ->where('series', 'like', "%$input%")
            ->where('item_id', $item_id)
            ->where('has_sale', false)
            ->where('warehouse_id', $warehouse->id)
            ->latest()
            ->get()
            ->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'series' => $row->series,
                    'date' => $row->date,
                    //                    'item_id'      => $row->item_id,
                    //                    'warehouse_id' => $row->warehouse_id,
                    'has_sale' => $row->has_sale,
                    //                    'lot_code'     => ($row->item_loteable_type) ? $lot_code : null,
                ];
            });

        //        $sale_note_item_id = $request->has('sale_note_item_id') ? $request->sale_note_item_id : null;
        //
        //        if ($request->document_item_id)
        //        {
        //            //proccess credit note
        //            $document_item = DocumentItem::query()
        //                ->findOrFail($request->document_item_id);
        //            /** @var array $lots */
        //            $lots = $document_item->item->lots;
        //            $records
        //                ->whereIn('id', collect($lots)->pluck('id')->toArray())
        //                ->where('has_sale', true)
        //                ->latest();
        //
        //        }
        //        else if($sale_note_item_id)
        //        {
        //            $records = $this->getRecordsForSaleNoteItem($records, $sale_note_item_id, $request);
        //        }
        //        else
        //        {
        //
        //            $records
        //                ->where('item_id', $request->item_id)
        //                ->where('has_sale', false)
        //                ->where('warehouse_id', $warehouse->id)
        //                ->latest();
        //        }

        //        return new ItemLotCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function getIgv(Request $request)
    {

        $user = auth()->user() ?? auth('api')->user();
        $establishment_id = $request->input('establishment_id');
        $date = $request->input('date');
        if (!$date) {
            $date = now();
        }
        if (!$establishment_id) {
            $establishment_id = $user->establishment_id;
        }
        $date_start = config('tenant.igv_31556_start');
        $date_end = config('tenant.igv_31556_end');
        $date_percentage = config('tenant.igv_31556_percentage');
        $establishment = Establishment::query()
            ->select('id', 'has_igv_31556')
            ->find($establishment_id);
        if ($establishment->has_igv_31556) {
            if ($date >= $date_start && $date <= $date_end) {
                return $date_percentage;
            }
        }
        $igv = 0.18;
        return $igv;
    }

    public function getCustomers(Request $request)
    {
        $moreThan100 = DB::connection('tenant')->table('persons')->count() > 50;
        $identity_document_type_id = $request->input('identity_document_type_id');
        $input = $request->input('input');
        $query = Person::query()
            ->where('number', 'like', "%{$input}%")
            ->orWhere('name', 'like', "%{$input}%")
            ->whereType('customers');
        if ($identity_document_type_id) {
            $query->whereIn('identity_document_type_id', $identity_document_type_id);
        }
        if ($moreThan100) {
            $query->limit(50);
        }

        $customers = $query->whereIsEnabled()
            ->orderBy('name')
            ->get()->transform(function ($row) {
                return $row->getCollectionData();
            });

        return compact('customers');
    }
}
