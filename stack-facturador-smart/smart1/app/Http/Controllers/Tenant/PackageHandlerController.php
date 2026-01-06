<?php

namespace App\Http\Controllers\Tenant;

use App\CoreFacturalo\HelperFacturalo;
use App\CoreFacturalo\Requests\Inputs\Common\EstablishmentInput;
use App\CoreFacturalo\Requests\Inputs\Common\PersonInput;
use App\CoreFacturalo\Requests\Inputs\Functions;
use App\CoreFacturalo\Template;
use App\Exports\PackageHandlerExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SearchItemController;
use App\Http\Resources\Tenant\PackageHandlerCollection;
use App\Http\Resources\Tenant\PackageHandlerResource;
use App\Models\Tenant\BankAccount;
use App\Models\Tenant\Cash;
use App\Models\Tenant\Catalogs\ChargeDiscountType;
use App\Models\Tenant\Catalogs\CurrencyType;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Item;
use App\Models\Tenant\PackageHandler;
use App\Models\Tenant\PackageHandlerItem;
use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\Person;
use App\Models\Tenant\Series;
use App\Models\Tenant\User;
use App\Models\Tenant\Warehouse;
use App\Traits\PrinterTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Dispatch\Models\Driver;
use Modules\Document\Models\SeriesConfiguration;
use Modules\Document\Traits\SearchTrait;
use Modules\Finance\Traits\FilePaymentTrait;
use Modules\Finance\Traits\FinanceTrait;
use Illuminate\Support\Str;
use Modules\Inventory\Traits\InventoryTrait;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;

class PackageHandlerController extends Controller
{
    use FinanceTrait;
    use SearchTrait;
    use FilePaymentTrait;
    use InventoryTrait;
    use PrinterTrait;
    protected $package_handler;
    protected $company;
    protected $document;
    protected $apply_change;
    protected $configuration;
    public function index()
    {

        return view('tenant.package_handler.index');
    }
    public function create($id = null)
    {
        $cashid = null;
        if ($id != null) {
            $salenote = PackageHandler::find($id);
            $cash_open = Cash::where('user_id', $salenote->user_id)->where('state', true)->first();
            if ($cash_open != null) {
                $cashid = $cash_open->id;
            }
        } else {
            $cash_open = Cash::where('user_id', User::getUserCashId())->where('state', true)->first();
            if ($cash_open != null) {
                $cashid = $cash_open->id;
            }
        }
        return view('tenant.package_handler.form', compact('id', 'cashid'));
    }
    public function searchCustomerById($id)
    {
        return $this->searchClientById($id);
    }
    public function excelPackages(Request $request){

        $column = $request->input('column');
        $value = $request->input('value');
        $records = PackageHandler::query();

        if ($column && $value) {

            switch ($column) {
                case 'sender_id':

                    $records = $records->whereHas('sender', function ($query) use ($value) {
                        $query->where('name', 'like', "%{$value}%")
                            ->orWhere('number', 'like', "%{$value}%");;
                    });
                    break;
                case 'issuer_id':
                    $records = $records->whereHas('issuer', function ($query) use ($value) {
                        $query->where('name', 'like', "%{$value}%")
                            ->orWhere('number', 'like', "%{$value}%");;
                    });
                    break;
                case 'driver_id':
                    $records = $records->whereHas('driver', function ($query) use ($value) {
                        $query->where('name', 'like', "%{$value}%")
                            ->orWhere('number', 'like', "%{$value}%");;
                    });
                    break;
                default:
                    $records = $records->where($column, 'like', "%{$value}%");
                    break;
            }
        }
        $records = $records->get();
        $company = Company::active();
        return (new PackageHandlerExport)
        ->records($records)
        ->company($company)
        ->download('Reporte_de_Tickets_Encomienda_' . Carbon::now() . '.xlsx');
    }
    public function table($table)
    {
        switch ($table) {
            case 'customers':

                $customers = Person::whereType('customers')
                    ->whereIsEnabled()->orderBy('name')->take(20)->get()->transform(function (Person $row) {
                        return [
                            'id' => $row->id,
                            'description' => $row->number . ' - ' . $row->name,
                            'seller' => $row->seller,
                            'seller_id' => $row->seller_id,
                            'name' => $row->name,
                            'number' => $row->number,
                            'is_driver' => $row->is_driver,
                            'identity_document_type_id' => $row->identity_document_type_id,
                            'identity_document_type_code' => $row->identity_document_type->code
                        ];
                    });
                    $configuration = Configuration::first();
                    if ($configuration->package_handlers) {
                        $existingDrivers = $customers->filter(function ($row) {
                            return $row["is_driver"] == true;
                        });
          
    
                        if ($existingDrivers->count() < 10) {
                          
                            $additionalDrivers = Person::whereType('customers')
                                ->whereIsEnabled()
                                ->where('is_driver', true)
                                ->orderBy('name')
                                ->take(10 - $existingDrivers->count())
                                ->get()->transform(function (Person $row) {
                                    return [
                                        'id' => $row->id,
                                        'description' => $row->number . ' - ' . $row->name,
                                        'seller' => $row->seller,
                                        'seller_id' => $row->seller_id,
                                        'name' => $row->name,
                                        'number' => $row->number,
                                        'is_driver' => $row->is_driver,
                                        'identity_document_type_id' => $row->identity_document_type_id,
                                        'identity_document_type_code' => $row->identity_document_type->code
                                    ];
                                });
                            $customers = collect($customers->concat($additionalDrivers)->unique('id'))->toArray();
                            
                        } else {
                            $customers = $customers;
                        }
                    }
                return $customers;

                break;

            case 'items':

                return SearchItemController::getItemsToSaleNote();
                $establishment_id = auth()->user()->establishment_id;
                $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();
                // $warehouse_id = ($warehouse) ? $warehouse->id:null;

                $items_u = Item::whereWarehouse()->whereIsActive()->whereNotIsSet()->orderBy('description')->take(20)->get();

                $items_s = Item::where('unit_type_id', 'ZZ')->whereIsActive()->orderBy('description')->take(10)->get();

                $items = $items_u->merge($items_s);

                return collect($items)->transform(function ($row) use ($warehouse) {
                    $warehouse_id = ($warehouse) ? $warehouse->id : null;
                    /** @var Item $row */
                    return $row->getDataToItemModal($warehouse);
                    $detail = $this->getFullDescription($row, $warehouse);
                    return [
                        'id' => $row->id,
                        'full_description' => $detail['full_description'],
                        'brand' => $detail['brand'],
                        'category' => $detail['category'],
                        'stock' => $detail['stock'],
                        'description' => $row->description,
                        'currency_type_id' => $row->currency_type_id,
                        'currency_type_symbol' => $row->currency_type->symbol,
                        'sale_unit_price' => round($row->sale_unit_price, 2),
                        'purchase_unit_price' => $row->purchase_unit_price,
                        'unit_type_id' => $row->unit_type_id,
                        'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                        'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                        'has_igv' => (bool) $row->has_igv,
                        'lots_enabled' => (bool) $row->lots_enabled,
                        'series_enabled' => (bool) $row->series_enabled,
                        'is_set' => (bool) $row->is_set,
                        'warehouses' => collect($row->warehouses)->transform(function ($row) use ($warehouse_id) {
                            return [
                                'warehouse_id' => $row->warehouse->id,
                                'warehouse_description' => $row->warehouse->description,
                                'stock' => $row->stock,
                                'checked' => ($row->warehouse_id == $warehouse_id) ? true : false,
                            ];
                        }),
                        'item_unit_types' => $row->item_unit_types,
                        'lots' => [],

                        'lots_group' => collect($row->lots_group)->transform(function ($row) {
                            return [
                                'id'  => $row->id,
                                'code' => $row->code,
                                'quantity' => $row->quantity,
                                'date_of_due' => $row->date_of_due,
                                'checked'  => false
                            ];
                        }),
                        'lot_code' => $row->lot_code,
                        'date_of_due' => $row->date_of_due
                    ];
                });


                break;
            default:

                return [];

                break;
        }
    }
    public function ticket($id)
    {
        $data = PackageHandler::findOrFail($id);
        // dd($data["documents"]->count());
        $company = Company::active();
        $establishment = $data->establishment;
        $total_ =  350;
        $pdf = Pdf::loadView('tenant.package_handler.ticket', compact("data", "company", "establishment"))
            ->setPaper(array(0, 0, 180, $total_), 'portrait');
        $filename = "Ticket de encomienda";

        return $pdf->stream($filename . '.pdf');
    }
    public function store(Request $request)
    {
        return $this->storeWithData($request->all());
    }

    public function mergeData($inputs)
    {

        $this->company = Company::active();

        $cash_id = Functions::valueKeyInArray($inputs, 'cash_id');
        if ($cash_id == null) {
            $cash_id = optional(Cash::where([['user_id', User::getUserCashId()], ['state', true]]))->first()->id;
        }
        // Para matricula, se busca el hijo en atributos
        $attributes = $inputs['attributes'] ?? [];
        $type_period = isset($inputs['type_period']) ? $inputs['type_period'] : null;
        $quantity_period = isset($inputs['quantity_period']) ? $inputs['quantity_period'] : null;
        $d_of_issue = new Carbon($inputs['date_of_issue']);
        $automatic_date_of_issue = null;

        if ($type_period && $quantity_period > 0) {

            $add_period_date = ($type_period == 'month') ? $d_of_issue->addMonths($quantity_period) : $d_of_issue->addYears($quantity_period);
            $automatic_date_of_issue = $add_period_date->format('Y-m-d');
        }

        if (key_exists('series_id', $inputs)) {
            $series = Series::query()->find($inputs['series_id'])->number;
        } else {
            $series = $inputs['series'];
        }

        $number = null;

        if ($inputs['id']) {
            $number = $inputs['number'];
        } else {

            if (PackageHandler::count() == 0) {
                $series_configuration = SeriesConfiguration::where([['document_type_id', "80"], ['series', $series]])->first();
                $number = $series_configuration->number ?? 1;
            } else {
                $document = PackageHandler::query()
                    ->select('number')
                    ->where('series', $series)
                    ->orderByRaw('CAST(number AS UNSIGNED) DESC')
                    ->first();

                $number = ($document) ? $document->number + 1 : 1;
            }
        }
        $seller_id = isset($inputs['seller_id']) ? (int)$inputs['seller_id'] : 0;

        $additional_information = isset($inputs['additional_information']) ? $inputs['additional_information'] : '';


        $values = [
            'additional_information' => $additional_information,
            'automatic_date_of_issue' => $automatic_date_of_issue,
            'user_id' => $seller_id == 0 ? auth()->user()->id : $seller_id,
            'seller_id' => $seller_id,
            'external_id' => Str::uuid()->toString(),
            'sender' => PersonInput::set($inputs['sender_id']),
            'issuer' => PersonInput::set($inputs['issuer_id']),
            'driver' => PersonInput::set($inputs['driver_id']),
            // 'sender' => "[]",
            // 'issuer' => "[]",
            // 'driver' => "[]",
            $establishment_id = auth()->user()->establishment_id,
            'establishment' => EstablishmentInput::set($establishment_id),
            'soap_type_id' => $this->company->soap_type_id,
            'state_type_id' => '01',
            'series' => $series,
            'number' => $number,
            'cash_id' => $cash_id,

        ];





        unset($inputs['series_id']);
        $inputs = array_merge($inputs, $values);
        return $inputs;
    }
    public function storeWithData($inputs)
    {

        DB::connection('tenant')->beginTransaction();
        try {
            if (!isset($inputs['id'])) {
                $inputs['id'] = false;
            }
            $data = $this->mergeData($inputs);
            $this->package_handler =  PackageHandler::query()->updateOrCreate(['id' => $inputs['id']], $data);
            if(isset($inputs['driver_id'])){
                $license_plate = $inputs['driver_id'];
                if($license_plate != null){
                    $license_plate = Person::find($license_plate);
                    $license_plate = $license_plate->barcode;
                }else{
                    $license_plate = '';
                }
                $this->package_handler->license_plate = $license_plate;
              

            }
            $this->deleteAllPayments($this->package_handler->payments);

            //se elimina los items para activar el evento deleted del modelo y controlar el inventario
            $this->deleteAllItems($this->package_handler->items);

            // $configuration = Configuration::first();
            foreach ($data['items'] as $row) {

                // $item_id = isset($row['id']) ? $row['id'] : null;
                $item_id = isset($row['record_id']) ? $row['record_id'] : null;
                $package_handler_item = PackageHandlerItem::query()->firstOrNew(['id' => $item_id]);

                // $this->setIdLoteSelectedToItem($row);
                $package_handler_item->fill($row);
                $package_handler_item->package_handler_id = $this->package_handler->id;
                // $package_handler_item->attributes =  null;
                $package_handler_item->save();

                // control de lotes


            }
            //pagos
            $this->savePayments($this->package_handler, $data['payments'], $data['cash_id']);

            // $this->createPdf($this->package_handler, "a4", $this->package_handler->filename);
            // $this->regularizePayments($data['payments']);

            DB::connection('tenant')->commit();

            return [
                'success' => true,
                'data' => [
                    'id' => $this->package_handler->id,
                    'number_full' => $this->package_handler->number_full,
                ],
            ];
        } catch (Exception $e) {
            $this->generalWriteErrorLog($e);

            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'track_line' => $e->getTrace(),
                'message' => $e->getMessage(),
            ];
        }
    }


    public function savePayments($package_handler, $payments, $cash_id = null)
    {

        $total = $package_handler->total;
        $balance = $total - collect($payments)->sum('payment');

        $search_cash = ($balance < 0) ? collect($payments)->firstWhere('payment_method_type_id', '01') : null;

        $this->apply_change = false;

        if ($balance < 0 && $search_cash) {

            $payments = collect($payments)->map(function ($row) use ($balance) {

                $change = null;
                $payment = $row['payment'];

                if ($row['payment_method_type_id'] == '01' && !$this->apply_change) {

                    $change = abs($balance);
                    $payment = $row['payment'] - abs($balance);
                    $this->apply_change = true;
                }

                return [
                    "id" => null,
                    "document_id" => null,
                    "sale_note_id" => null,
                    "date_of_payment" => $row['date_of_payment'],
                    "payment_method_type_id" => $row['payment_method_type_id'],
                    "reference" => $row['reference'],
                    "payment_destination_id" => isset($row['payment_destination_id']) ? $row['payment_destination_id'] : null,
                    "payment_filename" => isset($row['payment_filename']) ? $row['payment_filename'] : null,
                    "change" => $change,
                    "payment" => $payment
                ];
            });
        }



        foreach ($payments as $row) {

            if ($balance < 0 && !$this->apply_change) {
                $row['change'] = abs($balance);
                $row['payment'] = $row['payment'] - abs($balance);
                $this->apply_change = true;
            }

            $record_payment = $package_handler->payments()->create($row);

            if (isset($row['payment_destination_id'])) {
                $this->createGlobalPayment($record_payment, $row);
            }

            if (isset($row['payment_filename'])) {
                $record_payment->payment_file()->create([
                    'filename' => $row['payment_filename']
                ]);
            }

            // para carga de voucher
            $this->saveFilesFromPayments($row, $record_payment, 'sale_notes');
        }
    }
    public function tables($user_id = null)
    {
        $user = new User();
        if (Auth::user()) {
            $user = Auth::user();
        }
        $establishment_id =  $user->establishment_id;
        $userId =  $user->id;
        $customers = $this->table('customers');
        $establishments = Establishment::where('id', auth()->user()->establishment_id)->get();
        $currency_types = CurrencyType::whereActive()->get();
        $discount_types = ChargeDiscountType::whereType('discount')->whereLevel('item')->get();
        $drivers = Driver::all();
        $charge_types = ChargeDiscountType::whereType('charge')->whereLevel('item')->get();
        $global_charge_types = ChargeDiscountType::whereIn('id', ['50'])->get();
        $company = Company::active();
        $payment_method_types = PaymentMethodType::where('description', 'not like', '%Factura%')->get();
        $series = collect(Series::all())->transform(function ($row) {
            return [
                'id' => $row->id,
                'contingency' => (bool) $row->contingency,
                'document_type_id' => $row->document_type_id,
                'establishment_id' => $row->establishment_id,
                'number' => $row->number
            ];
        });
        $payment_destinations = $this->getPaymentDestinations();
        $configuration = Configuration::select('destination_sale', 'ticket_58')->first();
        $sellers = User::getSellersToNvCpe($establishment_id, $userId);
        $global_discount_types = ChargeDiscountType::getGlobalDiscounts();

        return compact(
            'drivers',
            'customers',
            'establishments',
            'currency_types',
            'discount_types',
            'configuration',
            'charge_types',
            'company',
            'payment_method_types',
            'series',
            'payment_destinations',
            'sellers',
            'global_charge_types',
            'global_discount_types'
        );
    }
    public function records(Request $request)
    {
        $column = $request->input('column');
        $value = $request->input('value');
        $records = PackageHandler::query();

        if ($column && $value) {

            switch ($column) {
                case 'sender_id':

                    $records = $records->whereHas('sender', function ($query) use ($value) {
                        $query->where('name', 'like', "%{$value}%")
                            ->orWhere('number', 'like', "%{$value}%");;
                    });
                    break;
                case 'issuer_id':
                    $records = $records->whereHas('issuer', function ($query) use ($value) {
                        $query->where('name', 'like', "%{$value}%")
                            ->orWhere('number', 'like', "%{$value}%");;
                    });
                    break;
                case 'driver_id':
                    $records = $records->whereHas('driver', function ($query) use ($value) {
                        $query->where('name', 'like', "%{$value}%")
                            ->orWhere('number', 'like', "%{$value}%");;
                    });
                    break;
                default:
                    $records = $records->where($column, 'like', "%{$value}%");
                    break;
            }
        }
        return new PackageHandlerCollection($records->paginate(config('tenant.items_per_page')));
    }
    public function record($id)
    {
        $record = PackageHandler::findOrFail($id);
        return new PackageHandlerResource($record);
    }
    public function columns()
    {
        return [
            'number' => 'Número',
            'sender_id' => 'Remitente',
            'issuer_id' => 'Destinatario',
            'arrival' => 'Punto de llegada',
            'departure' => 'Punto de salida',
            'date_of_issue' => 'Fecha de emisión',
            'driver_id' => 'Conductor',
        ];
    }
}
