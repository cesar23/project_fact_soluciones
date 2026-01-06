<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Requests\Tenant\ConfigurationRequest;
use App\Http\Resources\Tenant\ConfigurationResource;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Item;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Tenant\Catalogs\{
    AffectationIgvType,
    ChargeDiscountType
};
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use App\CoreFacturalo\Template;
use App\Models\System\User;
use App\Models\Tenant\AppConfigurationTaxo;
use App\Models\Tenant\AppConfigurationTaxoRole;
use App\Models\Tenant\Company;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\FormatTemplate;
use Modules\LevelAccess\Models\ModuleLevel;
use App\Models\Tenant\Skin;
use App\Models\Tenant\Warehouse;
use App\Models\Tenant\CustomColorTheme;
use App\Traits\CacheTrait;
use Illuminate\Support\Facades\Schema;
use Modules\Finance\Helpers\UploadFileHelper;


class ConfigurationController extends Controller
{
    use CacheTrait;
    public function updateWithIgvProductReportCash(Request $request)
    {
        $configuration = Configuration::first();
        $configuration->with_igv_product_report_cash = $request->with_igv_product_report_cash;
        $configuration->save();
        self::clearCache();
        return response()->json(['success' => true, 'message' => 'Configuración actualizada correctamente']);
    }

    public function create()
    {
        return view('tenant.configurations.form');
    }
    private function set_default_configuration_type($type_restore, $configuration)
    {
        //affectation_igv_type_id
        //02 afecta 03 no afecta
        //global_discount_type_id
        $configuration->legend_forest_to_xml = false;
        $configuration->affectation_igv_type_id = 10;
        $configuration->global_discount_type_id = "02";
        if ($type_restore == 'exonerated') {
            $configuration->affectation_igv_type_id = 20;
            $configuration->global_discount_type_id = "03";
        } else if ($type_restore == 'exonerated_plus') {
            $configuration->affectation_igv_type_id = 20;
            $configuration->global_discount_type_id = "03";
            $configuration->legend_forest_to_xml = true;
        }
        $configuration->save();
        self::clearCache();
    }
    public function restore_default(Request $request)
    {
        $type_restore = $request->type_restore;
        $first_configuration = Configuration::first();
        Configuration::where('id', '!=', $first_configuration->id)->delete();
        $new_configuration = new Configuration();
        $fields = [
            'send_auto',
            'plan',
            'phone_whatsapp',
            'apk_url',
            'visual',
            'date_time_start',
            'quantity_documents',
            'quantity_sales_notes',
            'include_igv',
            'product_only_location',
            'terms_condition',
            'terms_condition_sale',
            'header_image',
            'login',
            'finances',
            'smtp_encryption',
            'smtp_password',
            'smtp_user',
            'smtp_host',
            'created_at',
            'updated_at',
            'url_apiruc',
            'token_apiruc',
            'top_menu_a_id',
            'top_menu_b_id',
            'top_menu_c_id',
            'top_menu_d_id',
            'show_price_barcode_ticket',
            'pdf_footer_images',
            'background_image',
            'shortcuts',
            'main_warehouse',
            'purchase_affectation_igv_type_id',
            'terms_condition_dispatches',
            'whatsapp_document_message',
            'show_categories',
            'show_models',
            'show_brands',
        ];

        foreach ($fields as $field) {
            $new_configuration->$field = $first_configuration->$field;
        }
        $new_configuration->default_price_change_item = false;
        $new_configuration->default_purchase_price_change_item = false;
        $new_configuration->save();
        $last_configuration = Configuration::where('id', '!=', $first_configuration->id)->first();
        $attributes = Schema::connection('tenant')->getColumnListing('configurations');
        foreach ($attributes as $attribute) {

            if ($attribute !== 'id') {
                $first_configuration->$attribute = $last_configuration->$attribute;
            }
        }
        $first_configuration->save();
        $this->set_default_configuration_type($type_restore, $first_configuration);
        Configuration::where('id', '!=', $first_configuration->id)->delete();
        self::clearCache();
        return [
            'success' => true,
            'message' => 'Configuración restaurada'
        ];
    }
    public function store_shortcuts()
    {
        $configuration = Configuration::first();
        $configuration->shortcuts = request()->input('shortcuts');
        $configuration->save();
        self::clearCache();
        return [
            'success' => true,
            'message' => 'Configuración actualizada'
        ];
    }
    public function shortcuts()
    {
        return view('tenant.configuration.shortcut');
    }
    public function saveBillOfExchangeTemplate(Request $request)
    {
        $configuration = Configuration::first();
        $bill_of_exchange_template = $request->bill_of_exchange_template;
        $configuration->bill_of_exchange_template = $bill_of_exchange_template;
        $configuration->save();
        self::clearCache();
        return [
            'success' => true,
            'message' => 'Configuración actualizada'
        ];
    }
    public function getBillOfExchangeTemplate()
    {
        $configuration = Configuration::first();
        return [
            'success' => true,
            'bill_of_exchange_template' => $configuration->bill_of_exchange_template
        ];
    }
    public function generateDispatch(Request $request)
    {
        $template = new Template();
        $pdf = new Mpdf();
        $pdf_margin_top = 15;
        $pdf_margin_bottom = 15;
        // $pdf_margin_top = 15;
        $pdf_margin_right = 15;
        // $pdf_margin_bottom = 15;
        $pdf_margin_left = 15;

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
                        DIRECTORY_SEPARATOR . $base_pdf_template .
                        DIRECTORY_SEPARATOR . 'font')
                ]),
                'fontdata' => $fontData + [
                    'custom_bold' => [
                        'R' => $pdf_font_bold . '.ttf',
                    ],
                    'custom_regular' => [
                        'R' => $pdf_font_regular . '.ttf',
                    ],
                ],
                'margin_top'    => $pdf_margin_top,
                'margin_right'  => $pdf_margin_right,
                'margin_bottom' => $pdf_margin_bottom,
                'margin_left'   => $pdf_margin_left,
            ]);
        } else {
            $pdf = new Mpdf([
                'margin_top'    => $pdf_margin_top,
                'margin_right'  => $pdf_margin_right,
                'margin_bottom' => $pdf_margin_bottom,
                'margin_left'   => $pdf_margin_left
            ]);
        }
        $path_css = app_path('CoreFacturalo' . DIRECTORY_SEPARATOR . 'Templates' .
            DIRECTORY_SEPARATOR . 'preprinted_pdf' .
            DIRECTORY_SEPARATOR . $request->base_pdf_template .
            DIRECTORY_SEPARATOR . 'style.css');

        $stylesheet = file_get_contents($path_css);

        // $actions = array_key_exists('actions', $request->inputs)?$request->inputs['actions']:[];
        $actions = [];
        $html = $template->preprintedpdf($request->base_pdf_template, 'dispatch', Company::active(), 'a4');
        $pdf->WriteHTML($stylesheet, HTMLParserMode::HEADER_CSS);
        $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);

        Storage::put('preprintedpdf' . DIRECTORY_SEPARATOR . $request->base_pdf_template . '.pdf', $pdf->output('', 'S'));

        return $request->base_pdf_template;
    }

    public function show($template)
    {
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="file.pdf"'
        ];

        return response()->file(storage_path('app' . DIRECTORY_SEPARATOR . 'preprintedpdf' . DIRECTORY_SEPARATOR . $template . '.pdf'), $headers);
    }

    // public function dispatch(Request $request) {
    //     return 'prueba';

    //     $fact =  DB::connection('tenant')->transaction(function () use($request) {
    //         $facturalo = new Facturalo();
    //         $facturalo->save($request->all());
    //         $facturalo->createXmlUnsigned();
    //         $facturalo->signXmlUnsigned();
    //         $facturalo->createPdf();
    //         $facturalo->senderXmlSignedBill();

    //         return $facturalo;
    //     });

    //     $document = $fact->getDocument();
    //     $response = $fact->getResponse();

    //     return [
    //         'success' => true,
    //         'message' => "Se creo la guía de remisión {$document->series}-{$document->number}",
    //         'data' => [
    //             'id' => $document->id,
    //         ],
    //     ];
    // }

    public function addSeeder()
    {
        $reiniciar = DB::connection('tenant')
            ->table('format_templates')
            ->truncate();
        $archivos = Storage::disk('core')->allDirectories('Templates/pdf');
        $collection = [];
        $valor = [];
        foreach ($archivos as $valor) {
            $line = explode('/', $valor);
            if (count($line) <= 3) {
                array_push($collection, $line);
            }
        }

        foreach ($collection as $insertar) {
            $urls = [
                'guide' => \File::exists(public_path('templates/pdf/' . $insertar[2] . '/image_guide.png')) ? 'templates/pdf/' . $insertar[2] . '/image_guide.png' : '',
                'invoice' => \File::exists(public_path('templates/pdf/' . $insertar[2] . '/image.png')) ? 'templates/pdf/' . $insertar[2] . '/image.png' : 'templates/pdf/default/image.png',
                'ticket' => \File::exists(public_path('templates/pdf/' . $insertar[2] . '/ticket.png')) ? 'templates/pdf/' . $insertar[2] . '/ticket.png' : '',
            ];

            $insertar = DB::connection('tenant')
                ->table('format_templates')
                ->insert([
                    [
                        'formats' => $insertar[2],
                        'urls' => json_encode($urls),
                        'is_custom_ticket' => \File::exists(public_path('templates/pdf/' . $insertar[2] . '/ticket.png')) ? 1 : 0
                    ]
                ]);
        }

        // revisión custom //obsoleto
        // $exists = Storage::disk('core')->exists('Templates/pdf/custom/style.css');
        // if (!$exists) {
        //     Storage::disk('core')->copy('Templates/pdf/default/style.css', 'Templates/pdf/custom/style.css');
        //     Storage::disk('core')->copy('Templates/pdf/default/invoice_a4.blade.php', 'Templates/pdf/custom/invoice_a4.blade.php');
        //     Storage::disk('core')->copy('Templates/pdf/default/partials/footer.blade.php', 'Templates/pdf/custom/partials/footer.blade.php');
        // }

        return [
            'success' => true,
            'message' => 'Configuración actualizada'
        ];
    }

    public function refreshTickets()
    {
        $lists = FormatTemplate::where('is_custom_ticket', true)->get();

        return [
            'success' => true,
            'message' => 'Configuración actualizada'
        ];
    }

    public function getTicketFormats()
    {
        $formats = FormatTemplate::where('is_custom_ticket', true)->get()->transform(function ($row) {
            return $row->getCollectionData();
        });

        return compact('formats');
    }

    public function addPreprintedSeeder()
    {
        $reiniciar = DB::connection('tenant')
            ->table('preprinted_format_templates')
            ->truncate();
        $archivos = Storage::disk('core')->allDirectories('Templates/preprinted_pdf');
        $colection = [];
        $valor = [];
        foreach ($archivos as $valor) {
            $lina = explode('/', $valor);
            if (count($lina) <= 3) {
                array_push($colection, $lina);
            }
        }

        foreach ($colection as $insertar) {
            $insertar = DB::connection('tenant')
                ->table('preprinted_format_templates')
                ->insert(['formats' => $insertar[2]]);
        }

        // revisión custom
        $exists = Storage::disk('core')->exists('Templates/preprinted_pdf/custom/style.css');
        if (!$exists) {
            Storage::disk('core')->copy('Templates/preprinted_pdf/default/style.css', 'Templates/preprinted_pdf/custom/style.css');
            Storage::disk('core')->copy('Templates/preprinted_pdf/default/invoice_a4.blade.php', 'Templates/preprinted_pdf/custom/invoice_a4.blade.php');
            Storage::disk('core')->copy('Templates/preprinted_pdf/default/partials/footer.blade.php', 'Templates/preprinted_pdf/custom/partials/footer.blade.php');
        }

        return [
            'success' => true,
            'message' => 'Configuración actualizada'
        ];
    }

    public function changeFormat(Request $request)
    {
        $establishment = Establishment::find($request->establishment);
        $establishment->template_pdf = $request->formats;
        $establishment->save();

        // $config_format = config(['tenant.pdf_template' => $format->formats]);
        // $fp = fopen(base_path() .'/config/tenant.php' , 'w');
        // fwrite($fp, '<?php return ' . var_export(config('tenant'), true) . ';');
        // fclose($fp);
        return [
            'success' => true,
            'message' => 'Configuración actualizada'
        ];
    }

    public function changeTicketFormat(Request $request)
    {
        $establishment = Establishment::find($request->establishment);
        $establishment->template_ticket_pdf = $request->formats;
        $establishment->save();

        return [
            'success' => true,
            'message' => 'Configuración actualizada'
        ];
    }

    public function getFormats()
    {
        $formats = FormatTemplate::get()->transform(function ($row) {
            return $row->getCollectionData();
        });

        return compact('formats');

        return $formats;
    }

    public function getPreprintedFormats()
    {
        $formats = DB::connection('tenant')->table('preprinted_format_templates')->get();

        return $formats;
    }
    public function saveEstablishmentTicket(Request $request)
    {
        $id = $request->establishment_id;
        $establishment = Establishment::find($id);
        $establishment->template_documents_ticket = $request->template_documents_ticket;
        $establishment->template_sale_notes_ticket = $request->template_sale_notes_ticket;
        $establishment->template_dispatches_ticket = $request->template_dispatches_ticket;
        $establishment->template_quotations_ticket = $request->template_quotations_ticket;
        $establishment->save();


        return [
            'success' => true,
            'message' => 'Configuración actualizada'
        ];
    }
    public function saveEstablishment(Request $request)
    {
        $id = $request->establishment_id;
        $establishment = Establishment::find($id);
        $establishment->template_documents = $request->template_documents;
        $establishment->template_sale_notes = $request->template_sale_notes;
        $establishment->template_dispatches = $request->template_dispatches;
        $establishment->template_quotations = $request->template_quotations;

        $establishment->save();


        return [
            'success' => true,
            'message' => 'Configuración actualizada'
        ];
    }
    public function pdfTemplates()
    {

        $establishments = Establishment::select(['id', 'description', 'template_pdf', 'template_sale_notes', 'template_dispatches', 'template_quotations', 'template_documents'])->get();
        return view('tenant.advanced.pdf_templates')->with('establishments', $establishments);
    }

    public function pdfTicketTemplates()
    {
        $establishments = Establishment::select(['id', 'description', 'template_ticket_pdf', 'template_documents_ticket', 'template_sale_notes_ticket', 'template_dispatches_ticket', 'template_quotations_ticket'])->get();
        return view('tenant.advanced.pdf_ticket_templates')->with('establishments', $establishments);
    }

    public function pdfGuideTemplates()
    {
        return view('tenant.advanced.pdf_guide_templates');
    }

    public function pdfPreprintedTemplates()
    {
        return view('tenant.advanced.pdf_preprinted_templates');
    }

    public function record()
    {
        $configuration = Configuration::first();
        return [
            'data' => $configuration->getCollectionData()
        ];
        $record = new ConfigurationResource($configuration);

        return  $record;
    }

    public function storeWhatsappDocumentMessage(Request $request)
    {
        self::clearCache();
        $configuration = Configuration::first();
        $configuration->whatsapp_document_message = $request->whatsapp_document_message;
        $configuration->save();

        return [
            'success' => true,
            'message' => 'Configuración actualizada',
        ];
    }
    public static function clearCache(){
        CacheTrait::clearCache('configuration');
        CacheTrait::clearCache('public_config');
        CacheTrait::clearCache('cash_payment_methods');
    }
    public function store(ConfigurationRequest $request)


    {

        $cp = Company::query()
            ->select('id', 'number')
            ->first();

        $id = $request->input('id');
        $configuration = Configuration::find($id);
        $configuration->fill($request->all());
        
        if($configuration->all_products){
            $configuration->all_products = DB::connection('tenant')->table('items')->count() <= 100;
        }

        if($configuration->pos_quick_sale){
            DB::connection('tenant')->table('inventory_configurations')->update(['stock_control' => false]);
        }
        
        $configuration->save();


        Cache::forget("{$cp->number}_token_sunat");
        $users_id = User::all()->pluck('id');
        foreach ($users_id as $user_id) {
            Cache::forget("series_by_user_id_{$user_id}");
        }
        self::clearCache();


        return [
            'success' => true,
            'configuration' => $configuration->getCollectionData(),
            'message' => 'Configuración actualizada',
        ];
    }
    public function updateShowEditButton(Request $request)
    {
        $configuration = Configuration::first();
        $configuration->show_edit_button = $request->input('show_edit_button');
        $configuration->save();

        self::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Configuración actualizada'
        ]);
    }

    public function getConfiguration()
    {
        $configuration = Configuration::first();
        return response()->json([
            'show_edit_button' => $configuration->show_edit_button,
        ]);
    }
    public function getShowSalePricePdf()
    {
        $configuration = Configuration::first();
        return response()->json(['show_sale_price_pdf' => $configuration->show_sale_price_pdf]);
    }
    public function updateShowSalePricePdf(Request $request)
    {
        $configuration = Configuration::first();
        $configuration->show_sale_price_pdf = $request->input('show_sale_price_pdf');
        $configuration->save();

        self::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Configuración actualizada'
        ]);
    }
    /**
     * Solo guarda lo sdatos de token para el cliente
     *
     * @param Request $request
     *
     * @return array
     */
    public function storeApiRuc(Request  $request)
    {
        $configuration = Configuration::first();
        if (empty($configuration)) {
            $configuration = new Configuration();
        }
        $configuration->token_apiruc = $request->token_apiruc;
        $configuration->url_apiruc = $request->url_apiruc;

        $configuration->save();

        self::clearCache();

        return [
            'success' => true,
            'configuration' => $configuration->getCollectionData(),
            'message' => 'Configuración actualizada',
        ];
    }

    public function icbper(Request $request)
    {
        DB::connection('tenant')->transaction(function () use ($request) {
            $id = $request->input('id');
            $configuration = Configuration::find($id);
            $configuration->amount_plastic_bag_taxes = $request->amount_plastic_bag_taxes;
            $configuration->save();

            self::clearCache();

            $items = Item::get(['id', 'amount_plastic_bag_taxes']);

            foreach ($items as $item) {
                $item->amount_plastic_bag_taxes = $configuration->amount_plastic_bag_taxes;
                $item->update();
            }
        });

        return [
            'success' => true,
            'message' => 'Configuración actualizada'
        ];
    }

    public function tables()
    {
        $warehouses = Warehouse::where('active', true)->get();
        $affectation_igv_types = AffectationIgvType::whereActive()->get();
        $global_discount_types = ChargeDiscountType::whereIn('id', ['02', '03'])->whereActive()->get();

        return compact('affectation_igv_types', 'global_discount_types', 'warehouses');
    }

    public function visualDefaults()
    {
        $defaults = [
            'bg'       => 'light',
            'header'   => 'light',
            'sidebars' => 'light',
        ];
        $configuration = Configuration::first();
        $configuration->visual = $defaults;
        $configuration->save();

        self::clearCache();

        return [
            'success' => true,
            'message' => 'Configuración actualizada'
        ];
    }

    public function visualSettings(Request $request)
    {
        $visuals = [
            'bg'       => $request->bg,
            'header'   => $request->header,
            'sidebars' => $request->sidebars,
            'navbar' => $request->navbar,
            'sidebar_theme' => $request->sidebar_theme
        ];

        $configuration = Configuration::find(1);
        $configuration->visual = $visuals;
        $configuration->save();

        self::clearCache();

        return [
            'success' => true,
            'message' => 'Configuración actualizada'
        ];
    }

    public function getSystemPhone()
    {
        // $configuration = Configuration::first();
        // $ws = $configuration->enable_whatsapp;

        // $current = url('/phone');
        // $parse_current = parse_url($current);
        // $explode_current = explode('.', $parse_current['host']);
        // $app_url = config('app.url');
        // if(!array_key_exists('port', $parse_current)){
        //     $path = $app_url.$parse_current['path'];
        // }else{
        //     $path = $app_url.':'.$parse_current['port'].$parse_current['path'];
        // }

        // $http = new Client(['verify' => false]);
        // $response = $http->request('GET', $path);
        // if($response->getStatusCode() == '200'){
        //     $body = $response->getBody();

        //     $configuration->phone_whatsapp = $body;
        //     $configuration->save();
        // }
        // return 'error';
    }
    public function uploadBackground(Request $request)
    {
        if ($request->hasFile('file')) {
            $configuration = Configuration::first();

            $file = $request->file('file');
            $ext = $file->getClientOriginalExtension();
            $name = date('Ymd') . '_bg_' . $configuration->id . '.' . $ext;

            request()->validate(['file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048']);

            UploadFileHelper::checkIfValidFile($name, $file->getPathName(), true);

            $file->storeAs('public/uploads/header_images', $name);

            $configuration->background_image = $name;

            $configuration->save();

            self::clearCache();

            return [
                'success' => true,
                'message' => __('app.actions.upload.success'),
                'name'    => $name,
            ];
        }

        return [
            'success' => false,
            'message' => __('app.actions.upload.error'),
        ];
    }
    public function uploadOrderPurchaseLogo(Request $request)
    {
        if ($request->hasFile('file')) {
            $configuration = Configuration::first();

            $file = $request->file('file');
            $ext = $file->getClientOriginalExtension();
            $name = date('Ymd') . '_purchase_order_logo_' . $configuration->id . '.' . $ext;

            request()->validate(['file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048']);

            UploadFileHelper::checkIfValidFile($name, $file->getPathName(), true);

            $file->storeAs('public/uploads/header_images', $name);

            $configuration->order_purchase_logo = $name;

            $configuration->save();

            self::clearCache();

            return [
                'success' => true,
                'message' => __('app.actions.upload.success'),
                'name'    => $name,
            ];
        }

        return [
            'success' => false,
            'message' => __('app.actions.upload.error'),
        ];
    }
    public function uploadFile(Request $request)
    {
        if ($request->hasFile('file')) {
            $configuration = Configuration::first();

            $file = $request->file('file');
            $ext = $file->getClientOriginalExtension();
            $name = date('Ymd') . '_' . $configuration->id . '.' . $ext;

            request()->validate(['file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048']);

            UploadFileHelper::checkIfValidFile($name, $file->getPathName(), true);

            $file->storeAs('public/uploads/header_images', $name);

            $configuration->header_image = $name;

            $configuration->save();

            self::clearCache(); 

            return [
                'success' => true,
                'message' => __('app.actions.upload.success'),
                'name'    => $name,
            ];
        }

        return [
            'success' => false,
            'message' => __('app.actions.upload.error'),
        ];
    }

    public function changeMode()
    {
        $configuration = Configuration::first();
        $visual = $configuration->visual;
        $visual->sidebar_theme = $visual->bg === 'dark' ? 'white' : 'dark';
        $visual->bg = $visual->bg === 'dark' ? 'white' : 'dark';
        $configuration->visual = $visual;
        $configuration->save();
        
        self::clearCache();

        return redirect()->back();
    }


    public function apiruc()
    {
        $configuration = Configuration::first();
        return [
            'url_apiruc' => $configuration->url_apiruc,
            'token_apiruc' => $configuration->token_apiruc,
            'token_false' => !$configuration->UseCustomApiPeruToken(),
        ];
    }

    private function getMenu()
    {
        $configuration = Configuration::first();
        return $menus = [
            'top_menu_a' => $configuration->top_menu_a_id ? $configuration->top_menu_a : '',
            'top_menu_b' => $configuration->top_menu_b_id ? $configuration->top_menu_b : '',
            'top_menu_c' => $configuration->top_menu_c_id ? $configuration->top_menu_c : '',
            'top_menu_d' => $configuration->top_menu_d_id ? $configuration->top_menu_d : '',
        ];
    }

    public function visualGetMenu()
    {
        $modules = ModuleLevel::where([['route_name', '!=', null], ['label_menu', '!=', null]])->get();

        return [
            'modules' => $modules,
            'menu' => $this->getMenu()
        ];
    }

    public function visualSetMenu(Request $request)
    {
        $configuration = Configuration::first();
        $configuration->top_menu_a_id = $request->menu_a;
        $configuration->top_menu_b_id = $request->menu_b;
        $configuration->top_menu_c_id = $request->menu_c;
        $configuration->top_menu_d_id = $request->menu_d;
        $configuration->save();

        self::clearCache();

        return [
            'success' => true,
            'menu' => $this->getMenu(),
            'message' => 'Configuración actualizada',
        ];
    }

    public function visualUploadSkin(Request $request)
    {
        if ($request->file->getClientMimeType() != 'text/css') {
            return [
                'success' => false,
                'message' =>  'Tipo de archivo no permitido',
            ];
        }
        if (Storage::disk('public')->exists('skins' . DIRECTORY_SEPARATOR . $request->file->getClientOriginalName())) {
            return [
                'success' => false,
                'message' =>  'Archivo ya existe',
            ];
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            $file_content = file_get_contents($file->getRealPath());
            $filename = $file->getClientOriginalName();
            $name = pathinfo($file->getClientOriginalName());

            UploadFileHelper::checkIfValidCssFile($filename, $file->getPathName(), 'css', ['text/css', 'text/plain']);

            Storage::disk('public')->put('skins' . DIRECTORY_SEPARATOR . $filename, $file_content);

            $skin = new Skin;
            $skin->filename = $filename;
            $skin->name = $name['filename'];
            $skin->save();

            self::clearCache();

            $skins = Skin::all();
            return [
                'success' => true,
                'message' =>  'Archivo cargado exitosamente',
                'skins' => $skins
            ];
        }
        return [
            'success' => false,
            'message' =>  __('app.actions.upload.error'),
        ];
    }

    public function visualDeleteSkin(Request $request)
    {
        $config = Configuration::first();
        if ($config->skin_id == $request->id) {
            return [
                'success' => false,
                'message' => 'No se puede eliminar el Tema actual'
            ];
        }


        $skin = Skin::find($request->id);
        Storage::disk('public')->delete('skins' . DIRECTORY_SEPARATOR . $skin->filename);
        $skin->delete();

        $skins = Skin::all();

        return [
            'success' => true,
            'message' =>  'Tema eliminado correctamente',
            'skins' => $skins
        ];
    }


    /**
     *
     * Consulta de imagenes footer
     *
     * @return array
     */
    public function getPdfFooterImages()
    {
        return Configuration::first()->getDataPdfFooterImages();
    }


    /**
     *
     * Cargar imagenes para pdf footer
     *
     * @param  Request $request
     * @return array
     */
    public function pdfFooterImages(Request $request)
    {
        $images = $request->images;
        $data = [];
        $configuration = Configuration::first();
        $folder = 'pdf_footer_images';

        foreach ($images as $index => $image) {
            $temp_path = $image['temp_path'] ?? null;

            if ($temp_path) {
                $old_filename = $image['filename'];
                UploadFileHelper::checkIfValidFile($old_filename, $temp_path, true);
                $first_old_filename = explode('.', $old_filename)[0];
                $filename = UploadFileHelper::uploadImageFromTempFile($folder, $old_filename, $temp_path, "{$first_old_filename}_{$index}_", true);
            } else {
                $filename = $image['name'];
            }

            $data[] = [
                'filename' => $filename,
            ];
        }

        $configuration->pdf_footer_images = $data;
        $configuration->update();

        self::clearCache();

        return $this->generalResponse(true, 'Proceso realizado correctamente.');
    }

    public function getAppConfigurationTaxoRole()
    {
        $configuration = AppConfigurationTaxo::get()
        ->transform(function($name){
            return [
                'id' => $name->id,
                'menu' => $name->menu,
                'route' => $name->route,
                'is_visible' => $name->is_visible,
                'roles' => $name->roles->map(function($role){
                    return [
                        'id' => $role->id,
                        'role_id' => $role->role_id,
                        'is_visible' => $role->is_visible,
                    ];
                }),
            ];
        });

        
        return response()->json($configuration);
    }

    public function updateAppConfigurationTaxo(Request $request)
    {
        $configuration = AppConfigurationTaxo::find($request->id);
        $configuration->is_visible = $request->is_visible;
        $configuration->save();

        self::clearCache();
        return response()->json($configuration);
    }

    public function updateAppConfigurationTaxoRole(Request $request)
    {
        $configuration = AppConfigurationTaxoRole::find($request->id);
        $configuration->is_visible = $request->is_visible;
        $configuration->save();

        self::clearCache();
        return response()->json($configuration);
    }

    /**
     * Obtener todos los temas personalizados
     */
    public function getCustomThemes()
    {
        $themes = CustomColorTheme::orderBy('created_at', 'desc')->get()->map(function ($theme) {
            return [
                'id' => $theme->id,
                'name' => $theme->name,
                'primary' => $theme->primary,
                'secondary' => $theme->secondary,
                'tertiary' => $theme->tertiary,
                'quaternary' => $theme->quaternary,
                'is_light' => $theme->is_light,
                'is_default' => $theme->is_default,
                'svg' => $theme->generateSvg(),
            ];
        });

        return response()->json([
            'success' => true,
            'themes' => $themes
        ]);
    }

    /**
     * Guardar o actualizar un tema personalizado
     */
    public function saveCustomTheme(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'primary' => 'required|string|size:7',
            'secondary' => 'required|string|size:7',
            'tertiary' => 'required|string|size:7',
            'quaternary' => 'required|string|size:7',
            'is_light' => 'required|boolean',
        ]);

        $userId = auth()->id();

        if ($request->id) {
            $theme = CustomColorTheme::findOrFail($request->id);
            $theme->update($request->only(['name', 'primary', 'secondary', 'tertiary', 'quaternary', 'is_light']));
        } else {
            $theme = CustomColorTheme::create([
                'name' => $request->name,
                'primary' => $request->primary,
                'secondary' => $request->secondary,
                'tertiary' => $request->tertiary,
                'quaternary' => $request->quaternary,
                'is_light' => $request->is_light,
                'user_id' => $userId,
            ]);
        }

        self::clearCache();

        return response()->json([
            'success' => true,
            'message' => $request->id ? 'Tema actualizado correctamente' : 'Tema creado correctamente',
            'theme' => [
                'id' => $theme->id,
                'name' => $theme->name,
                'primary' => $theme->primary,
                'secondary' => $theme->secondary,
                'tertiary' => $theme->tertiary,
                'quaternary' => $theme->quaternary,
                'is_light' => $theme->is_light,
                'svg' => $theme->generateSvg(),
            ]
        ]);
    }

    /**
     * Eliminar un tema personalizado
     */
    public function deleteCustomTheme($id)
    {
        $theme = CustomColorTheme::findOrFail($id);

        // Verificar si es el tema por defecto
        if ($theme->is_default) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el tema por defecto'
            ], 400);
        }

        $theme->delete();

        self::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Tema eliminado correctamente'
        ]);
    }

    /**
     * Aplicar un tema personalizado
     */
    public function applyCustomTheme(Request $request, $id)
    {
        $theme = CustomColorTheme::findOrFail($id);

        // Aquí podrías guardar en localStorage del frontend
        // o en la configuración del usuario
        self::clearCache();

        return response()->json([
            'success' => true,
            'message' => 'Tema aplicado correctamente',
            'theme_id' => $theme->id,
            'data_color' => "custom-{$theme->id}"
        ]);
    }
}
