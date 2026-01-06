<?php

namespace App\Http\Controllers\Tenant;

use Exception;
use Mpdf\Mpdf;
use Mpdf\HTMLParserMode;
use App\Models\Tenant\Item;
use App\Models\Tenant\User;
use Illuminate\Support\Str;
use App\Traits\OfflineTrait;
use Illuminate\Http\Request;
use App\Models\Tenant\Person;
use App\Models\Tenant\Series;
use App\Models\Tenant\Company;
use Modules\Item\Models\Brand;
use Mpdf\Config\FontVariables;
use App\CoreFacturalo\Template;
use App\Models\Tenant\Quotation;
use App\Models\Tenant\StateType;
use App\Models\Tenant\Warehouse;
use Mpdf\Config\ConfigVariables;
use Modules\Item\Models\Category;
use Illuminate\Support\Facades\DB;
use App\Mail\Tenant\QuotationEmail;
use App\Models\Tenant\NameDocument;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Establishment;
use Illuminate\Support\Facades\Mail;
use App\Models\Tenant\NameQuotations;
use App\Models\Tenant\PaymentMethodType;
use Modules\Finance\Traits\FinanceTrait;
use App\Models\Tenant\Catalogs\PriceType;
use App\Models\Tenant\Catalogs\CurrencyType;
use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\Catalogs\AttributeType;
use App\Models\Tenant\Catalogs\OperationType;
use App\Models\Tenant\Catalogs\SystemIscType;
use App\Http\Controllers\SearchItemController;
use App\Http\Requests\Tenant\QuotationRequest;
use App\CoreFacturalo\Requests\Inputs\Functions;
use App\Http\Resources\Tenant\QuotationResource;
use Modules\Document\Models\SeriesConfiguration;
use App\Http\Resources\Tenant\QuotationCollection;
use App\Models\Tenant\Catalogs\AffectationIgvType;
use App\Models\Tenant\Catalogs\ChargeDiscountType;
use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\CoreFacturalo\Requests\Inputs\Common\PersonInput;
use Modules\Inventory\Models\Warehouse as ModuleWarehouse;
use App\CoreFacturalo\Requests\Inputs\Common\EstablishmentInput;
use App\Models\System\Client;
use App\Models\Tenant\Cash;
use App\Models\Tenant\Catalogs\UnitType;
use App\Models\Tenant\ConditionBlockPaymentMethod;
use App\Models\Tenant\ItemUnitType;
use App\Models\Tenant\ItemWarehousePrice;
use App\Models\Tenant\PlateNumberDocument;
use App\Models\Tenant\QuotationItem;
use App\Models\Tenant\QuotationProject;
use App\Models\Tenant\QuotationProjectItem;
use App\Models\Tenant\QuotationsTechniciansQuotation;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Models\Hostname;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\BusinessTurn\Models\BusinessTurn;
use Modules\Finance\Traits\FilePaymentTrait;
use Modules\Sale\Models\TechnicalServiceQuotation;

class QuotationController extends Controller
{

    use FinanceTrait;
    use OfflineTrait;
    use StorageDocument;
    use FilePaymentTrait;

    protected $quotation;
    protected $company;

    public function byClientId($id)
    {
        $records = Quotation::where('customer_id', $id)
            ->whereDoesntHave('purchase_order')
            ->get()
            ->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'number_full' => $row->number_full,
                    'date_of_issue' => $row->date_of_issue,
                    'total' => $row->total,
                    'state_type_id' => $row->state_type_id,
                ];
            });
        return $records;
    }
    public function index()
    {
        $data = NameQuotations::first();
        $isCommercial = auth()->user()->integrate_user_type_id == 2;
        $quotations_optional =  $data != null ? $data->quotations_optional : null;
        $quotations_optional_value =  $data != null ? $data->quotations_optional_value : null;
        $company = Company::select('soap_type_id')->first();
        $soap_company = $company->soap_type_id;
        $generate_order_note_from_quotation = Configuration::getRecordIndividualColumn('generate_order_note_from_quotation');
        return view('tenant.quotations.index', compact(
            'isCommercial',
            'soap_company',
            'generate_order_note_from_quotation',
            'quotations_optional',
            'quotations_optional_value'
        ));
    }
    public function message_whatsapp($document_id)
    {
        $document = Quotation::find($document_id);
        $message = "Estimd@: *" . $document->customer->name . "* \n";
        $message .= "Informamos que su comprobante electrónico ha sido emitido exitosamente.\n";
        $message .= "Los datos de su comprobante electrónico son:\n";
        $message .= "Razón social: *" . $document->customer->name . "* \n";
        $message .= "Fecha de emisión: *" . \Carbon\Carbon::parse($document->date_of_issue)->format('d-m-Y') . "* \n";
        $message .= "Nro. de comprobante: *" . $document->series . "-" . $document->number . "* \n";
        $message .= "Total: *" . number_format($document->total, 2, ".", "") . "*";
        return [
            "message" => $message,
            "success" => true,
        ];
    }
    public function create($saleOpportunityId = null)
    {
        $data = NameQuotations::first();
        $quotations_optional =  $data != null ? $data->quotations_optional : null;
        $quotations_optional_value =  $data != null ? $data->quotations_optional_value : null;
        return view('tenant.quotations.form', compact('saleOpportunityId', 'quotations_optional', 'quotations_optional_value'));
    }

    public function edit($id)
    {
        $resourceId = $id;
        $data = NameQuotations::first();
        $quotations_optional =  $data != null ? $data->quotations_optional : null;
        $quotations_optional_value =  $data != null ? $data->quotations_optional_value : null;
        return view('tenant.quotations.form_edit', compact('resourceId', 'quotations_optional', 'quotations_optional_value'));
    }

    public function columns()
    {
        $is_integrate_system = BusinessTurn::isIntegrateSystem();
        if ($is_integrate_system) {
            return [
                'customer_phone' => 'N° celular',
                'number' => 'Número',
                'customer' => 'DNI/RUC - Nombre'
            ];
        }
        return [
            'number' => 'Número',
            'customer' => 'Cliente',
            'date_of_issue' => 'Fecha de emisión',
            'delivery_date' => 'Fecha de entrega',
            'user_name' => 'Registrado por',
            'seller_name' => 'Vendedor',
            'referential_information' => 'Inf.Referencial',
        ];
    }

    public function filter()
    {
        $state_types = StateType::whereIn('id', ['01', '05', '09'])->get();

        return compact('state_types');
    }

    public function records(Request $request)
    {
        // 
        $records = $this->getRecords($request);

        return new QuotationCollection($records->paginate(config('tenant.items_per_page')));
    }

    private function getRecords($request)
    {
        $column = $request->input('column');
        $value = $request->input('value');
        $plate_number = $request->input('plate_number');
        $query = Quotation::query();

        if ($value) {

            if ($column === 'user_name') {
                $query->whereHas('user', function ($q) use ($value) {
                    $q->where('name', 'like', "%{$value}%");
                })
                    ->whereTypeUser();
            } else if ($column == 'customer_phone') {
                $query->whereHas('person', function ($q) use ($value) {
                    $q->where('telephone', 'like', "%{$value}%");
                })
                    ->whereTypeUser();
            } else if ($column === 'customer') {
                $query->whereHas('person', function ($q) use ($value) {
                    $q->where('name', 'like', "%{$value}%")
                        ->orWhere('number', 'like', "%{$value}%");
                })
                    ->whereTypeUser();
            } else if ($column === 'seller_name') {
                $query->whereHas('seller', function ($q) use ($value) {
                    $q->where('name', 'like', "%{$value}%");
                });
            } else if ($column === 'number') {
                if (!is_null($value) && $value !== '') {
                    $query->where('number', $value)->whereTypeUser();
                }
            } else {
                $query->where($column, 'like', "%{$value}%")
                    ->whereTypeUser();
            }
        } else {
            $query->whereTypeUser();
        }

        if ($plate_number) {
            $query->whereHas('plateNumberDocument', function ($q) use ($plate_number) {
                $q->whereHas('plateNumber', function ($q2) use ($plate_number) {
                    $q2->where('description', 'like', "%{$plate_number}%");
                });
            });
        }

        $records = $query->latest();

        $form = json_decode($request->form);
        if (isset($form->date_start) && isset($form->date_end)) {

            $records->whereBetween('date_of_issue', [$form->date_start, $form->date_end]);
        }

        $state_type_id = $form->state_type_id ?? null;
        if ($state_type_id) $records->where('state_type_id', $state_type_id);

        return $records;
    }

    public function searchCustomers(Request $request)
    {
        $customers = Person::whereType('customers')
            ->orderBy('name')
            ->whereIsEnabled();
        if ($request->has('customer_id')) {
            $customers->where('id', $request->customer_id);
        } else {
            $customers->where(function ($query) use ($request) {
                $query->where('number', 'like', "%{$request->input}%")
                    ->orWhere('name', 'like', "%{$request->input}%");
            });
        }
        $customers = $customers->get()->transform(function ($row) {
            /** @var Person $row */
            return $row->getCollectionData();
            /* Se ha movido al modelo */
            return [
                'id' => $row->id,
                'description' => $row->number . ' - ' . $row->name,
                'name' => $row->name,
                'number' => $row->number,
                'identity_document_type_id' => $row->identity_document_type_id,
                'identity_document_type_code' => $row->identity_document_type->code,
                'addresses' => $row->addresses,
                'address' => $row->address,
            ];
        });
        return compact('customers');
    }

    public function tablesCompany($id)
    {
        $company = Company::where('website_id', $id)->first();
        $company_active = Company::active();
        $document_number = $company->document_number;
        $website_id = $company->website_id;
        $user = auth()->user()->id;
        $user_to_save = User::find($user);
        $user_to_save->company_active_id = $website_id;
        $user_to_save->save();
        $key = "cash_" . $company_active->name . "_" . $user;
        Cache::put($key, $website_id, 60);
        $payment_destinations = $this->getPaymentDestinations();
        if ($website_id && $company->id != $company_active->id) {
            $hostname = Hostname::where('website_id', $website_id)->first();
            $client = Client::where('hostname_id', $hostname->id)->first();
            $tenancy = app(Environment::class);
            $tenancy->tenant($client->hostname->website);
        }
        $establishment = Establishment::find(1);
        $establishment_info = EstablishmentInput::set($establishment->id);
        $series = Series::where('establishment_id', $establishment->id)->get()
            ->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'contingency' => (bool)$row->contingency,
                    'document_type_id' => $row->document_type_id,
                    'establishment_id' => $row->establishment_id,
                    'number' => $row->number,
                ];
            });
        // $series = Series::FilterSeries(1)
        //     ->get()
        //     ->transform(function ($row)  use ($document_number) {
        //         /** @var Series $row */
        //         return $row->getCollectionData2($document_number);
        //     })->where('disabled', false);
        return [
            'success' => true,
            'data' => $company,
            'payment_destinations' => $payment_destinations,
            'series' => $series,
            'establishment' => $establishment_info,
        ];
    }
    public function tables()
    {
        $unit_types = UnitType::where('active', true)->get();
        $business_turns = BusinessTurn::where('active', true)->get();
        $is_integrate_system = BusinessTurn::isIntegrateSystem();
        $customers = $this->table('customers');
        $establishments = Establishment::where('id', auth()->user()->establishment_id)->get();
        $all_establishments = Establishment::whereActive()->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'description' => $row->description
            ];
        });
        $currency_types = CurrencyType::whereActive()->get();
        // $document_types_invoice = DocumentType::whereIn('id', ['01', '03'])->where('active',true)->get();
        $discount_types = ChargeDiscountType::whereType('discount')->whereLevel('item')->get();
        $charge_types = ChargeDiscountType::whereType('charge')->whereLevel('item')->get();
        $company = Company::active();
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $document_type_03_filter = config('tenant.document_type_03_filter');
        $payment_method_types = ConditionBlockPaymentMethod::getCashPaymentMethods();
        $payment_method_types_credit = ConditionBlockPaymentMethod::getCreditPaymentMethods();
        $payment_destinations = $this->getPaymentDestinations();
        $configuration = Configuration::select(
            'multi_companies',
            'package_handlers',
            'quotation_projects',
            'destination_sale'
        )->first();
        $affectation_igv_types = AffectationIgvType::whereActive()->get();
        $serie = Series::where('document_type_id', "COT")->where("establishment_id", auth()->user()->establishment_id)->first();
        if (!$serie) {
            $serie = Series::where('document_type_id', "COT")->where("establishment_id", $establishments[0]->id)->first();
        }
        $series = collect(Series::where(
            'establishment_id',
            $establishments[0]->id
        )->get())->transform(function ($row) {
            return [
                'id' => $row->id,
                'contingency' => (bool) $row->contingency,
                'document_type_id' => $row->document_type_id,
                'establishment_id' => $row->establishment_id,
                'number' => $row->number
            ];
        });
        if ($serie) {
            $serie = $serie->number;
        }
        /*
        carlomagno83/facturadorpro4#233

        $sellers = User::without(['establishment'])
            ->whereIn('type', ['seller'])
            ->orWhere('id', auth()->user()->id)
            ->get();
        */
        $sellers = User::GetSellers(false)->where('is_locked', false)->get();
        $companies = Company::all();
        if ($configuration->multi_companies) {
            $companies = Company::all();
        }
        return compact(
            'unit_types',
            'all_establishments',
            'payment_method_types_credit',
            'companies',
            'business_turns',
            'affectation_igv_types',
            'is_integrate_system',
            'series',
            'categories',
            'brands',
            'customers',
            'serie',
            'establishments',
            'currency_types',
            'discount_types',
            'charge_types',
            'configuration',
            'company',
            'document_type_03_filter',
            'payment_method_types',
            'payment_destinations',
            'sellers'
        );
    }


    public function option_tables($quotation_id = null)
    {
        $configuration = Configuration::getConfig();
        $company_id = null;
        $establishment_info = null;
        $global_discount_types = ChargeDiscountType::getGlobalDiscounts();
        $quotation = Quotation::find($quotation_id);
        $website_id = $quotation ? $quotation->website_id : null;
        $payment_destinations = $this->getPaymentDestinations();
        $document_types_invoice = DocumentType::whereIn('id', ['01', '03'])->where('active', true)->get();
        $exclude_payment_methods = $configuration->nc_payment_nv ? [] : ['NC'];
        $payment_method_types = PaymentMethodType::getPaymentMethodTypes($exclude_payment_methods);
        $sellers = User::where('establishment_id', auth()->user()->establishment_id)->whereIn('type', ['seller', 'admin'])->orWhere('id', auth()->user()->id)->get();

        if ($configuration->multi_companies && $quotation && $website_id) {
            // $website_id = $alter_company->website_id;
            $company_alter = Company::where('website_id', $website_id)->first();
            $document_number = $company_alter->document_number;
            $key = 'cash_' . auth()->user()->id;
            $company_active_id = Cache::put($key, $website_id, 60);
            User::find(auth()->user()->id)->update(['company_active_id' => $website_id]);
            $payment_destinations = $this->getPaymentDestinations();
            $company_id = $company_alter->website_id;
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
        // $payment_method_types = PaymentMethodType::all();
        // $sellers = User::GetSellers(true)->get();

        return compact(
            'global_discount_types',
            'establishment_info',
            'company_id',
            'series',
            'document_types_invoice',
            'payment_method_types',
            'payment_destinations',
            'sellers'
        );
    }

    public function item_tables()
    {
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        // $items = $this->table('items');
        $items = SearchItemController::getItemsToQuotation();
        $affectation_igv_types = AffectationIgvType::whereActive()->get();
        $system_isc_types = SystemIscType::whereActive()->get();
        $price_types = PriceType::whereActive()->get();
        $discount_types = ChargeDiscountType::whereType('discount')->whereLevel('item')->get();
        $charge_types = ChargeDiscountType::whereType('charge')->whereLevel('item')->get();
        $attribute_types = AttributeType::whereActive()->orderByDescription()->get();
        $is_client = $this->getIsClient();
        $operation_types = OperationType::whereActive()->get();

        return compact(
            'categories',
            'brands',
            'items',
            'operation_types',
            'affectation_igv_types',
            'system_isc_types',
            'price_types',
            'discount_types',
            'charge_types',
            'attribute_types',
            'is_client'
        );
    }

    public function record($id)
    {
        $record = new QuotationResource(Quotation::findOrFail($id));

        return $record;
    }

    public function record2($id)
    {
        $record = new QuotationResource(Quotation::findOrFail($id));

        return $record;
    }


    public function getFullDescription($row)
    {

        $desc = ($row->internal_id) ? $row->internal_id . ' - ' . $row->description : $row->description;
        $category = ($row->category) ? " - {$row->category->name}" : "";
        $brand = ($row->brand) ? " - {$row->brand->name}" : "";
        $location = ($row->location) ? " - {$row->location}" : "";
        $desc = "{$desc} {$category} {$brand} {$location}";

        return $desc;
    }
    function duplicateQuotationProjectItem($quotation_item_id, $new_quotation_item_id)
    {
        $reference_quotation_item_project = QuotationProjectItem::where('quotation_item_id', $quotation_item_id)->first();
        $quotation_item_project = new QuotationProjectItem;
        $quotation_item_project->quotation_item_id = $new_quotation_item_id;
        $quotation_item_project->disponibility = $reference_quotation_item_project->disponibility;
        $quotation_item_project->header = $reference_quotation_item_project->header;
        $quotation_item_project->save();
    }
    function createQuotationProjectItem($quotation_item, $row)
    {
        $quotation_item_project = new QuotationProjectItem;
        $quotation_item_project->quotation_item_id = $quotation_item->id;
        if (isset($row["disponibilidad"])) {
            $quotation_item_project->disponibility = $row["disponibilidad"];
        }
        if (isset($row["disponibility"])) {
            $quotation_item_project->disponibility = $row["disponibility"];
        }
        $quotation_item_project->header = $row["header"];
        $quotation_item_project->save();
    }
    function duplicateQuotationProject($quotation_id)
    {
        $reference_quotation_project = QuotationProject::where('quotation_id', $quotation_id)->first();
        $quotation_project = new QuotationProject;
        $quotation_project->quotation_id = $this->quotation->id;
        $quotation_project->project_name = $reference_quotation_project->project_name;
        $quotation_project->atention = $reference_quotation_project->atention;
        $quotation_project->direction = $reference_quotation_project->direction;
        $quotation_project->email = $reference_quotation_project->email;
        $quotation_project->telephone = $reference_quotation_project->telephone;
        $quotation_project->limit_date = $reference_quotation_project->limit_date;
        $quotation_project->observations = $reference_quotation_project->observations;
        $quotation_project->percentage = $reference_quotation_project->percentage;
        $quotation_project->save();
    }
    function createQuotationProject($request)
    {
        $quotation_project = new QuotationProject;
        $quotation_project->quotation_id = $this->quotation->id;
        $quotation_project->project_name = $request->project_name;
        $quotation_project->atention = $request->atention;
        $quotation_project->direction = $request->direction;
        $quotation_project->email = $request->email;
        $quotation_project->telephone = $request->telephone;
        $quotation_project->observations = $request->observations;
        $quotation_project->limit_date = $request->limit_date;
        $quotation_project->percentage = $request->percentage;
        $quotation_project->save();
    }
    public function store(QuotationRequest $request)
    {
        DB::connection('tenant')->transaction(function () use ($request) {

            $configuration = Configuration::first();
            $is_project = $configuration->quotation_projects == 1;
            $alter_establishment = Functions::valueKeyInArray($request, 'establishment');
            $quotation_technician_id = Functions::valueKeyInArray($request, 'quotation_technician_id', null);
            $data = $this->mergeData($request);
            $company_id = Functions::valueKeyInArray($request, 'company_id', null);
            $data['terms_condition'] = $this->getTermsCondition();
            $data['quotations_optional'] = $request->quotations_optional;
            $data['quotations_optional_value'] = $request->quotations_optional_value;
            $series = Functions::valueKeyInArray($data, "prefix", null);
            $series_id = Functions::valueKeyInArray($data, "series_id", null);
            
            $plate_number_id = Functions::valueKeyInArray($request, 'plate_number_id', null);
            $technical_service_id = Functions::valueKeyInArray($request, 'technical_service_id', null);
            if ($series_id) {
                $series_configuration = Series::find($series_id);
                $data["prefix"] = $series_configuration->number;
            }
            if (
                Quotation::where('prefix', $data["prefix"])
                ->count() == 0 && $series
            ) {
                $series_configuration = SeriesConfiguration::where([['document_type_id', "COT"], ['series', $series]])->first();
                if ($series_configuration) {
                    $number = $series_configuration->number ?? 1;
                    $data["id"] = $number;
                    $data["number"] = $number;
                }
            }

            $number = Functions::valueKeyInArray($data, "number", null);

            if (!$number || $number == "#") {
                //get last id from table
                $last_id = Quotation::where('prefix', $data["prefix"])
                    ->lockForUpdate()
                    ->orderBy('id', 'desc')
                    ->first();
                if ($last_id) {
                    if ($last_id->number) {
                        $data["number"] = $last_id->number + 1;
                    } else {
                        $data["number"] = $last_id->id + 1;
                    }
                } else {
                    $data["number"] = 1;
                }
            }

            if ($company_id) {
                $prefix = Functions::valueKeyInArray($request, "prefix", null);
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

                $alter_number = Functions::valueKeyInArray($request, 'number');

                if ($alter_number) {
                    $data['number'] = $alter_number;
                }
                if (!$alter_number) {



                    $document_found = Quotation::where('prefix', $data['prefix'])
                        ->where('website_id', $company_id)
                        ->lockForUpdate()
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
                            $document_found =  Quotation::where('prefix', $data['prefix'])
                                ->whereNull('website_id')
                                ->lockForUpdate()
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
            $this->quotation = Quotation::create($data);

            if ($technical_service_id) {
                TechnicalServiceQuotation::create([
                    'quotation_id' => $this->quotation->id,
                    'technical_service_id' => $technical_service_id,
                ]);
            }
            QuotationsTechniciansQuotation::where('quotation_id', $this->quotation->id)->delete();
            if ($quotation_technician_id) {
                QuotationsTechniciansQuotation::create([
                    'quotation_id' => $this->quotation->id,
                    'quotation_technician_id' => $quotation_technician_id,
                ]);
            }


            if ($is_project) {
                $this->createQuotationProject($request);
            }
            if ($configuration->plate_number_config) {
                PlateNumberDocument::where('quotation_id', $this->quotation->id)->delete();
                if ($plate_number_id) {
                    PlateNumberDocument::create([
                        'plate_number_id' => $plate_number_id,
                        'quotation_id' => $this->quotation->id,
                        'km' => Functions::valueKeyInArray($data, 'km'),
                    ]);
                }
            }
            $fee = Functions::valueKeyInArray($data, 'fee', []);
            $this->saveFees($this->quotation, $fee);

            foreach ($data['items'] as $row) {
                $update_price = Functions::valueKeyInArray($row, 'update_price', false);
                $quotation_item =  $this->quotation->items()->create($row);
                if ($is_project) {
                    $this->createQuotationProjectItem($quotation_item, $row);
                }
                if ($update_price) {
                    try {
                        $price = floatval($row["unit_price"]);
                        if (isset($row["item"]["presentation"]["id"])) {
                            $id = $row["item"]["presentation"]["id"];
                            $unit_type = ItemUnitType::find($id);
                            if ($unit_type) {
                                switch ($unit_type->price_default) {
                                    case 1:
                                        $unit_type->price1 = $price;
                                        break;
                                    case 2:
                                        $unit_type->price2 = $price;
                                        break;
                                    default:
                                        $unit_type->price3 = $price;
                                        break;
                                }

                                $unit_type->save();
                            }
                        } else {

                            $item_id = $row["item_id"];
                            Item::where('id', $item_id)->update(["sale_unit_price" => $price]);
                            if (isset($row["warehouse_id"]) && $row["warehouse_id"] != null) {
                                ItemWarehousePrice::where('item_id', $item_id)
                                    ->where('warehouse_id', $row["warehouse_id"])
                                    ->update(["sale_unit_price" => $price]);
                            }
                        }
                    } catch (Exception $e) {
                        Log::error($e->getMessage());
                    }
                }
            }
            $this->checkTotals($this->quotation);
            $this->savePayments($this->quotation, $data['payments']);

            $this->setFilename();

            $this->createPdf($this->quotation, "a4", $this->quotation->filename);
        });

        return [
            'success' => true,
            'data' => [
                'id' => $this->quotation->id,
                'number_full' => $this->quotation->number_full,
            ],
        ];
    }

    public function update(QuotationRequest $request)
    {

        DB::connection('tenant')->transaction(function () use ($request) {
            // $data = $this->mergeData($request);
            // return $request['id'];
            $configuration = Configuration::select('terms_condition', 'quotation_projects')->first();
            $request['terms_condition'] = $this->getTermsCondition();
            $data['quotations_optional'] = $request->quotations_optional;
            $plate_number_id = Functions::valueKeyInArray($request, 'plate_number_id', null);
            $quotation_technician_id = Functions::valueKeyInArray($request, 'quotation_technician_id', null);

            $data['quotations_optional_value'] = $request->quotations_optional_value;
            $series_id = Functions::valueKeyInArray($request, "series_id", null);
            if ($series_id) {
                $series_configuration = Series::find($series_id);
                $request["prefix"] = $series_configuration->number;
            }
            if(!$request['id']){
                return [
                    'success' => false,
                    'message' => 'No se está enviando el id de la cotización para la actualización. Intente nuevamente.'
                ];
            }
            $this->quotation = Quotation::find($request['id']);
            if(!$this->quotation){
                return [
                    'success' => false,
                    'message' => 'Cotización con el '.$request['id'].' no encontrada'
                ];
            }
            $this->quotation->fill($request->all());
            QuotationProject::where('quotation_id', $this->quotation->id)->delete();
            if ($configuration->quotation_projects == 1) {
                $this->createQuotationProject($request);
            }
            $this->quotation->customer = PersonInput::set($request['customer_id'], isset($request['customer_address_id']) ? $request['customer_address_id'] : null);
            $items_id = $this->quotation->items->pluck('id')->toArray();
            QuotationProjectItem::whereIn('quotation_item_id', $items_id)->delete();
            $this->quotation->items()->delete();

            $this->deleteAllPayments($this->quotation->payments);

            foreach ($request['items'] as $row) {

                $quotation_item =   $this->quotation->items()->create($row);
                if ($configuration->quotation_projects == 1) {
                    $this->createQuotationProjectItem($quotation_item, $row);
                }
            }
            $this->checkTotals($this->quotation, true);

            $this->savePayments($this->quotation, $request['payments']);

            $this->setFilename();
            if ($configuration->plate_number_config) {
                PlateNumberDocument::where('quotation_id', $this->quotation->id)->delete();
                if ($plate_number_id) {
                    PlateNumberDocument::create([
                        'plate_number_id' => $plate_number_id,
                        'quotation_id' => $this->quotation->id,
                        'km' => Functions::valueKeyInArray($data, 'km'),
                    ]);
                }
            }
            QuotationsTechniciansQuotation::where('quotation_id', $this->quotation->id)->delete();
            if ($quotation_technician_id) {
                QuotationsTechniciansQuotation::create([
                    'quotation_id' => $this->quotation->id,
                    'quotation_technician_id' => $quotation_technician_id,
                ]);
            }
        });

        $this->quotation->auditUpdated(null, $this->quotation->total, $this->quotation->total);

        return [
            'success' => true,
            'data' => [
                'id' => $this->quotation->id,
            ],
        ];
    }
    private function checkTotals($quotation, $update = false)
    {
        if ($quotation->total_discount == 0 && $quotation->total_charge == 0) {
            $total = $quotation->total;
            $total_items = $quotation->items->whereNotIn('affectation_igv_type_id', ["11", "12", "13", "14", "15", "16"])
                ->sum('total');
            $total = floatval($total);
            $total_items = floatval($total_items);
            if ($total != $total_items) {
                // throw new \Exception("El total de los items no coincide con el total de la cotizacion");
                $message = "El total de la cotización con id {$quotation->id} no coincide con el total de los items: {$total_items} ({$total})";
                if ($update) {
                    $message .= " (actualización)";
                }
                Log::error($message);
            }
        }
    }
    public function getTermsCondition()
    {

        $configuration = Configuration::select('terms_condition')->first();

        if ($configuration) {
            return $configuration->terms_condition;
        }

        return null;
    }


    public function duplicate(Request $request)
    {
        // return $request->id;
        $configuration = Configuration::first();
        $get_last_quotation = Quotation::orderBy('id', 'desc')->first();
        $number = $get_last_quotation->number + 1;
        if (!$number) {
            $number = $get_last_quotation->id + 1;
        }
        $obj = Quotation::find($request->id);
        $this->quotation = $obj->replicate();
        $this->quotation->number = $number;
        $this->quotation->external_id = Str::uuid()->toString();
        $this->quotation->state_type_id = '01';
        $is_project = $configuration->quotation_projects == 1;
        $this->quotation->save();
        if ($is_project) {
            $this->duplicateQuotationProject($obj->id);
        }

        foreach ($obj->items as $row) {
            $new = $row->replicate();
            $new->quotation_id = $this->quotation->id;
            $new->save();
            if ($is_project) {
                $this->duplicateQuotationProjectItem($row->id, $new->id);
            }
        }

        $this->setFilename();

        return [
            'success' => true,
            'data' => [
                'id' => $this->quotation->id,
            ],
        ];
    }

    public function columns2()
    {
        return [
            'series' => Series::whereIn('document_type_id', ['COT'])->get(),

        ];
    }
    public function records2(Request $request)
    {

        $records = $this->getRecords2($request);
        return new QuotationCollection($records->paginate(config('tenant.items_per_page')));
    }


    private function getRecords2($request)
    {
        $series = $request->series;
        $number = $request->number;
        $records = Quotation::query();
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
    public function anular($id)
    {
        $obj = Quotation::find($id);
        $obj->state_type_id = 11;
        $obj->save();
        $obj->auditVoided('Anulado');
        return [
            'success' => true,
            'message' => 'Producto anulado con éxito'
        ];
    }

    public function mergeData($inputs)
    {

        $this->company = Company::active();
        $cash_id = Functions::valueKeyInArray($inputs, 'cash_id');
        if(!$cash_id){
            $cash = Cash::where('state', 1)->where('user_id', User::getUserCashId())->latest()->first();
            $cash_id = $cash ? $cash->id : null;
        }
        $user = auth()->user() ?? auth('api')->user();
        $values = [
            'cash_id' => $cash_id,
            'user_id' => $user->id,
            'external_id' => Str::uuid()->toString(),
            'customer' => PersonInput::set($inputs['customer_id'], isset($inputs['customer_address_id']) ? $inputs['customer_address_id'] : null),
            'establishment' => EstablishmentInput::set($inputs['establishment_id']),
            'soap_type_id' => $this->company->soap_type_id,
            'state_type_id' => '01',
            'payment_method_type_id' => Functions::valueKeyInArray($inputs, 'payment_method_type_id', '01')
        ];

        $inputs->merge($values);

        return $inputs->all();
    }


    private function setFilename()
    {

        $name = [$this->quotation->prefix, $this->quotation->number ?? $this->quotation->id, date('Ymd')];
        if ($this->quotation->website_id) {
            $company = Company::where('website_id', $this->quotation->website_id)->first();
            if ($company) {
                $name[] = $company->number;
            }
        }
        $this->quotation->filename = join('-', $name);
        $this->quotation->save();
    }


    public function table($table)
    {
        switch ($table) {
            case 'customers':

                $customers = Person::whereType('customers')->whereIsEnabled()->orderBy('name')->take(20)->get()->transform(function ($row) {
                    /** @var Person $row */
                    return $row->getCollectionData();
                    /** Se ha movido al modelo */
                    return [
                        'id' => $row->id,
                        'description' => $row->number . ' - ' . $row->name,
                        'name' => $row->name,
                        'number' => $row->number,
                        'identity_document_type_id' => $row->identity_document_type_id,
                        'identity_document_type_code' => $row->identity_document_type->code,
                        'addresses' => $row->addresses,
                        'address' => $row->address
                    ];
                });
                return $customers;

                break;

            case 'items':

                $warehouse = Warehouse::where('establishment_id', auth()->user()->establishment_id)->first();

                $items = Item::orderBy('description')->whereIsActive()
                    // ->with(['warehouses' => function($query) use($warehouse){
                    //     return $query->where('warehouse_id', $warehouse->id);
                    // }])
                    ->take(20)->get();

                $this->ReturnItem($items);

                return $items;

                break;
            default:
                return [];

                break;
        }
    }


    /**
     * Realiza la busqueda de producto en cotizacion.
     * @param Request $request
     * @return array
     */
    public function searchItems(Request $request)
    {
        $items = SearchItemController::getItemsToQuotation($request);
        return compact('items');
    }

    public function items($id)
    {
        $quotation_items = QuotationItem::where('quotation_id', $id)->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'description' => $row->item->description,
                'name_product_pdf' => $row->name_product_pdf,
                'quantity' => $row->quantity,
            ];
        });
        return compact('quotation_items');
    }

    /**
     * Normaliza la salida de la colección de items para su consumo en las funciones.
     *
     */
    public function ReturnItem(&$item)
    {
        $configuration = Configuration::first();
        $establishment_id = auth()->user()->establishment_id;
        $warehouse = \Modules\Inventory\Models\Warehouse::where('establishment_id', $establishment_id)->first();

        $item->transform(function ($row) use ($configuration, $warehouse) {
            /** @var \App\Models\Tenant\Item $row */
            return $row->getDataToItemModal($warehouse, false, true);
            /** Se ha movido al modelo*/
            $full_description = $this->getFullDescription($row);
            return [
                'id' => $row->id,
                'full_description' => $full_description,
                'description' => $row->description,
                'currency_type_id' => $row->currency_type_id,
                'model' => $row->model,
                'brand' => $row->brand,
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
                        'range_min' => $row->range_min,
                        'range_max' => $row->range_max,
                    ];
                }),
                'warehouses' => collect($row->warehouses)->transform(function ($row) {
                    return [
                        'warehouse_id' => $row->warehouse->id,
                        'warehouse_description' => $row->warehouse->description,
                        'stock' => $row->stock,

                    ];
                }),

            ];
        });
    }

    public function searchItemById($id)
    {

        $items = SearchItemController::getItemsToQuotation(null, $id);
        return compact('items');
    }


    public function searchCustomerById($id)
    {
        return $this->searchClientById($id);
    }

    public function download($external_id, $format)
    {
        $quotation = Quotation::where('external_id', $external_id)->first();

        if (!$quotation) throw new Exception("El código {$external_id} es inválido, no se encontro la cotización relacionada");

        $this->reloadPDF($quotation, $format, $quotation->filename);

        return $this->downloadStorage($quotation->filename, 'quotation');
    }

    public function toPrint($external_id, $format)
    {
        $quotation = Quotation::where('external_id', $external_id)->first();

        if (!$quotation) throw new Exception("El código {$external_id} es inválido, no se encontro la cotización relacionada");

        $this->reloadPDF($quotation, $format, $quotation->filename);
        $temp = tempnam(sys_get_temp_dir(), 'quotation');

        file_put_contents($temp, $this->getStorage($quotation->filename, 'quotation'));

        /*
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$quotation->filename.'"'
        ];
        */

        return response()->file($temp, $this->generalPdfResponseFileHeaders($quotation->filename));
    }

    private function reloadPDF($quotation, $format, $filename)
    {
        $this->createPdf($quotation, $format, $filename);
    }

    public function saveFees($quotation, $fees)
    {
        foreach ($fees as $row) {
            $quotation->fee()->create($row);
        }
    }
    public function getWidthTicket($format_pdf)
    {
        $width = 0;

        if (config('tenant.enabled_template_ticket_80')) {
            $width = 76;
        } else {
            switch ($format_pdf) {
                case 'ticket_58':
                    $width = 56;
                    break;
                case 'ticket_50':
                    $width = 45;
                    break;
                default:
                    $width = 78;
                    break;
            }
        }

        return $width;
    }

    public function createPdf($quotation = null, $format_pdf = null, $filename = null)
    {
        ini_set("pcre.backtrack_limit", "5000000");
        $template = new Template();
        $pdf = new Mpdf();

        $document = ($quotation != null) ? $quotation : $this->quotation;
        $company = ($this->company != null) ? $this->company : Company::active();
        $filename = ($filename != null) ? $filename : $this->quotation->filename;

        $configuration = Configuration::first();
        if ($configuration->multi_companies &&  $document->website_id) {
            $company = Company::where('website_id', $document->website_id)->first();
            if ($company) {
                $this->company = $company;
            }
        }
        $establishment = Establishment::find($document->establishment_id);
        if ($establishment->template_quotations) {
            $base_template = $establishment->template_quotations;
        } else {
            $base_template = $establishment->template_pdf;
        }

        if (($format_pdf === 'ticket') or
            ($format_pdf === 'ticket_58') or

            ($format_pdf === 'ticket_50')
        ) {
            // $base_template = Establishment::find($document->establishment_id)->template_ticket_pdf;
            if ($establishment->template_quotations_ticket) {
                $base_template = $establishment->template_quotations_ticket;
            } else {
                $base_template = $establishment->template_ticket_pdf;
            }
        }


        $is_project = $configuration->quotation_projects == 1;
        if ($is_project) {
            $quotation_project = QuotationProject::where('quotation_id', $document->id)->first();
            if ($quotation_project && $format_pdf == 'a4' || $format_pdf == null) {
                $base_template = 'project';
            }
        }

        $html = $template->pdf($base_template, "quotation", $company, $document, $format_pdf);

        if ($format_pdf === 'ticket' or $format_pdf === 'ticket_80' or $format_pdf === 'ticket_58') {

            $width = $this->getWidthTicket($format_pdf);

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
            $payments = $document->payments()->count() * 5;
            $discount_global = 0;
            $terms_condition = $document->terms_condition ? 15 : 0;
            $contact = $document->contact ? 15 : 0;

            $document_description = ($document->description) ? count(explode("\n", $document->description)) * 3 : 0;


            foreach ($document->items as $it) {
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
                        $payments +
                        $total_exonerated +
                        $terms_condition +
                        $contact +
                        $document_description +
                        $total_taxed - ($format_pdf === 'ticket_58' ? 50 : 0)
                ],
                'margin_top' => 2,
                'margin_right' => $format_pdf === 'ticket_58' ? 2 : 5,
                'margin_bottom' => 0,
                'margin_left' => $format_pdf === 'ticket_58' ? 2 : 5
            ]);
        } 
        
        
        
        
        else if ($format_pdf === 'a5') {

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
                    $diferencia + $alto
                ],
                'margin_top' => 2,
                'margin_right' => 5,
                'margin_bottom' => 0,
                'margin_left' => 5
            ]);
        } else {


            $pdf_font_regular = config('tenant.pdf_name_regular');
            $pdf_font_bold = config('tenant.pdf_name_bold');

            if ($pdf_font_regular != false) {
                $defaultConfig = (new ConfigVariables())->getDefaults();
                $fontDirs = $defaultConfig['fontDir'];

                $defaultFontConfig = (new FontVariables())->getDefaults();
                $fontData = $defaultFontConfig['fontdata'];

                $default = [
                    'fontDir' => array_merge($fontDirs, [
                        app_path('CoreFacturalo' . DIRECTORY_SEPARATOR . 'Templates' .
                            DIRECTORY_SEPARATOR . 'pdf' .
                            DIRECTORY_SEPARATOR . $base_template .
                            DIRECTORY_SEPARATOR . 'font')
                    ]),
                    'fontdata' => $fontData + [
                        'custom_bold' => [
                            'R' => $pdf_font_bold . '.ttf',
                        ],
                        'custom_regular' => [
                            'R' => $pdf_font_regular . '.ttf',
                        ],
                    ]
                ];

                if ($base_template == 'citec') {
                    $default = [
                        'mode' => 'utf-8',
                        'margin_top' => 2,
                        'margin_right' => 0,
                        'margin_bottom' => 0,
                        'margin_left' => 0,
                        'fontDir' => array_merge($fontDirs, [
                            app_path('CoreFacturalo' . DIRECTORY_SEPARATOR . 'Templates' .
                                DIRECTORY_SEPARATOR . 'pdf' .
                                DIRECTORY_SEPARATOR . $base_template .
                                DIRECTORY_SEPARATOR . 'font')
                        ]),
                        'fontdata' => $fontData + [
                            'custom_bold' => [
                                'R' => $pdf_font_bold . '.ttf',
                            ],
                            'custom_regular' => [
                                'R' => $pdf_font_regular . '.ttf',
                            ],
                        ]
                    ];
                }


                if (in_array($base_template, ['personalizada_horizontal', 'personalizada_gonzalo_ultramix', 'concremix'])) {

                    $default = [
                        'mode' => 'utf-8',
                        'format' => "A4",
                        'margin_top' => 5,
                        'margin_right' => 5,
                        'margin_bottom' => 5,
                        'margin_left' => 5
                    ];
                } else {

                    $default = [
                        'mode' => 'utf-8',
                        'format' => "A4",
                        'margin_top' => 15,
                        'margin_right' => 15,
                        'margin_bottom' => 15,
                        'margin_left' => 15
                    ];
                }

                $pdf = new Mpdf($default);
            }

            if (in_array($base_template, ['personalizada_latinfac_coti', 'personalizada_gonzalo_ultramix', 'ultramix', 'concremix', 'personalizada_famavet', 'famavet', 'rounded'])) {
                $default = [
                    'mode' => 'utf-8',
                    'margin_top' => 5,
                    'margin_right' => 5,
                    'margin_bottom' => 5,
                    'margin_left' => 5,
                ];
            } else if (in_array($base_template, ['default_footer_carousel', 'cotizacion_multiservicio'])) {
                $default = [
                    'mode' => 'utf-8',
                    'margin_top' => 5,
                    'margin_right' => 5,
                    'margin_bottom' => 1,
                    'margin_left' => 5,
                    'margin_footer' => 2,
                ];
            } else {
                $default = [
                    'mode' => 'utf-8',
                    'margin_top' => 15,
                    'margin_right' => 15,
                    'margin_bottom' => 15,
                    'margin_left' => 15,
                ];
            }

            $pdf = new Mpdf($default);
        }

        if (in_array($base_template, ['personalizada_gonzalo_ultramix', 'personalizada_gonzalo_concremix', 'concremix', 'ultramix'])) {
            $path_css = app_path('CoreFacturalo' . DIRECTORY_SEPARATOR . 'Templates' .
                DIRECTORY_SEPARATOR . 'pdf' .
                DIRECTORY_SEPARATOR . $base_template .
                DIRECTORY_SEPARATOR . 'quotations.css');
        } else {
            $path_css = app_path('CoreFacturalo' . DIRECTORY_SEPARATOR . 'Templates' .
                DIRECTORY_SEPARATOR . 'pdf' .
                DIRECTORY_SEPARATOR . $base_template .
                DIRECTORY_SEPARATOR . 'style.css');
        }

        $stylesheet = file_get_contents($path_css);

        $pdf->WriteHTML($stylesheet, HTMLParserMode::HEADER_CSS);
        // $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);
        if ($base_template === 'famavet') {
            $backgroud_image_document = $company->logo;
            if ($backgroud_image_document) {
                $url =  url('/storage/uploads/logos/' . $backgroud_image_document);
                $pdf->SetWatermarkImage($url, 0.15);
                $pdf->showWatermarkImage = true;
            }
        }
        if ($base_template === 'cotizacion_multiservicio') {
            $logo = $company->logo;

            if ($logo) {
                $logo_path = storage_path('app/public/uploads/logos/' . $logo);

                if (file_exists($logo_path)) {
                    try {
                        $pdf->SetWatermarkImage($logo_path, 0.15, 'D', array(0, 50));
                        $pdf->showWatermarkImage = true;
                    } catch (\Exception $e) {
                        Log::error("Error al establecer marca de agua: " . $e->getMessage());
                    }
                }
            }
        }
        if ($format_pdf != 'ticket') {
            if (config('tenant.pdf_template_footer')) {
                $html_footer = $template->pdfFooter($base_template, $this->quotation ?? $document);
                $html_footer_term_condition = ($document->terms_condition) ? $template->pdfFooterTermCondition($base_template, $document) : "";

                $html_footer_legend = "";
                if ($configuration->legend_footer) {
                    try {
                        $html_footer_legend = $template->pdfFooterLegend($base_template, $this->quotation);
                    } catch (\Exception $e) {
                        Log::error("Error al establecer leyenda de footer: " . $e->getMessage());
                    }
                }

                $html_footer_images = "";
                $this->setPdfFooterImages($html_footer_images, $configuration, $format_pdf, $template, $base_template);

                $pdf->setAutoBottomMargin = 'stretch';
                $pdf->SetHTMLFooter($html_footer_term_condition . $html_footer_images . $html_footer . $html_footer_legend);
                // $pdf->SetHTMLFooter($html_footer_term_condition . $html_footer . $html_footer_legend);

            }
            //$html_footer = $template->pdfFooter();
            //$pdf->SetHTMLFooter($html_footer);
        }
        if ($base_template == 'corvels') {
            $chunks = preg_split('/(<img[^>]+>)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE);

            $base64Image = '';

            foreach ($chunks as $chunk) {
                if (stripos($chunk, 'data:') === 0) {
                    $base64Image = $chunk;
                } else {
                    $pdf->WriteHTML($base64Image . $chunk, HTMLParserMode::HTML_BODY);
                    $base64Image = '';
                }
            }
        } else {

            $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);
        }
        $this->uploadFile($filename, $pdf->output('', 'S'), 'quotation');
    }
    ///pa actualizar txt de commit

    /**
     * Asignar imagenes en footer
     *
     * @param  string $html_footer_images
     * @param  Configuration $configuration
     * @param  string $format_pdf
     * @param  Template $template
     * @param  string $base_template
     * @return void
     */
    public function setPdfFooterImages(&$html_footer_images, $configuration, $format_pdf, $template, $base_template)
    {
        if ($format_pdf === 'a4' && $configuration->applyImagesInPdfFooter() && in_array($base_template, ['default', 'default3'])) {
            $html_footer_images = $template->pdfFooterImages($base_template, $configuration->getBase64PdfFooterImages());
        }
    }


    public function uploadFile($filename, $file_content, $file_type)
    {
        $this->uploadStorage($filename, $file_content, $file_type);
    }

    public function email(Request $request)
    {
        $request->validate([
            'customer_email' => 'required|email'
        ]);

        // $client = Person::find($request->customer_id);
        $quotation = Quotation::find($request->id);
        $customer_email = $request->input('customer_email');

        // $this->reloadPDF($quotation, "a4", $quotation->filename);
        $company = Company::active();
        $email = $customer_email;
        $mailable = new QuotationEmail($company, $quotation);
        $id = (int)$request->id;
        $sendIt = EmailController::SendMail($email, $mailable, $id, 3);
        /*
        Configuration::setConfigSmtpMail();
        $array_email = explode(',', $customer_email);
        if (count($array_email) > 1) {
            foreach ($array_email as $email_to) {
                $email_to = trim($email_to);
                if(!empty($email_to)) {
                    Mail::to($email_to)->send(new QuotationEmail($client, $quotation));
                }
            }
        } else {
            Mail::to($customer_email)->send(new QuotationEmail($client, $quotation));
        }
        */
        return [
            'success' => true
        ];
    }


    public function savePayments($quotation, $payments)
    {

        foreach ($payments as $payment) {

            $record_payment = $quotation->payments()->create($payment);

            if(isset($payment['filename']) && isset($payment['temp_path'])) {
                $new_request = (object) [
                    'filename' => $payment['filename'],
                    'temp_path' => $payment['temp_path']
                ];
                $this->saveFiles($record_payment, $new_request, 'quotations');
            }

            if (isset($payment['payment_destination_id'])) {
                $this->createGlobalPayment($record_payment, $payment);
            }
        }
    }

    public function changed_description(Request $request, $id)
    {
        $description = $request->description;
        $record = Quotation::find($id);
        $record->description = $description;
        $record->save();

        return [
            'success' => true,
            'message' => 'Observación actualizada correctamente'
        ];
    }
    public function changed($id)
    {
        $record = Quotation::find($id);
        $record->changed = true;
        $record->save();

        return [
            'success' => true
        ];
    }

    public function updateStateType($state_type_id, $id)
    {
        $record = Quotation::find($id);
        $record->state_type_id = $state_type_id;
        $record->save();

        return [
            'success' => true,
            'message' => 'Estado actualizado correctamente'
        ];
    }


    public function itemWarehouses($item_id)
    {

        $record = Item::find($item_id);
        // 

        $establishment_id = auth()->user()->establishment_id;
        $warehouse = ModuleWarehouse::where('establishment_id', $establishment_id)->first();

        return collect($record->warehouses)->transform(function ($row) use ($warehouse) {
            return [
                'warehouse_description' => $row->warehouse->description,
                'stock' => $row->stock,
                'warehouse_id' => $row->warehouse_id,
                'checked' => ($row->warehouse_id == $warehouse->id) ? true : false,
            ];
        });
    }
}
