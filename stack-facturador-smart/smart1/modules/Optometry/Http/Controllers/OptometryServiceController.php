<?php

namespace Modules\Optometry\Http\Controllers;

use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\CoreFacturalo\Requests\Inputs\Common\PersonInput;
use App\CoreFacturalo\Requests\Inputs\Functions;
use App\CoreFacturalo\Template;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Cash;
use App\Models\Tenant\Catalogs\AffectationIgvType;
use App\Models\Tenant\Catalogs\ChargeDiscountType;
use App\Models\Tenant\Catalogs\CurrencyType;
use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\Catalogs\NoteCreditType;
use App\Models\Tenant\Catalogs\NoteDebitType;
use App\Models\Tenant\Catalogs\OperationType;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\PaymentCondition;
use App\Models\Tenant\Person;
use App\Models\Tenant\Series;
use App\Models\Tenant\User;
use App\Models\Tenant\Warehouse;
use App\Traits\OfflineTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\BusinessTurn\Models\BusinessTurn;
use Modules\Finance\Traits\FinanceTrait;
use Modules\Optometry\Http\Requests\OptometryServiceRequest;
use Modules\Optometry\Http\Resources\OptometryServiceCollection;
use Modules\Optometry\Models\OptometryService;
use Modules\Optometry\Models\OptometryServiceData;
use Modules\Optometry\Models\OptometryServiceItem;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;

use function PHPSTORM_META\map;

/**
 * Class OptometryServiceController
 *
 * @package Modules\Sale\Http\Controllers
 * @mixin Controller
 */
class OptometryServiceController extends Controller
{
    use StorageDocument;
    use FinanceTrait;
    use OfflineTrait;

    protected $optometry_service;
    protected $optometry_service_car;
    protected $company;

    public function index()
    {
        return view('optometry::optometry-services.index');
    }

    public function columns()
    {
        return [
            'id' => 'Número',
            'customer' => 'Cliente',
            'date_of_issue' => 'Fecha de emisión',
        ];
    }

    public function records(Request $request)
    {
        $records = $this->getRecords($request);

        return new OptometryServiceCollection($records->paginate(config('tenant.items_per_page')));
    }

    private function getRecords($request)
    {
        if ($request->column == 'customer') {
            $records = OptometryService::whereHas('person', function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->value}%");
            });
        } else {
            $records = OptometryService::where($request->column, 'like', "%{$request->value}%");
        }

        return $records->whereTypeUser()->latest();
    }

    public function searchCustomers(Request $request)
    {
        $customers = Person::where('number', 'like', "%{$request->input}%")
            ->orWhere('name', 'like', "%{$request->input}%")
            ->whereType('customers')->orderBy('name')
            ->whereIsEnabled()
            ->get()->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'description' => $row->number . ' - ' . $row->name,
                    'name' => $row->name,
                    'number' => $row->number,
                    'identity_document_type_id' => $row->identity_document_type_id,
                ];
            });

        return compact('customers');
    }

    public function tables()
    {
        $customers = $this->table('customers');
        // $prepayment_documents = $this->table('prepayment_documents');
        $establishments = Establishment::where('id', auth()->user()->establishment_id)->get(); // Establishment::all();
        $series = collect(Series::all())->transform(function ($row) {
            return [
                'id' => $row->id,
                'contingency' => (bool)$row->contingency,
                'document_type_id' => $row->document_type_id,
                'establishment_id' => $row->establishment_id,
                'number' => $row->number
            ];
        });
        $document_types_invoice = DocumentType::whereIn('id', ['01', '03'])->where('active', true)->get();
        $document_types_note = DocumentType::whereIn('id', ['07', '08'])->get();
        $note_credit_types = NoteCreditType::whereActive()->orderByDescription()->get();
        $note_debit_types = NoteDebitType::whereActive()->orderByDescription()->get();
        $currency_types = CurrencyType::whereActive()->get();
        $operation_types = OperationType::whereActive()->get();
        $discount_types = ChargeDiscountType::whereType('discount')->whereLevel('item')->get();
        $charge_types = ChargeDiscountType::whereType('charge')->whereLevel('item')->get();
        $company = Company::active();
        $document_type_03_filter = config('tenant.document_type_03_filter');
        $user = auth()->user()->type;
        $sellers = User::where('establishment_id', auth()->user()->establishment_id)->whereIn('type', ['seller', 'admin'])->orWhere('id', auth()->user()->id)->get();
        $payment_method_types = $this->table('payment_method_types');
        $business_turns = BusinessTurn::where('active', true)->get();
        $enabled_discount_global = config('tenant.enabled_discount_global');
        $is_client = $this->getIsClient();
        $select_first_document_type_03 = config('tenant.select_first_document_type_03');
        $payment_conditions = PaymentCondition::all();

        $document_types_guide = DocumentType::whereIn('id', ['09', '31'])->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'active' => (bool)$row->active,
                'short' => $row->short,
                'description' => ucfirst(mb_strtolower(str_replace('REMITENTE ELECTRÓNICA', 'REMITENTE', $row->description))),
            ];
        });
        // $cat_payment_method_types = CatPaymentMethodType::whereActive()->get();
        // $detraction_types = DetractionType::whereActive()->get();

        //        return compact('customers', 'establishments', 'series', 'document_types_invoice', 'document_types_note',
        //                       'note_credit_types', 'note_debit_types', 'currency_types', 'operation_types',
        //                       'discount_types', 'charge_types', 'company', 'document_type_03_filter',
        //                       'document_types_guide');

        // return compact('customers', 'establishments', 'series', 'document_types_invoice', 'document_types_note',
        //                'note_credit_types', 'note_debit_types', 'currency_types', 'operation_types',
        //                'discount_types', 'charge_types', 'company', 'document_type_03_filter');

        $payment_destinations = $this->getPaymentDestinations();
        $document_id = auth()->user()->document_id;
        $series_id = auth()->user()->series_id;
        $affectation_igv_types = AffectationIgvType::whereActive()->get();

        return compact(
            'document_id',
            'series_id',
            'customers',
            'establishments',
            'series',
            'document_types_invoice',
            'document_types_note',
            'note_credit_types',
            'note_debit_types',
            'currency_types',
            'operation_types',
            'discount_types',
            'charge_types',
            'company',
            'document_type_03_filter',
            'document_types_guide',
            'user',
            'sellers',
            'payment_method_types',
            'enabled_discount_global',
            'business_turns',
            'is_client',
            'select_first_document_type_03',
            'payment_destinations',
            'payment_conditions',
            'affectation_igv_types'
        );
    }

    public function table($table)
    {
        switch ($table) {
            case 'customers':

                $customers = Person::whereType('customers')->whereIsEnabled()->orderBy('name')->take(20)->get()->transform(function ($row) {
                    return [
                        'id' => $row->id,
                        'description' => $row->number . ' - ' . $row->name,
                        'name' => $row->name,
                        'number' => $row->number,
                        'identity_document_type_id' => $row->identity_document_type_id,
                        'identity_document_type_code' => $row->identity_document_type->code
                    ];
                });
                return $customers;

                break;
            default:
                return [];

                break;
        }
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function record($id = 0)
    {
        $service = OptometryService::find($id);
        if ($service == null) $service = new OptometryService();
        return ['data' => $service->getCollectionData()];
    }

    public function store(OptometryServiceRequest $request)
    {

        DB::connection('tenant')->transaction(function () use ($request) {

            $data = $this->mergeData($request);
            $tc_id = ($request->has('id')) ? $request->id : null;

            $optometry_service = OptometryService::updateOrCreate(['id' => $request->input('id')], $data);
            $all_item = [];
            /* Elimina items del servicio */
            if ($tc_id != null) {
                $items = OptometryServiceItem::where('optometry_services_id', $tc_id)->wherenotin('id', $all_item)->get();
                /** @var OptometryServiceItem $temp */
                foreach ($items as $temp) {
                    $temp->delete();
                }
            }
            foreach ($data['items'] as $row) {
                /** @var OptometryServiceItem $temp_item */
                if(!isset($row['warehouse_id'])){
                    $row['warehouse_id'] = Warehouse::where('establishment_id', auth()->user()->establishment_id)->first()->id;
                }
                $temp_item = $optometry_service->items()->create($row);
                $all_item[] = $temp_item->id;
            }
            
            $general = Functions::valueKeyInArray($data, 'general');
            if ($general) {
                $general['optometry_service_id'] = $optometry_service->id;
                OptometryServiceData::updateOrCreate(['optometry_service_id' => $optometry_service->id], $general);
            }
            $this->optometry_service = $optometry_service;
            $this->setFilename();
            $this->createPdf($this->optometry_service, "a4", $this->optometry_service->filename);

            $cash = Cash::query()->where([['user_id', auth()->id()], ['state', true]])->first();
            $cash->cash_documents()->create([
                'optometry_service_id' => $this->optometry_service->id
            ]);
        });

        return [
            'success' => true,
            'message' => $request->id ? 'Servicio técnico actualizado' : 'Servicio técnico registrado'
        ];
    }

    public function mergeData($inputs)
    {

        $this->company = Company::active();

        $values = [
            'user_id' => auth()->id(),
            'customer' => PersonInput::set($inputs['customer_id']),
            'soap_type_id' => $this->company->soap_type_id,
        ];

        $inputs->merge($values);

        return $inputs->all();
    }

    private function setFilename()
    {

        $name = ['TS', $this->optometry_service->id, date('Ymd')];
        $this->optometry_service->filename = join('-', $name);
        $this->optometry_service->save();
    }
    function get_value_car($label, $property)
    {
        $quantity = "quantity_" . $property;
        $state = "state_" . $property;
        return [
            "label" => $label,
            "quantity" => $this->optometry_service_car->{$quantity},
            "state" => $this->optometry_service_car->{$state}
        ];
    }
    public function format_vehicle($id)
    {
        $optometry_service = OptometryService::find($id);
        $this->optometry_service = $optometry_service;
        $list1 = [
            $this->get_value_car("Faros Delanteros", "front_lights"),
            $this->get_value_car("Luces Direccionales Delanteros", "directional_lights_front"),
            $this->get_value_car("Luces Direccionales Posteriores", "directional_lights_back"),
            $this->get_value_car("Luces de Peligro", "hazard_lights"),
            $this->get_value_car("Brazo y Plumilla Limpia Parabrizas", "wiper_washer_arm"),
            $this->get_value_car("Tapa Gasolina", "gasoil_cap"),
            $this->get_value_car("Antena Radio", "radio_antenna"),
            $this->get_value_car("Espejos Laterales", "side_mirrors"),
            $this->get_value_car("Manijas de Prueba ", "test_handles"),
            $this->get_value_car("Alarma", "alarm"),
            $this->get_value_car("Escarpines", "booties"),
            $this->get_value_car("Llanta y Aro de Respuesto", "spare_tire"),
            $this->get_value_car("Dado Segruo de Rueda", "wheel_nut"),
            $this->get_value_car("Copa de Aro", "wheel_cup"),
        ];
        $list2 = [
            $this->get_value_car("Cenicero", "ashtray"),
            $this->get_value_car("Espejo Retrovisor Interno", "internal_rearview_mirror"),
            $this->get_value_car("Auto Radio", "car_radio"),
            $this->get_value_car("Alfombra de protección", "protection_mat"),
            $this->get_value_car("Pisos de jebe", "rubber_floors"),
            $this->get_value_car("Posa Vasos", "cup_holder"),
            $this->get_value_car("Llave de Vehículo", "vehicle_key"),


        ];
        $list3 = [
            $this->get_value_car("Extintor", "extinguisher"),
            $this->get_value_car("Gata y palanca", "jack_lever"),
            $this->get_value_car("Estuche de Herramientas", "toolkit"),
        ];

        $list4 = [
            $this->get_value_car("Tarjeta de Propiedad", "property_card"),
            $this->get_value_car("Cuaderno de bitácora", "logbook"),
            $this->get_value_car("Manual del Propietario", "owner_manual"),
            $this->get_value_car("Porta Documentos", "document_holder"),
        ];

        // front_lights
        // directional_lights_front
        // directional_lights_back
        // hazard_lights
        // wiper_washer_arm
        // gasoil_cap
        // radio_antenna
        // side_mirrors
        // test_handles
        // alarm
        // booties
        // spare_tire
        // wheel_nut
        // wheel_cup
        // ashtray
        // internal_rearview_mirror
        // car_radio
        // protection_mat
        // rubber_floors
        // cup_holder
        // vehicle_key
        // extinguisher
        // jack_lever
        // toolkit
        // property_card
        // logbook
        // owner_manual
        // document_holder
        // auth_drive
        // move_on
        // no_value_things
        // cost_for_days
        // gasoline_level
        // observations
        // created_at
        // updated_at



        $company = Company::active();
        $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
        $pdf = Pdf::loadView('optometry::optometry-services.format_vehicle', compact(
            "company",
            "list1",
            "establishment",
            "list2",
            "list3",
            "list4",
            "optometry_service",
            "optometry_service_car"
        ));
        return $pdf->stream('FORMATO.pdf');
    }
    public function createPdf($optometry_service = null, $format_pdf = null, $filename = null)
    {

        ini_set("pcre.backtrack_limit", "5000000");
        $template = new Template();
        $pdf = new Mpdf();

        $document = ($optometry_service != null) ? $optometry_service : $this->optometry_service;
        $company = ($this->company != null) ? $this->company : Company::active();
        $filename = ($filename != null) ? $filename : $this->optometry_service->filename;

        $configuration = Configuration::first();

        $base_template = $configuration->formats; //config('tenant.pdf_template');

        $html = $template->pdf($base_template, "optometry_service", $company, $document, $format_pdf);

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

            $pdf = new Mpdf($default);
        }

        $path_css = app_path('CoreFacturalo' . DIRECTORY_SEPARATOR . 'Templates' .
            DIRECTORY_SEPARATOR . 'pdf' .
            DIRECTORY_SEPARATOR . $base_template .
            DIRECTORY_SEPARATOR . 'style.css');

        $stylesheet = file_get_contents($path_css);

        $pdf->WriteHTML($stylesheet, HTMLParserMode::HEADER_CSS);
        $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);


        $this->uploadFile($filename, $pdf->output('', 'S'), 'optometry_service');
    }

    public function uploadFile($filename, $file_content, $file_type)
    {
        $this->uploadStorage($filename, $file_content, $file_type);
    }

    public function searchCustomerById($id)
    {
        return $this->searchClientById($id);
    }

    public function toPrint($id, $format)
    {

        $optometry_service = OptometryService::find($id);

        if (!$optometry_service) throw new Exception("El código es inválido, no se encontró el servicio técnico relacionado");

        $this->reloadPDF($optometry_service, $format, $optometry_service->filename);
        $temp = tempnam(sys_get_temp_dir(), 'optometry_service');

        file_put_contents($temp, $this->getStorage($optometry_service->filename, 'optometry_service'));

        /*
            $headers = [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$optometry_service->filename.'"'
            ];
            */

        return response()->file($temp, $this->generalPdfResponseFileHeaders($optometry_service->filename));
    }

    private function reloadPDF($optometry_service, $format, $filename)
    {
        $this->createPdf($optometry_service, $format, $filename);
    }

    public function destroy($id)
    {

        $record = OptometryService::findOrFail($id);

        if ($record->payments()->count() > 0) {
            return [
                'success' => false,
                'message' => 'El servicio técnico tiene pagos asociados, debe eliminarlos previamente'
            ];
        }
        $record->items()->delete();
        $record->delete();

        return [
            'success' => true,
            'message' => 'Servicio técnico eliminado con éxito'
        ];
    }
}
