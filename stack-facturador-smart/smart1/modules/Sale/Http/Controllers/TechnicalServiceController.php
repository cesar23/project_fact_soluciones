<?php

namespace Modules\Sale\Http\Controllers;

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
use App\Models\Tenant\PlateNumber;
use App\Models\Tenant\Series;
use App\Models\Tenant\TechnicalServiceItem;
use App\Models\Tenant\User;
use App\Traits\OfflineTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\BusinessTurn\Models\BusinessTurn;
use Modules\Finance\Traits\FinanceTrait;
use Modules\Item\Models\WebPlatform;
use Modules\Sale\Http\Requests\TechnicalServiceRequest;
use Modules\Sale\Http\Resources\TechnicalServiceCollection;
use Modules\Sale\Models\TechnicalService;
use Modules\Sale\Models\TechnicalServiceCar;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use function PHPSTORM_META\map;

/**
 * Class TechnicalServiceController
 *
 * @package Modules\Sale\Http\Controllers
 * @mixin Controller
 */
class TechnicalServiceController extends Controller
{
    use StorageDocument;
    use FinanceTrait;
    use OfflineTrait;

    protected $technical_service;
    protected $technical_service_car;
    protected $company;

    public function index()
    {
        return view('sale::technical-services.index');
    }

    public function columns()
    {
        return [
            'id' => 'Número',
            'customer' => 'Cliente',
            'date_of_issue' => 'Fecha de emisión',
            'plate_number' => 'Placa',
        ];
    }

    public function records(Request $request)
    {
        $records = $this->getRecords($request);

        return new TechnicalServiceCollection($records->paginate(config('tenant.items_per_page')));
    }

    private function getRecords($request)
    {
        if ($request->column == 'customer') {
            $records = TechnicalService::whereHas('person', function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->value}%");
            });
        } else if ($request->column == 'plate_number') {
            $records = TechnicalService::whereHas('technical_service_car', function ($query) use ($request) {
                $query->where('plate_number', 'like', "%{$request->value}%");
            });
        } else {
            $records = TechnicalService::where($request->column, 'like', "%{$request->value}%");
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
                $plate_number = PlateNumber::where('person_id', $row->id)->get();
                return [
                    'id' => $row->id,
                    'description' => $row->number . ' - ' . $row->name,
                    'name' => $row->name,
                    'number' => $row->number,
                    'identity_document_type_id' => $row->identity_document_type_id,
                    'telephone' => $row->telephone,
                    'plate_number' => $plate_number->map(function ($plate_number) {
                        return [
                            'id' => $plate_number->id,
                            'description' => $plate_number->description,
                            'model' => optional($plate_number->model)->description,
                            'brand' => optional($plate_number->brand)->description,
                        ];
                    }),
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
        $web_platforms = $this->table('web_platforms');
        $suppliers = $this->table('suppliers');
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
        $users = User::where('establishment_id', auth()->user()->establishment_id)->whereIn('type', ['seller', 'admin'])->orWhere('id', auth()->user()->id)->get()
            ->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'description' => $row->name,
                ];
            });


        return compact(
            'users',
            'web_platforms',
            'suppliers',
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

                    $plate_number = PlateNumber::where('person_id', $row->id)->first();
                    return [
                        'id' => $row->id,
                        'description' => $row->number . ' - ' . $row->name,
                        'name' => $row->name,
                        'telephone' => $row->telephone,
                        'number' => $row->number,
                        'identity_document_type_id' => $row->identity_document_type_id,
                        'identity_document_type_code' => $row->identity_document_type->code,
                        'plate_number' => $plate_number ? [
                            'id' => $plate_number->id,
                            'description' => $plate_number->description,
                            'model' => optional($plate_number->model)->description,
                            'brand' => optional($plate_number->brand)->description,
                        ] : null,
                    ];
                });
                return $customers;

                break;
            case 'web_platforms':
                $web_platforms = WebPlatform::orderBy('name')->get()->transform(function ($row) {
                    return [
                        'id' => $row->id,
                        'description' => $row->name,
                    ];
                });
                return $web_platforms;
                break;
            case 'suppliers':
                $suppliers = Person::where('type', 'suppliers')->orderBy('name')->get()->transform(function ($row) {
                    return [
                        'id' => $row->id,
                        'description' => $row->number . ' - ' . $row->name,
                    ];
                });
                return $suppliers;
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
        $service = TechnicalService::find($id);
        if ($service == null) $service = new TechnicalService();
        return ['data' => $service->getCollectionData()];
    }

    /**
     * Sube una imagen a una ubicación temporal
     * 
     * @param Request $request
     * @return array
     */
    public function uploadTempImage(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Validar que sea una imagen
            $validator = Validator::make($request->all(), [
                'file' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            ]);

            if ($validator->fails()) {
                return [
                    'success' => false,
                    'message' => 'El archivo debe ser una imagen válida (jpg, png) de máximo 5MB'
                ];
            }

            // Obtener la extensión del archivo original
            $extension = $file->getClientOriginalExtension();

            // Obtener el nombre original y truncarlo si es muy largo (max 50 caracteres)
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $originalName = substr($originalName, 0, 50);

            // Generar nombre único para el archivo usando UUID
            $filename = Str::uuid()->toString() . '_' . $originalName . '.' . $extension;
            $temp_path = 'uploads/temp/' . $filename;

            // Guardar archivo en carpeta temporal
            $file->storeAs('public/temp', $filename);

            return [
                'success' => true,
                'temp_path' => $temp_path,
                'message' => 'Imagen subida correctamente'
            ];
        }

        return [
            'success' => false,
            'message' => 'No se ha seleccionado ninguna imagen'
        ];
    }

    public function changeState($id, $state_technical_service_id)
    {
        $technical_service = TechnicalService::find($id);
        $technical_service->state_id = $state_technical_service_id;
        $technical_service->save();
        return response()->json(['message' => 'Estado actualizado correctamente']);
    }

    // Modificar el método store para procesar las imágenes temporales
    public function store(TechnicalServiceRequest $request)
    {
        DB::connection('tenant')->transaction(function () use ($request) {

            $data = $this->mergeData($request);
            $tc_id = ($request->has('id')) ? $request->id : null;

            $technical_service = TechnicalService::updateOrCreate(['id' => $request->input('id')], $data);
            $all_item = [];
            /* Elimina items del servicio */
            if ($tc_id != null) {
                $items = TechnicalServiceItem::where('technical_services_id', $tc_id)->wherenotin('id', $all_item)->get();
                /** @var TechnicalServiceItem $temp */
                foreach ($items as $temp) {
                    $temp->delete();
                }
                TechnicalServiceCar::where('technical_service_id', $tc_id)->delete();
            }
            foreach ($data['items'] as $row) {
                /** @var TechnicalServiceItem $temp_item */
                $temp_item = $technical_service->items()->create($row);
                $all_item[] = $temp_item->id;
            }
            $vehicle = Functions::valueKeyInArray($data, 'vehicle');
            if ($vehicle != null) {
                $vehicle['technical_service_id'] = $technical_service->id;
                TechnicalServiceCar::create($vehicle);
            }

            // Procesar imágenes temporales
            if ($request->has('temp_images') && is_array($request->temp_images)) {
                foreach ($request->temp_images as $temp_path) {
                    $filename = basename($temp_path);
                    $final_path = 'technical_service_images/' . $technical_service->id . '/' . $filename;

                    // Mover imagen de la carpeta temporal a la definitiva
                    if (Storage::disk('public')->exists('temp/' . $filename)) {
                        // Crear directorio si no existe
                        if (!Storage::disk('public')->exists('technical_service_images/' . $technical_service->id)) {
                            Storage::disk('public')->makeDirectory('technical_service_images/' . $technical_service->id);
                        }

                        // Mover archivo
                        Storage::disk('public')->move('temp/' . $filename, 'technical_service_images/' . $technical_service->id . '/' . $filename);

                        // Guardar registro en base de datos
                        $technical_service->images()->create([
                            'technical_service_id' => $technical_service->id,
                            'image_path' => $final_path
                        ]);
                    }
                }
            }

            $this->technical_service = $technical_service;
            $this->setFilename();
            $this->createPdf($this->technical_service, "a4", $this->technical_service->filename);

            $cash = Cash::query()->where([['user_id', auth()->id()], ['state', true]])->first();
            if ($cash) {
                $cash->cash_documents()->create([
                    'technical_service_id' => $this->technical_service->id
                ]);
            }
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

        $name = ['TS', $this->technical_service->id, date('Ymd')];
        $this->technical_service->filename = join('-', $name);
        $this->technical_service->save();
    }
    function get_value_car($label, $property)
    {
        $quantity = "quantity_" . $property;
        $state = "state_" . $property;
        return [
            "label" => $label,
            "quantity" => $this->technical_service_car->{$quantity},
            "state" => $this->technical_service_car->{$state}
        ];
    }
    public function format_vehicle($id)
    {
        $technical_service = TechnicalService::find($id);
        $technical_service_car = TechnicalServiceCar::where('technical_service_id', $id)->first();
        $this->technical_service = $technical_service;
        $this->technical_service_car = $technical_service_car;
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
            $this->get_value_car("Pico y Pala", "pick_and_shovel"),
            $this->get_value_car("Pertiga", "pole"),
            $this->get_value_car("Circulina", "beacon_light"),
            $this->get_value_car("Pirata", "pirate_light"),
            $this->get_value_car("Neblinero", "fog_light"),
            $this->get_value_car("Tacos", "wheel_chocks"),
            $this->get_value_car("Conos", "traffic_cones"),
            $this->get_value_car("Cable de remolque", "tow_cable"),

            
        ];
        $list2 = [
            $this->get_value_car("Cenicero", "ashtray"),
            $this->get_value_car("Espejo Retrovisor Interno", "internal_rearview_mirror"),
            $this->get_value_car("Auto Radio", "car_radio"),
            $this->get_value_car("Alfombra de protección", "protection_mat"),
            $this->get_value_car("Pisos de jebe", "rubber_floors"),
            $this->get_value_car("Posa Vasos", "cup_holder"),
            $this->get_value_car("Llave de Vehículo", "vehicle_key"),
            $this->get_value_car("Kit antiderrame", "spill_kit"),
            $this->get_value_car("Botiquin", "first_aid_kit"),
            $this->get_value_car("Cable de bateria", "battery_cable"),


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


        $company = Company::active();
        $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
        $pdf = Pdf::loadView('sale::technical-services.format_vehicle', compact(
            "company",
            "list1",
            "establishment",
            "list2",
            "list3",
            "list4",
            "technical_service",
            "technical_service_car"
        ));
        return $pdf->stream('FORMATO.pdf');
    }
    public function createPdf($technical_service = null, $format_pdf = null, $filename = null)
    {

        ini_set("pcre.backtrack_limit", "5000000");
        $template = new Template();
        $pdf = new Mpdf();

        $document = ($technical_service != null) ? $technical_service : $this->technical_service;
        $company = ($this->company != null) ? $this->company : Company::active();
        $filename = ($filename != null) ? $filename : $this->technical_service->filename;

        $configuration = Configuration::first();

        $base_template = $configuration->formats; //config('tenant.pdf_template');

        $html = $template->pdf($base_template, "technical_service", $company, $document, $format_pdf);

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


        $this->uploadFile($filename, $pdf->output('', 'S'), 'technical_service');
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

        $technical_service = TechnicalService::find($id);

        if (!$technical_service) throw new Exception("El código es inválido, no se encontró el servicio técnico relacionado");

        $this->reloadPDF($technical_service, $format, $technical_service->filename);
        $temp = tempnam(sys_get_temp_dir(), 'technical_service');

        file_put_contents($temp, $this->getStorage($technical_service->filename, 'technical_service'));

        /*
            $headers = [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$technical_service->filename.'"'
            ];
            */

        return response()->file($temp, $this->generalPdfResponseFileHeaders($technical_service->filename));
    }

    private function reloadPDF($technical_service, $format, $filename)
    {
        $this->createPdf($technical_service, $format, $filename);
    }

    public function destroy($id)
    {

        $record = TechnicalService::findOrFail($id);

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

    /**
     * Elimina una imagen de un servicio técnico
     * 
     * @param  int $id ID de la imagen
     * @return array
     */
    public function removeImage($id)
    {
        try {
            $image = \Modules\Sale\Models\TechnicalServiceImage::findOrFail($id);

            // Eliminar archivo físico
            if (Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }

            // Verificar si existe el directorio y está vacío después de eliminar la imagen
            $directoryPath = dirname($image->image_path);
            if (Storage::disk('public')->exists($directoryPath)) {
                $filesInDirectory = Storage::disk('public')->files($directoryPath);
                if (count($filesInDirectory) === 0) {
                    // Si no hay más archivos en el directorio, lo eliminamos
                    Storage::disk('public')->deleteDirectory($directoryPath);
                }
            }

            // Eliminar registro
            $image->delete();

            return [
                'success' => true,
                'message' => 'Imagen eliminada correctamente'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar la imagen: ' . $e->getMessage()
            ];
        }
    }
}
