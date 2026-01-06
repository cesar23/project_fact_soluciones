<?php

namespace Modules\Order\Http\Controllers;

use App\CoreFacturalo\HelperFacturalo;
use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\CoreFacturalo\Requests\Inputs\Common\EstablishmentInput;
use App\CoreFacturalo\Requests\Inputs\Common\PersonInput;
use App\CoreFacturalo\Requests\Inputs\DocumentInput;
use App\CoreFacturalo\Requests\Inputs\Functions;
use App\CoreFacturalo\Requests\Web\Validation\DocumentValidation;
use App\CoreFacturalo\Template;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SearchItemController;
use App\Http\Controllers\Tenant\DocumentController;
use App\Http\Controllers\Tenant\EmailController;
use App\Http\Controllers\Tenant\SaleNoteController;
use App\Http\Requests\Tenant\DocumentRequest;
use App\Http\Requests\Tenant\SaleNoteRequest;
use App\Models\System\Client;
use App\Models\Tenant\Catalogs\AffectationIgvType;
use App\Models\Tenant\Catalogs\AttributeType;
use App\Models\Tenant\Catalogs\ChargeDiscountType;
use App\Models\Tenant\Catalogs\CurrencyType;
use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\Catalogs\OperationType;
use App\Models\Tenant\Catalogs\PriceType;
use App\Models\Tenant\Catalogs\SystemIscType;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Dispatch;
use App\Models\Tenant\Document;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\FoodDealerAuth;
use App\Models\Tenant\GuideFile;
use App\Models\Tenant\Item;
use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\Person;
use App\Models\Tenant\PersonFoodDealer;
use App\Models\Tenant\Quotation;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\Series;
use App\Models\Tenant\User;
use App\Models\Tenant\Warehouse;
use App\Traits\OfflineTrait;
use Exception;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Models\Hostname;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\BusinessTurn\Models\BusinessTurn;
use Modules\Dispatch\Models\Dispatcher;
use Modules\Document\Models\SeriesConfiguration;
use Modules\Finance\Traits\FinanceTrait;
use Modules\Order\Http\Requests\OrderNoteRequest;
use Modules\Order\Http\Resources\OrderNoteCollection;
use Modules\Order\Http\Resources\OrderNoteDocumentCollection;
use Modules\Order\Http\Resources\OrderNoteResource;
use Modules\Order\Mail\OrderNoteEmail;
use Modules\Order\Models\MiTiendaPe;
use Modules\Order\Models\OrderNote;
use Modules\Order\Models\OrderNoteFee;
use Modules\Order\Models\OrderNoteItem;
use Modules\Restaurant\Models\OrdenItem;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use Mpdf\MpdfException;
use Throwable;


/**
 * Class OrderNoteController
 *
 * @package Modules\Order\Http\Controllers
 * @mixin Controller
 */
class OrderNoteController extends Controller
{

    use FinanceTrait;
    use StorageDocument;
    use OfflineTrait;

    protected $order_note;
    protected $company;

    public function index()
    {
        $company = Company::orderBy('id')->select('soap_type_id')->first();
        $soap_company = $company->soap_type_id;
        $configuration = Configuration::first();

        return view('order::order_notes.index', compact('soap_company', 'configuration'));
    }


    public function getToDelete(Request $request)
    {
        $date_start = $request->date_start;
        $date_end = $request->date_end;
        $records = OrderNote::whereBetween('date_of_issue', [$date_start, $date_end]);
        return new OrderNoteCollection($records->paginate(50));
    }
    public function deletes(Request $request)
    {
        try {
            DB::connection('tenant')->beginTransaction();
            $ids = $request->ids;
            foreach ($ids as $id) {
                $order_note = OrderNote::findOrFail($id);
                Document::where('order_note_id', $order_note->id)->update(['order_note_id' => null]);
                SaleNote::where('order_note_id', $order_note->id)->update(['order_note_id' => null]);
                Dispatch::where('reference_order_note_id', $order_note->id)->update(['reference_order_note_id' => null]);
                GuideFile::where('order_note_id', $order_note->id)->update(['order_note_id' => null]);
                PersonFoodDealer::where('order_note_id', $order_note->id)->update(['order_note_id' => null]);
                MiTiendaPe::where('order_note_id', $order_note->id)->update(['order_note_id' => null]);
                OrderNoteFee::where('order_note_id', $order_note->id)->delete();
                OrderNoteItem::where('order_note_id', $order_note->id)->delete();

                $order_note->delete();
            }
            DB::connection('tenant')->commit();
            return [
                'success' => true,
                'message' => 'Ordenes de venta eliminadas con éxito'
            ];
        } catch (Exception $e) {
            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    public function create()
    {

        return view('order::order_notes.form');
    }

    public function edit($id)
    {
        $resourceId = $id;
        $configuration = Configuration::first();
        return view('order::order_notes.form_edit', compact('resourceId', 'configuration'));
    }

    public function columns()
    {


        if (BusinessTurn::isFoodDealer()) {
            $columns = [
                'customer_name' => 'Cliente',
                'auth_by' => 'Autorizado por',
                'date_of_issue' => 'Fecha de emisión',
                'delivery_date' => 'Fecha de entrega',
                'user_name' => 'Vendedor',

            ];
        } else {
            $columns = [
                'date_of_issue' => 'Fecha de emisión',
                'delivery_date' => 'Fecha de entrega',
                'user_name' => 'Vendedor',
                'customer_name' => 'Cliente',

            ];
        }

        return $columns;
    }

    public function records(Request $request)
    {
        $records = $this->getRecords($request);

        return new OrderNoteCollection($records->paginate(config('tenant.items_per_page')));
    }

    private function getRecords($request)
    {

        if ($request->column == 'user_name' && $request->value) {

            $records = OrderNote::whereHas('user', function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->value}%");
            })
                ->whereTypeUser()
                ->latest();
        } else if ($request->column == 'auth_by' && $request->value) {
            $records = OrderNote::whereHas('person_food_dealer', function ($query) use ($request) {
                $query->whereHas('food_dealer_auth', function ($query) use ($request) {
                    $query->where("user_name", 'like', "%{$request->value}%");
                });
            })
                ->whereTypeUser()
                ->latest();
        } else if ($request->column == 'customer_name' && $request->value) {

            $records = OrderNote::whereHas('person', function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->value}%");
            })
                ->whereTypeUser()
                ->latest();
        } else {

            if ($request->column && $request->value) {
                $records = OrderNote::where($request->column, 'like', "%{$request->value}%")
                    ->whereTypeUser()
                    ->latest();
            } else {
                $records = OrderNote::whereTypeUser()->latest();
            }
        }

        return $records;
    }


    public function documents(Request $request)
    {
        $configuration = Configuration::first();
        $seller_id = $request->seller_id;
        $order_note_not_blocked = $configuration->order_note_not_blocked;
        $records = OrderNote::query();
        if (!$order_note_not_blocked) {
            // $records = $records->where('state_type_id', '01');
            $records =    $records->doesntHave('sale_notes')
                ->doesntHave('documents');
        }
        $records = $records->where('state_type_id', '01')
            ->whereTypeUser()
            ->latest();
        if ($seller_id) {
            $records = $records->where('user_id', $seller_id);
        }


        return new OrderNoteDocumentCollection($records->paginate(config('tenant.items_per_page')));
    }


    public function document_tables()
    {
        $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
        $series = Series::where('establishment_id', $establishment->id)->get();
        // $document_types_invoice = DocumentType::whereIn('id', ['01', '03', '80'])->get();
        $sellers = User::GetSellers(false)->get();
        $dispatchers = Dispatcher::where('is_active', true)->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'description' => $row->number . ' - ' . $row->name,
            ];
        });

        return compact('series', 'establishment', 'sellers', 'dispatchers');
    }


    public function generateDocuments(Request $request)
    {
        $is_food_dealer = BusinessTurn::isFoodDealer();

        DB::connection('tenant')->transaction(function () use ($request, $is_food_dealer) {
            $documents = $request->documents;
            $documents = collect($documents)->unique('order_note_id')->values()->all();
            foreach ($documents as $row) {

                if ($row['document_type_id'] === "80") {
                    if ($is_food_dealer) {
                        $exists = SaleNote::where('order_note_id', $row['order_note_id'])->exists();
                        if ($exists) {
                            continue;
                        }
                    }
                    app(SaleNoteController::class)->store(new SaleNoteRequest($row));
                } else {
                    if ($is_food_dealer) {
                        $exists = Document::where('order_note_id', $row['order_note_id'])->exists();
                        if ($exists) {
                            continue;
                        }
                    }
                    if (isset($row['series']) && is_array($row['series'])) {
                        $series = Series::find($row['series_id']);
                        $row['series'] = $series->number;
                    }
                    $data_val = DocumentValidation::validation($row);

                    app(DocumentController::class)->store(new DocumentRequest(DocumentInput::set($data_val)));
                }
            }
        });

        return [
            'success' => true,
            'message' => 'Comprobantes generados'
        ];
    }


    public function searchCustomers(Request $request)
    {

        $customers = Person::where('number', 'like', "%{$request->input}%")
            ->orWhere('name', 'like', "%{$request->input}%")
            ->whereType('customers')->orderBy('name')
            ->get()->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'description' => $row->number . ' - ' . $row->name,
                    'name' => $row->name,
                    'number' => $row->number,
                    'identity_document_type_id' => $row->identity_document_type_id,
                    'identity_document_type_code' => $row->identity_document_type->code,
                    'address' => $row->address,
                    'dispatch_addresses' => $row->dispatch_addresses,
                ];
            });

        return compact('customers');
    }

    public function searchItemById($id)
    {
        $items = SearchItemController::getItemsToOrderNote(null, $id);
        return compact('items');
    }

    public function searchItems(Request $request)
    {
        $items = SearchItemController::getItemsToOrderNote($request);
        return compact('items');
    }

    public function tables()
    {

        $customers = $this->table('customers');
        $has_varios = collect($customers)->first(function ($customer) {
            return str_contains(strtolower($customer['number']), '99999999');
        });

        if (!$has_varios) {
            $varios = Person::where('number', 'like', '99999999')
                ->whereType('customers')
                ->first();

            if ($varios) {
                $customers->push([
                    'id' => $varios->id,
                    'description' => $varios->number . ' - ' . $varios->name,
                    'name' => $varios->name,
                    'number' => $varios->number,
                    'identity_document_type_id' => $varios->identity_document_type_id,
                    'identity_document_type_code' => $varios->identity_document_type->code,
                    'address' => $varios->address,
                ]);
            }
        }
        if (auth()->user()) {
            $establishments = Establishment::where('id', auth()->user()->establishment_id)->get();
        } else {
            $establishments = Establishment::whereActive()->get();
        }
        $currency_types = CurrencyType::whereActive()->get();
        $serie = Series::where('document_type_id', "PD")->where("establishment_id", $establishments[0]->id)->first();
        if ($serie) {
            $serie = $serie->number;
        }
        // $document_types_invoice = DocumentType::whereIn('id', ['01', '03'])->where('active',true)->get();
        $discount_types = ChargeDiscountType::whereType('discount')->whereLevel('item')->get();
        $charge_types = ChargeDiscountType::whereType('charge')->whereLevel('item')->get();
        $company = Company::active();
        $companies = Company::all();
        $document_type_03_filter = config('tenant.document_type_03_filter');
        $payment_method_types = PaymentMethodType::orderBy('id', 'desc')->get();
        $sellers = User::GetSellers(false)->get();
        return compact(
            'customers',
            'sellers',
            'establishments',
            'currency_types',
            'discount_types',
            'charge_types',
            'company',
            'serie',
            'document_type_03_filter',
            'payment_method_types',
            'companies'
        );
    }

    public function table($table)
    {
        switch ($table) {
            case 'customers':

                $customers = Person::whereType('customers')->orderBy('name')->take(20)->get()->transform(function ($row) {
                    return [
                        'id' => $row->id,
                        'description' => $row->number . ' - ' . $row->name,
                        'name' => $row->name,
                        'number' => $row->number,
                        'identity_document_type_id' => $row->identity_document_type_id,
                        'identity_document_type_code' => $row->identity_document_type->code,
                        'address' => $row->address,
                    ];
                });
                return $customers;

                break;

            case 'items':

                $warehouse = Warehouse::where('establishment_id', auth()->user()->establishment_id)->first();

                $items = Item::orderBy('description')->whereIsActive()->whereNotIsSet()
                    // ->with(['warehouses' => function($query) use($warehouse){
                    //     return $query->where('warehouse_id', $warehouse->id);
                    // }])
                    ->get()->transform(function ($row) use ($warehouse) {
                        /** @var Item $row */
                        return $row->getDataToItemModal($warehouse, true, true);
                        $full_description = $this->getFullDescription($row);
                        // $full_description = ($row->internal_id)?$row->internal_id.' - '.$row->description:$row->description;
                        $lots = $row->item_lots->where('has_sale', false);
                        return [
                            'id' => $row->id,
                            'full_description' => $full_description,
                            'description' => $row->description,
                            'currency_type_id' => $row->currency_type_id,
                            'currency_type_symbol' => $row->currency_type->symbol,
                            'sale_unit_price' => $row->sale_unit_price,
                            'purchase_unit_price' => $row->purchase_unit_price,
                            'unit_type_id' => $row->unit_type_id,
                            'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                            'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                            'is_set' => (bool)$row->is_set,
                            'has_igv' => (bool)$row->has_igv,
                            'calculate_quantity' => (bool)$row->calculate_quantity,
                            'item_unit_types' => collect($row->item_unit_types)->transform(function ($row) {
                                return [
                                    'id' => $row->id,
                                    'description' => "{$row->description}",
                                    'item_id' => $row->item_id,
                                    'unit_type_id' => $row->unit_type_id,
                                    'quantity_unit' => $row->quantity_unit,
                                    'price1' => $row->price1,
                                    'price2' => $row->price2,
                                    'price3' => $row->price3,
                                    'price_default' => $row->price_default,
                                    'warehouse_id' => $row->warehouse_id,
                                ];
                            }),
                            'warehouses' => collect($row->warehouses)->transform(function ($row) use ($warehouse) {
                                return [
                                    'warehouse_id' => $row->warehouse->id,
                                    'warehouse_description' => $row->warehouse->description,
                                    'stock' => $row->stock,
                                    'checked' => ($row->warehouse_id == $warehouse->id) ? true : false,
                                ];
                            }),
                            'lots' => $lots->transform(function ($row) {
                                return [
                                    'id' => $row->id,
                                    'series' => $row->series,
                                    'date' => $row->date,
                                    'item_id' => $row->item_id,
                                    'warehouse_id' => $row->warehouse_id,
                                    'has_sale' => (bool)$row->has_sale,
                                    'lot_code' => ($row->item_loteable_type) ?
                                        (isset($row->item_loteable->lot_code) ?
                                            $row->item_loteable->lot_code :
                                            null) :
                                        null
                                ];
                            })->values(),
                            'series_enabled' => (bool)$row->series_enabled,
                        ];
                    });
                return $items;

                break;
            default:
                return [];

                break;
        }
    }

    public function getItemById($item_id)
    {
        $warehouse = Warehouse::where('establishment_id', auth()->user()->establishment_id)->first();

        $items = Item::orderBy('description')
            ->where('id', $item_id)
            ->whereIsActive()->whereNotIsSet()
            // ->with(['warehouses' => function($query) use($warehouse){
            //     return $query->where('warehouse_id', $warehouse->id);
            // }])
            ->get()->transform(function ($row) use ($warehouse) {
                /** @var Item $row */
                return $row->getDataToItemModal($warehouse, true, true);
                $full_description = $this->getFullDescription($row);
                // $full_description = ($row->internal_id)?$row->internal_id.' - '.$row->description:$row->description;
                $lots = $row->item_lots->where('has_sale', false);
                return [
                    'id' => $row->id,
                    'full_description' => $full_description,
                    'description' => $row->description,
                    'currency_type_id' => $row->currency_type_id,
                    'currency_type_symbol' => $row->currency_type->symbol,
                    'sale_unit_price' => $row->sale_unit_price,
                    'purchase_unit_price' => $row->purchase_unit_price,
                    'unit_type_id' => $row->unit_type_id,
                    'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                    'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                    'is_set' => (bool)$row->is_set,
                    'has_igv' => (bool)$row->has_igv,
                    'calculate_quantity' => (bool)$row->calculate_quantity,
                    'item_unit_types' => collect($row->item_unit_types)->transform(function ($row) {
                        return [
                            'id' => $row->id,
                            'description' => "{$row->description}",
                            'item_id' => $row->item_id,
                            'unit_type_id' => $row->unit_type_id,
                            'quantity_unit' => $row->quantity_unit,
                            'price1' => $row->price1,
                            'price2' => $row->price2,
                            'price3' => $row->price3,
                            'price_default' => $row->price_default,
                            'warehouse_id' => $row->warehouse_id,
                        ];
                    }),
                    'warehouses' => collect($row->warehouses)->transform(function ($row) use ($warehouse) {
                        return [
                            'warehouse_id' => $row->warehouse->id,
                            'warehouse_description' => $row->warehouse->description,
                            'stock' => $row->stock,
                            'checked' => ($row->warehouse_id == $warehouse->id) ? true : false,
                        ];
                    }),
                    'lots' => $lots->transform(function ($row) {
                        return [
                            'id' => $row->id,
                            'series' => $row->series,
                            'date' => $row->date,
                            'item_id' => $row->item_id,
                            'warehouse_id' => $row->warehouse_id,
                            'has_sale' => (bool)$row->has_sale,
                            'lot_code' => ($row->item_loteable_type) ?
                                (isset($row->item_loteable->lot_code) ?
                                    $row->item_loteable->lot_code :
                                    null) :
                                null
                        ];
                    })->values(),
                    'series_enabled' => (bool)$row->series_enabled,
                ];
            });
        return $items;
    }
    public function getFullDescription($row)
    {

        $desc = ($row->internal_id) ? $row->internal_id . ' - ' . $row->description : $row->description;
        $category = ($row->category) ? " - {$row->category->name}" : "";
        $brand = ($row->brand) ? " - {$row->brand->name}" : "";

        $desc = "{$desc} {$category} {$brand}";

        return $desc;
    }

    public function option_tables()
    {
        $configuration = Configuration::select(['multi_companies'])->first();
        $document_types_invoice = DocumentType::whereIn('id', ['01', '03', '80'])->get();
        $payment_method_types = PaymentMethodType::all();
        $payment_destinations = $this->getPaymentDestinations();
        $establishment_info = [];
        if ($configuration->multi_companies) {
            $website_id = auth()->user()->company_active_id;
            User::setCompanyActiveId($website_id);
            $company_alter = Company::where('website_id', $website_id)->first();
            $document_number = $company_alter->document_number;
            $key = 'cash_' . auth()->user()->id;
            $company_active_id = Cache::put($key, $website_id, 60);
            $company_id = $website_id;
            $hostname = Hostname::where('website_id', $website_id)->first();
            $client = Client::where('hostname_id', $hostname->id)->first();
            $tenancy = app(Environment::class);
            $tenancy->tenant($client->hostname->website);
            $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
            $establishment_info = EstablishmentInput::set($establishment->id);
            $series = Series::where('establishment_id', $establishment->id)->get();
        } else {

            $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
            $series = Series::where('establishment_id', $establishment->id)->get();
        }

        return compact('series', 'document_types_invoice', 'payment_method_types', 'payment_destinations');
    }

    public function item_tables()
    {
        // $items = $this->table('items');
        $currentWarehouseId = Warehouse::where('establishment_id', auth()->user()->establishment_id)->first()->id;
        $items = SearchItemController::getItemsToOrderNote();
        $categories = [];
        $affectation_igv_types = AffectationIgvType::whereActive()->get();
        $system_isc_types = SystemIscType::whereActive()->get();
        $price_types = PriceType::whereActive()->get();
        $operation_types = OperationType::whereActive()->get();
        $discount_types = ChargeDiscountType::whereType('discount')->whereLevel('item')->get();
        $charge_types = ChargeDiscountType::whereType('charge')->whereLevel('item')->get();
        $attribute_types = AttributeType::whereActive()->orderByDescription()->get();
        $is_client = $this->getIsClient();

        return compact(
            'currentWarehouseId',
            'items',
            'categories',
            'affectation_igv_types',
            'system_isc_types',
            'price_types',
            'discount_types',
            'charge_types',
            'attribute_types',
            'operation_types',
            'is_client'
        );
    }

    public function aprove($id)
    {
        $order_note = OrderNote::find($id);
        $order_note->state = 1;
        $order_note->save();
        return [
            'success' => true,
            'message' => 'Orden de venta aprobada'
        ];
    }
    public function record($id)
    {
        $record = new OrderNoteResource(OrderNote::findOrFail($id));

        return $record;
    }

    public function record2($id)
    {
        $record = new OrderNoteResource(OrderNote::findOrFail($id));

        return $record;
    }
    function getSerie($establishment_id)
    {
        $series = Series::where('establishment_id', $establishment_id)
            ->where('document_type_id', 'PD')
            ->get();
        $serie = $series->first();
        if ($serie) {
            $serie = $serie->number;
        }
        return $serie;
    }
    public function checkItemFoodDealer(Request $request)
    {
        $customer_id = $request->customer_id;
        $date_of_issue = $request->date_of_issue;
        $errors = [];
        $previous_records = [];

        $item_food_dealer = PersonFoodDealer::where('person_id', $customer_id)
            ->where('date_of_issue', $date_of_issue)
            ->get();

        foreach ($item_food_dealer as $record) {
            $item = $record->item;
            $order_note = $record->order_note;
            $previous_records[] = [
                'item_description' => $item->description,
                'order_note_number' => $order_note->number_full,
                'date_of_issue' => $date_of_issue,
                'time_of_issue' => $order_note->time_of_issue,
                'user_name' => $record->food_dealer_auth ? $record->food_dealer_auth->user_name : null,
            ];
        }
        $errors[] = "Esta persona ya recibió ordenes en la fecha $date_of_issue";

        if (count($previous_records) > 0) {
            return [
                'success' => false,
                'errors' => $errors,
                'previous_records' => $previous_records,
            ];
        } else {
            return [
                'success' => true,
            ];
        }
    }
    /**
     * @param OrderNoteRequest $request
     *
     * @return array
     * @throws Throwable
     */
    public function store(OrderNoteRequest $request)
    {

        try {
            $data = $this->mergeArray($request);
            $is_food_dealer = BusinessTurn::isFoodDealer();
            $message_food_dealer = "";
            // $user_name = $re
            /* @todo Deberia pasarse a facturalo para tenerlo como standar */
            DB::connection('tenant')->transaction(function () use ($data, $is_food_dealer, &$message_food_dealer) {
                $series = Functions::valueKeyInArray($data, "prefix", null);
                if($series == 'PD') {
                    $series = 'PD1';
                }
                $series_id = Functions::valueKeyInArray($data, "series_id", null);
                $payment_method_type_id = Functions::valueKeyInArray($data, "payment_method_type_id", null);
                if ($payment_method_type_id) {
                    $payment_method_type = PaymentMethodType::find($payment_method_type_id);
                    if ($payment_method_type) {
                        $data['payment_method_type_id'] = $payment_method_type->id;
                    } else {
                        $payment_method_type = PaymentMethodType::where('is_cash', true)->first();
                        if ($payment_method_type) {
                            $data['payment_method_type_id'] = $payment_method_type->id;
                        } else {
                            $data['payment_method_type_id'] = null;
                        }
                    }
                }
                $alter_establishment = Functions::valueKeyInArray($data, 'establishment');
                $user_name = Functions::valueKeyInArray($data, 'user_name');
                $company_id = Functions::valueKeyInArray($data, 'company_id');
                $serie = $this->getSerie($data['establishment_id']);
                $configuration = Configuration::first();

                if ($series_id) {
                    $series_db = Series::find($series_id);
                    if ($series_db) {
                        $serie = $series_db->number;
                    }
                }

                if ($serie) {
                    $data["prefix"] = $serie;
                }
                if (OrderNote::count() == 0 && $series) {
                    $series_configuration = SeriesConfiguration::where([['document_type_id', "PD"], ['series', $series]])->first();
                    $number = $series_configuration->number ?? 1;
                    $data["id"] = $number;
                    $data["number"] = $number;
                }
                $number = Functions::valueKeyInArray($data, "number", null);
                $multi_companies = $configuration->multi_companies && $company_id;
                if ((!$number || $number != '#') || !$multi_companies) {
                    $last_id = OrderNote::orderBy('id', 'desc')
                        ->where('prefix', $data['prefix'])
                        ->first();
                    if ($last_id) {
                        $data["number"] = ($last_id->number && $last_id->number != '#' ? $last_id->number : $last_id->id) + 1;
                    } else {
                        $data["number"] = 1;
                    }
                }

                if ($company_id) {
                    $prefix = Functions::valueKeyInArray($data, "prefix", null);
                    if ($prefix) {
                        $data["prefix"] = $prefix;
                    }
                    $company_found = Company::where('website_id', $company_id)->first();

                    $data['website_id'] = $company_id;
                    $data['company'] = $company_found->name;
                    // $alter_establishment = Functions::valueKeyInArray($request, 'establishment');
                    if ($alter_establishment) {
                        $data['establishment'] = $alter_establishment;
                    }

                    $alter_number = Functions::valueKeyInArray($data, 'number');
                    if ($alter_number) {
                        $data['number'] = $alter_number;
                    }
                    if (!$alter_number) {
                        $document_found = OrderNote::where('prefix', $data['prefix'])
                            ->where('website_id', $company_id)
                            ->orderBy(DB::raw('CAST(number AS UNSIGNED)'), 'desc')
                            ->first();
                        if ($document_found) {

                            $document_number = $document_found->number;
                            $document_number = $document_number + 1;
                            if ($document_number) {
                                $data['number'] = $document_number;
                            }
                        } else {
                            $first_company = Company::active();
                            $is_same_website = $first_company->website_id == $company_id;
                            if ($is_same_website) {
                                $document_found = OrderNote::where('prefix', $data['prefix'])
                                    ->whereNull('website_id')
                                    ->orderBy('number', 'desc')
                                    ->first();
                                if ($document_found) {
                                    $document_number = $document_found->number;
                                    $document_number = $document_number + 1;
                                    if ($document_number) {
                                        $data['number'] = $document_number;
                                    }
                                }
                            } else {
                                $data['number'] = 1;
                            }
                        }
                    }
                }
                if ($configuration->discount_order_note) {
                    $data["discount_order_note"] = true;
                }
                $this->order_note = OrderNote::create($data);
                if (isset($data['fee'])) {
                    foreach ($data['fee'] as $row) {
                        $this->order_note->fee()->create($row);
                    }
                }

                foreach ($data['items'] as $row) {
                    $warehouse_id = Functions::valueKeyInArray($row, "warehouse_id", null);
                    $discounts_acc = Functions::valueKeyInArray($row, 'discounts_acc', null);
                    if ($warehouse_id &&  $warehouse_id == 0) {
                        $warehouse = Warehouse::where('establishment_id', auth()->user()->establishment_id)->first();
                        $row['warehouse_id'] = $warehouse->id;
                    }
                    if ($discounts_acc) {
                        $row['discounts_acc'] = $discounts_acc;
                        // $row['item']['discounts_acc'] = $discounts_acc;
                    }

                    $this->generalSetIdLoteSelectedToItem($row);
                    $this->order_note->items()->create($row);
                    if ($is_food_dealer) {
                        $person_id = $data['customer_id'];
                        $item_id = $row['item_id'];
                        $date_of_issue = $data['date_of_issue'];
                        $person_food_dealer = new PersonFoodDealer();
                        $person_food_dealer->item_id = $item_id;
                        $person_food_dealer->person_id = $person_id;

                        $person_food_dealer->order_note_id = $this->order_note->id;
                        $person_food_dealer->date_of_issue = $date_of_issue;
                        $person_food_dealer->save();
                        $order = PersonFoodDealer::where('item_id', $item_id)
                            ->where('person_id', $person_id)
                            ->where('date_of_issue', $date_of_issue)
                            ->count() + 1;
                        $person_name = $person_food_dealer->person->name;

                        $message_food_dealer .= strtoupper("{$row['item']['description']}, PERSONA: {$person_name}, FECHA: {$date_of_issue}\n");
                        if ($user_name) {
                            FoodDealerAuth::create([
                                'user_name' => $user_name,
                                'person_food_dealer_id' => $person_food_dealer->id,
                                'order' => $order,
                            ]);
                        }
                    }
                }

                $this->setFilename();
                $this->createPdf($this->order_note, "a4", $this->order_note->filename);
            });
            if (BusinessTurn::isFoodDealer()) {
            }
            return [
                'success' => true,
                'data' => [
                    'id' => $this->order_note->id,
                    'message_food_dealer' => $message_food_dealer,
                    'external_id' => $this->order_note->external_id,
                    'number_full' => $this->order_note->number_full,
                    'filename' => $this->order_note->filename,
                    'print_ticket' => $this->order_note->getUrlPrintPdf('ticket'),
                    'url_print' => $this->order_note->getUrlPrintPdf('ticket'),
                ],
            ];
        } catch (Exception $e) {
            $message = $e->getMessage();
            $message .= "Linea: " . $e->getLine();
            $message .= "Archivo: " . $e->getFile();
            Log::error($message);
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Recibe un Request y lo transforma en array para poder ser procesado por el modelo.
     *
     * @param Request|null $request
     * @param null         $order_note
     *
     * @return array
     */
    public function mergeArray(Request $request = null, $order_note = null)
    {

        $this->company = Company::active();
        $data = $request->toArray();
        $values = [
            'user_id' => ($order_note) ? $order_note->user_id : ($request->user_id ? $request->user_id : auth()->user()->id),
            'external_id' => Str::uuid()->toString(),
            'customer' => PersonInput::set($request->customer_id),
            'establishment' => EstablishmentInput::set($request->establishment_id),
            'soap_type_id' => $this->company->soap_type_id,
            'state_type_id' => '01',
            'type' => 'order-notes',
        ];

        return array_merge($data, $values);
    }

    private function setFilename()
    {

        $name = [$this->order_note->prefix, ($this->order_note->number ?? $this->order_note->id), date('Ymd')];
        if ($this->order_note->website_id) {
            $company = Company::where('website_id', $this->order_note->website_id)->first();
            if ($company) {
                $name[] = $company->number;
            }
        }
        $this->order_note->filename = join('-', $name);
        $this->order_note->save();
    }

    /**
     * @param null $order_note
     * @param null $format_pdf
     * @param null $filename
     *
     * @throws MpdfException
     */
    public function createPdf($order_note = null, $format_pdf = null, $filename = null)
    {
        ini_set("pcre.backtrack_limit", "5000000");
        $template = new Template();
        $pdf = new Mpdf();

        $document = ($order_note != null) ? $order_note : $this->order_note;
        $company = ($this->company != null) ? $this->company : Company::active();
        $filename = ($filename != null) ? $filename : $this->order_note->filename;

        // $base_template = config('tenant.pdf_template');
        $base_template = Establishment::find($document->establishment_id)->template_pdf;
        if (($format_pdf === 'ticket') or
            ($format_pdf === 'ticket_58') or
            ($format_pdf === 'ticket_50')
        ) {
            $base_template = Establishment::find($document->establishment_id)->template_ticket_pdf;
        }
        $html = $template->pdf($base_template, "order_note", $company, $document, $format_pdf);

        if ($format_pdf === 'ticket' or $format_pdf === 'ticket_80' or $format_pdf === 'ticket_58') {

            $width = 78;
            $pdf_margin_top = 2;
            $pdf_margin_right = 5;
            $pdf_margin_bottom = 0;
            $pdf_margin_left = 5;
            if (config('tenant.enabled_template_ticket_80')) $width = 76;

            if ($format_pdf === 'ticket_58') {
                $width = 58;
                $pdf_margin_top = 1;
                $pdf_margin_right = 1;
                $pdf_margin_bottom = 0;
                $pdf_margin_left = 1;
            }

            $company_name = (strlen($company->name) / 20) * 10;
            $company_address = (strlen($document->establishment->address) / 30) * 10;
            $company_number = $document->establishment->telephone != '' ? '10' : '0';
            $customer_name = strlen($document->customer->name) > '25' ? '10' : '0';
            $customer_address = (strlen($document->customer->address) / 200) * 10;
            $p_order = $document->purchase_order != '' ? '10' : '0';

            $total_exportation = $document->total_exportation != '' ? '10' : '0';
            $total_free = $document->total_free != '' ? '10' : '0';
            $total_unaffected = $document->total_unaffected != '' ? '10' : '0';
            $total_exonerated = $document->total_exonerated != '' ? '10' : '0';
            $total_taxed = $document->total_taxed != '' ? '10' : '0';
            $quantity_rows = count($document->items);
            $discount_global = 0;
            $extra_by_item_description = 0;
            foreach ($document->items as $it) {
                if (strlen($it->item->description) > 100) {
                    $extra_by_item_description += 24;
                }
                if ($it->discounts) {
                    $discount_global = $discount_global + 1;
                }
            }
            $legends = $document->legends != '' ? '10' : '0';

            $pdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => [
                    $width,
                    120 +
                        ($quantity_rows * 8) +
                        ($discount_global * 3) +
                        $company_name +
                        $company_address +
                        $company_number +
                        $customer_name +
                        $customer_address +
                        $p_order +
                        $legends +
                        $total_exportation +
                        $total_free +
                        $total_unaffected +
                        $total_exonerated +
                        $total_taxed
                ],
                'margin_top' => $pdf_margin_top,
                'margin_right' => $pdf_margin_right,
                'margin_bottom' => $pdf_margin_bottom,
                'margin_left' => $pdf_margin_left,
            ]);
        } else {
            if ($format_pdf === 'a5') {

                $company_name = (strlen($company->name) / 20) * 10;
                $company_address = (strlen($document->establishment->address) / 30) * 10;
                $company_number = $document->establishment->telephone != '' ? '10' : '0';
                $customer_name = strlen($document->customer->name) > '25' ? '10' : '0';
                $customer_address = (strlen($document->customer->address) / 200) * 10;
                $p_order = $document->purchase_order != '' ? '10' : '0';

                $total_exportation = $document->total_exportation != '' ? '10' : '0';
                $total_free = $document->total_free != '' ? '10' : '0';
                $total_unaffected = $document->total_unaffected != '' ? '10' : '0';
                $total_exonerated = $document->total_exonerated != '' ? '10' : '0';
                $total_taxed = $document->total_taxed != '' ? '10' : '0';
                $quantity_rows = count($document->items);
                $discount_global = 0;
                $extra_by_item_description = 0;
                foreach ($document->items as $it) {

                    if ($it->discounts) {
                        $discount_global = $discount_global + 1;
                    }
                }
                $legends = $document->legends != '' ? '10' : '0';


                $alto = ($quantity_rows * 8) +
                    ($discount_global * 3) +
                    $company_name +
                    $company_address +
                    $company_number +
                    $customer_name +
                    $customer_address +
                    $p_order +
                    $legends +
                    $total_exportation +
                    $total_free +
                    $total_unaffected +
                    $total_exonerated +
                    $total_taxed;
                $diferencia = 148 - (float)$alto;

                $pdf = new Mpdf([
                    'mode' => 'utf-8',
                    'format' => [
                        210,
                        $diferencia + $alto,
                    ],
                    'margin_top' => 2,
                    'margin_right' => 5,
                    'margin_bottom' => 0,
                    'margin_left' => 5,
                ]);
            } else {

                $pdf_font_regular = config('tenant.pdf_name_regular');
                $pdf_font_bold = config('tenant.pdf_name_bold');

                if ($pdf_font_regular != false) {
                    $defaultConfig = (new ConfigVariables())->getDefaults();
                    $fontDirs = $defaultConfig['fontDir'];

                    $defaultFontConfig = (new FontVariables())->getDefaults();
                    $fontData = $defaultFontConfig['fontdata'];

                    $pdf = new Mpdf([
                        'fontDir' => array_merge($fontDirs, [
                            app_path('CoreFacturalo' . DIRECTORY_SEPARATOR . 'Templates' .
                                DIRECTORY_SEPARATOR . 'pdf' .
                                DIRECTORY_SEPARATOR . $base_template .
                                DIRECTORY_SEPARATOR . 'font'),
                        ]),
                        'fontdata' => $fontData + [
                            'custom_bold' => [
                                'R' => $pdf_font_bold . '.ttf',
                            ],
                            'custom_regular' => [
                                'R' => $pdf_font_regular . '.ttf',
                            ],
                        ],
                    ]);
                }
            }
        }

        $path_css = app_path('CoreFacturalo' . DIRECTORY_SEPARATOR . 'Templates' .
            DIRECTORY_SEPARATOR . 'pdf' .
            DIRECTORY_SEPARATOR . $base_template .
            DIRECTORY_SEPARATOR . 'style.css');

        $stylesheet = file_get_contents($path_css);

        $pdf->WriteHTML($stylesheet, HTMLParserMode::HEADER_CSS);
        $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);

        if ($format_pdf != 'ticket') {
            if (config('tenant.pdf_template_footer')) {
                $html_footer = $template->pdfFooter($base_template, $this->order_note);
                $pdf->SetHTMLFooter($html_footer);
            }
            //$html_footer = $template->pdfFooter();
            //$pdf->SetHTMLFooter($html_footer);
        }
        $helper_facturalo = new HelperFacturalo();
        if ($helper_facturalo->isAllowedAddDispatchTicket($format_pdf, 'order-note', $document)) {
            $company = Company::active();
            $helper_facturalo->addDocumentDispatchTicket($pdf, $company, $document, [
                $template,
                $base_template,
                $width,
                ($quantity_rows * 8) + $extra_by_item_description
            ]);
        }
        $this->uploadFile($filename, $pdf->output('', 'S'), 'order_note');
    }

    public function uploadFile($filename, $file_content, $file_type)
    {
        $this->uploadStorage($filename, $file_content, $file_type);
    }

    public function update(OrderNoteRequest $request)
    {

        DB::connection('tenant')->transaction(function () use ($request) {

            $this->order_note = OrderNote::firstOrNew(['id' => $request['id']]);

            // $data = $this->mergeData($request, $this->order_note);
            // $this->order_note->items()->delete();
            if (isset($request['id'])) {
                OrderNoteItem::where('order_note_id', $request['id'])
                    ->get()
                    ->each(function ($orderNote) {
                        $orderNote->delete();
                    });
            }
            $data = $this->mergeArray($request, $this->order_note);

            $this->order_note->fee()->delete();

            $this->order_note->fill($data);
            // OrdenItem::where('orden_id', $this->order_note->id)->delete();

            foreach ($request['items'] as $row) {

                // $this->order_note->items()->create($row);
                // $item_id = isset($row['id']) ? $row['id'] : null;
                $item_id = $this->getRowIdItem($row);
                // $order_note_item = OrderNoteItem::
                $this->generalSetIdLoteSelectedToItem($row);
                // $order_note_item->fill($row);
                $this->order_note->items()->create($row);
                // $order_note_item->order_note_id = $this->order_note->id;
                // $order_note_item->save();
            }

            $this->setFilename();
        });

        return [
            'success' => true,
            'data' => [
                'id' => $this->order_note->id,
            ],
        ];
    }


    /**
     *
     * Obtener id de la fila al editar pedido
     *
     * @param  array $row
     * @return int|null
     */
    private function getRowIdItem($row)
    {
        $row_id = null;

        if (isset($row['id'])) {
            $row_id = $row['id'];
        } else {
            if (isset($row['record_id'])) $row_id = $row['record_id'];
        }

        return $row_id;
    }


    public function destroy_order_note_item($id)
    {

        DB::connection('tenant')->transaction(function () use ($id) {

            $item = OrderNoteItem::findOrFail($id);
            $item->delete();
        });

        return [
            'success' => true,
            'message' => 'Item eliminado'
        ];
    }

    public function duplicate(Request $request)
    {
        // return $request->id;
        $obj = OrderNote::find($request->id);
        $this->order_note = $obj->replicate();
        $this->order_note->external_id = Str::uuid()->toString();
        $this->order_note->state_type_id = '01';
        $this->order_note->save();

        foreach ($obj->items as $row) {
            $new = $row->replicate();
            $new->order_note_id = $this->order_note->id;
            $new->save();
        }

        $this->setFilename();

        return [
            'success' => true,
            'data' => [
                'id' => $this->order_note->id,
            ],
        ];
    }
    public function columns2()
    {
        //remove the locations array the elements with value 07 and 15

        return [
            'series' => Series::whereIn('document_type_id', ['PD'])->get(),

        ];
    }
    public function records2(Request $request)
    {
        $records = $this->getRecords2($request);

        return new OrderNoteCollection($records->paginate(config('tenant.items_per_page')));
    }
    private function getRecords2($request)
    {
        $series = $request->series;
        $number = $request->number;
        $records = OrderNote::query();
        if ($request->column == 'date_of_issue' && $request->value != '' && $request->value != null) {
            $records->where('date_of_issue', 'like', "%{$request->value}%");
        }

        if ($request->column == 'user_name' && $request->value != '' && $request->value != null) {
            $records->whereHas('customer', function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->value}%");
            });
        }

        if ($series) {
            $records->where('prefix', $series);
        }

        if ($number) {
            $records->where('number', $number);
        }
        $records->latest();
        return $records;
    }
    public function voided($id)
    {
        DB::connection('tenant')->transaction(function () use ($id) {
            $obj = OrderNote::find($id);
            $obj->VoidOrderNote();
            $obj->update();
        });

        return [
            'success' => true,
            'message' => 'Pedido anulado con éxito'
        ];
    }

    /**
     * @param      $inputs
     * @param null $order_note
     *
     * @return mixed
     * @deprecated  use mergeArray instead
     */
    public function mergeData($inputs, $order_note = null)
    {

        $this->company = Company::active();

        $values = [
            'user_id' => ($order_note) ? $order_note->user_id : auth()->id(),
            'external_id' => Str::uuid()->toString(),
            'customer' => PersonInput::set($inputs['customer_id']),
            'establishment' => EstablishmentInput::set($inputs['establishment_id']),
            'soap_type_id' => $this->company->soap_type_id,
            'state_type_id' => '01'
        ];

        $inputs->merge($values);

        return $inputs->all();
    }

    /**
     * @param $id
     *
     * @return array
     */
    public function searchCustomerById($id)
    {

        return $this->searchClientById($id);
    }

    public function download($external_id, $format)
    {
        $order_note = OrderNote::where('external_id', $external_id)->first();

        if (!$order_note) throw new Exception("El código {$external_id} es inválido, no se encontro el pedido relacionado");

        $this->reloadPDF($order_note, $format, $order_note->filename);

        return $this->downloadStorage($order_note->filename, 'order_note');
    }

    private function reloadPDF($order_note, $format, $filename)
    {
        $this->createPdf($order_note, $format, $filename);
    }

    public function toPrint($external_id, $format)
    {
        $order_note = OrderNote::where('external_id', $external_id)->first();

        if (!$order_note) throw new Exception("El código {$external_id} es inválido, no se encontro el pedido relacionado");

        $this->reloadPDF($order_note, $format, $order_note->filename);
        $temp = tempnam(sys_get_temp_dir(), 'order_note');

        file_put_contents($temp, $this->getStorage($order_note->filename, 'order_note'));

        /*
            $headers = [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$order_note->filename.'"'
            ];
            */

        return response()->file($temp, $this->generalPdfResponseFileHeaders($order_note->filename));
    }

    public function email(Request $request)
    {

        $client = Person::find($request->customer_id);
        $order_note = OrderNote::find($request->id);
        $customer_email = $request->input('customer_email');

        // $this->reloadPDF($order_note, "a4", $order_note->filename);
        $email = $customer_email;
        $mailable = new OrderNoteEmail($client, $order_note);
        $id = (int)$order_note->id;
        $model = __FILE__ . ";;" . __LINE__;
        $sendIt = EmailController::SendMail($email, $mailable, $id, $model);
        /*
            Configuration::setConfigSmtpMail();
            $array_email = explode(',', $customer_email);
            if (count($array_email) > 1) {
                foreach ($array_email as $email_to) {
                    $email_to = trim($email_to);
                    if(!empty($email_to)) {
                        Mail::to($email_to)->send(new OrderNoteEmail($client, $order_note));
                    }
                }
            } else {
                Mail::to($customer_email)->send(new OrderNoteEmail($client, $order_note));
            }*/
        return [
            'success' => true
        ];
    }

    public function getQuotationToOrderNote(Quotation $id)
    {
        $company = Company::query()->first();
        $configuration = Configuration::query()->first();

        return $id->getCollectionData($company, $configuration, true);
    }

    /**
     * @return OrderNote
     */
    public function getOrderNote()
    {
        return $this->order_note;
    }

    /**
     * @param OrderNote $order_note
     *
     * @return OrderNoteController
     */
    public function setOrderNote(OrderNote $order_note)
    {
        $this->order_note = $order_note;
        return $this;
    }
}
