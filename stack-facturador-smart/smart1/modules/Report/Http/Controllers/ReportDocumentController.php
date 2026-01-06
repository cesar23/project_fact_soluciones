<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\Company;
use App\Models\Tenant\Document;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\SaleNote;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Item\Models\Category;
use Modules\Report\Exports\DocumentExport;
use Modules\Report\Http\Resources\DocumentCollection;
use Modules\Report\Http\Resources\SaleNoteCollection;
use Modules\Report\Traits\ReportTrait;
use App\Http\Controllers\Tenant\EmailController;
use Modules\Report\Mail\DocumentEmail;
use Mpdf\Mpdf;
use Modules\Report\Jobs\ProcessDocumentReport;
use App\Models\Tenant\DownloadTray;
use Hyn\Tenancy\Models\Hostname;
use App\Models\System\Client;

use Maatwebsite\Excel\Excel as BaseExcel;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\JobReportTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\BusinessTurn\Models\AgencyTransport;
use Modules\Report\Exports\DocumentExportStandard;
use Modules\Report\Exports\DocumentProductExport;

// Para exportManual PDF con Mpdf
use Mpdf\HTMLParserMode;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Illuminate\Support\Facades\Log;

class ReportDocumentController extends Controller
{
    use ReportTrait, JobReportTrait;



    public function productsToExcel($id,$document_type_id){
        $model =  $document_type_id == '80' ? SaleNote::class : Document::class;
        $document = $model::findOrFail($id);
        $items = $document->items;
        $establishment = Establishment::find(auth()->user()->establishment_id);
        $company = Company::active();
        $number = $document->series.'-'.$document->number;
        $date = $document->date_of_issue->format('Y-m-d');
        $documentExport = new DocumentProductExport();
        $documentExport->items($items)
                        ->company($company)
                        ->establishment($establishment)
                        ->number($number)
                        ->date($date);
            
        return $documentExport->download('Producto_de_comprobante_' . Carbon::now() . '.xlsx');
    }
    public function filter()
    {

        $document_types = DocumentType::whereIn('id', [
            '01', // factura
            '03', // boleta
            '07', // nota de credito
            '08', // nota de debito
            '80', // nota de venta
        ])->get();

        $persons = $this->getPersons('customers');
        $sellers = $this->getSellers();
        $agencies_transport =  AgencyTransport::all();
        $establishments = Establishment::whereActive()->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'name' => $row->description
            ];
        });
        $users = $this->getUsers();

        return compact(
            'agencies_transport',
            'document_types',
            'establishments',
            'persons',
            'sellers',
            'users'
        );
    }


    public function index()
    {
        return view('report::documents.index');
    }

    public function records(Request $request)
    {
        $documentTypeId = "01";
        if ($request->has('document_type_id')) {
            $documentTypeId = str_replace('"', '', $request->document_type_id);
        }
        $documentType = DocumentType::find($documentTypeId);
        if (null === $documentType) {
            $documentType = new DocumentType();
        }

        $classType = $documentType->getCurrentRelatiomClass();

        $records = $this->getRecords($request->all(), $classType);

        if ($classType == SaleNote::class) {
            return new SaleNoteCollection($records->paginate(config('tenant.items_per_page')));
        }
        return new DocumentCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function pdf_quotations(Request $request)
    {
        set_time_limit(1800); // Maximo 30 minutos
        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : auth()->user()->establishment;
        $documentTypeId = "01";
        if ($request->has('document_type_id')) {
            $documentTypeId = str_replace('"', '', $request->document_type_id);
        }
        $documentType = DocumentType::find($documentTypeId);
        if (null === $documentType) {
            $documentType = new DocumentType();
        }

        $classType = $documentType->getCurrentRelatiomClass();
        $records = $this->getRecords($request->all(), $classType);
        $records = $records->get();

        $filters = $request->all();

        $pdf = Pdf::loadView('report::documents.report_pdf_quotations', compact("records", "company", "establishment", "filters"))
            ->setPaper('a4', 'landscape');

        $filename = 'Reporte_Ventas_' . date('YmdHis');

        return $pdf->stream($filename . '.pdf');
    }
    public function pdf(Request $request)
    {
        set_time_limit(1800); // Maximo 30 minutos
        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : auth()->user()->establishment;
        $documentTypeId = "01";
        if ($request->has('document_type_id')) {
            $documentTypeId = str_replace('"', '', $request->document_type_id);
        }
        $documentType = DocumentType::find($documentTypeId);
        if (null === $documentType) {
            $documentType = new DocumentType();
        }

        $classType = $documentType->getCurrentRelatiomClass();
        $records = $this->getRecords($request->all(), $classType);
        $records = $records->get();

        $filters = $request->all();

        $pdf = Pdf::loadView('report::documents.report_pdf_standard', compact("records", "company", "establishment", "filters"))
            ->setPaper('a4', 'landscape');

        $filename = 'Reporte_Ventas_' . date('YmdHis');

        return $pdf->stream($filename . '.pdf');
    }


    public function pdfSimple(Request $request)
    {
        set_time_limit(1800); // Maximo 30 minutos
        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : auth()->user()->establishment;
        $documentTypeId = "01";
        if ($request->has('document_type_id')) {
            $documentTypeId = str_replace('"', '', $request->document_type_id);
        }
        $documentType = DocumentType::find($documentTypeId);
        if (null === $documentType) {
            $documentType = new DocumentType();
        }

        $classType = $documentType->getCurrentRelatiomClass();
        $records = $this->getRecords($request->all(), $classType);
        $records = $records->get();

        $filters = $request->all();

        $pdf = PDF::loadView('report::documents.report_pdf_simple', compact("records", "company", "establishment", "filters"))
            ->setPaper('a4', 'landscape');

        $filename = 'Reporte_Ventas_Simple' . date('YmdHis');

        return $pdf->download($filename . '.pdf');
    }


    public function excel(Request $request)
    {
        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : auth()->user()->establishment;

        $documentTypeId = "01";
        if ($request->has('document_type_id')) {
            $documentTypeId = str_replace('"', '', $request->document_type_id);
        }
        $documentType = DocumentType::find($documentTypeId);
        if (null === $documentType) {
            $documentType = new DocumentType();
        }

        $classType = $documentType->getCurrentRelatiomClass();
        $records = $this->getRecords($request->all(), $classType);
        $records = $records->get();
        $filters = $request->all();
        
        //get categories
        $categories = [];
        $categories_services = [];

        if ($request->include_categories == "true") {
            $categories = $this->getCategories($records, false);
            $categories_services = $this->getCategories($records, true);
        }

        $documentExport = new DocumentExportStandard();
        $documentExport
            ->records($records)
            ->company($company)
            ->establishment($establishment)
            ->filters($filters)
            ->categories($categories)
            ->categories_services($categories_services);
        // return $documentExport->view();
        return $documentExport->download('Reporte_Ventas_' . Carbon::now() . '.xlsx');
    }


    public function exportManual(Request $request){
        ini_set('max_execution_time', 3000);
        ini_set('memory_limit', '3072M');
        // Decodificar directamente a objeto. Si el JSON es inválido o no es un objeto, será null.
        $columns = json_decode($request->input('columns')); 
        $format = $request->input('format', 'xlsx'); 
        $filters = $request->all();

        $company = Company::first(); 
        $establishment_id = $filters['establishment_id'] ?? auth()->user()->establishment_id;
        $establishment = Establishment::findOrFail($establishment_id);

        $classType = $this->getFilterClassType_manual($request); 
        $records_query = $this->getRecords_manual($filters, $classType); // Query Builder

        $categories = collect([]); // Inicializar como colección vacía por defecto
        $categories_services = collect([]); // Inicializar como colección vacía por defecto

        if ($format == 'xlsx') {
            $records_collection = $records_query->get(); // Materializar colección para Excel

            if (isset($filters['include_categories']) && ($filters['include_categories'] == "true" || $filters['include_categories'] == "1")) {
                if (!$records_collection->isEmpty()) { 
                     $categories = $this->getCategories_manual($records_collection, false);
                     $categories_services = $this->getCategories_manual($records_collection, true);
                }
            }

            $documentExportManual = new \Modules\Report\Exports\DocumentExportManual(); 
            
            $enabled_sales_agents = \App\Models\Tenant\Configuration::getRecordIndividualColumn('enabled_sales_agents');

            $documentExportManual
                ->records($records_collection) // Pasar la colección completa
                ->company($company)
                ->establishment($establishment)
                ->filters($filters)
                ->columns($columns) 
                ->categories($categories)
                ->categories_services($categories_services)
                ->enabled_sales_agents($enabled_sales_agents);

            $filename_excel = 'Reporte_Manual_Coleccion_' . \Carbon\Carbon::now()->format('YmdHis') . '.xlsx';
            return $documentExportManual->download($filename_excel);

        } elseif ($format == 'pdf') {
            $records_cursor = $records_query->cursor(); // Usar cursor para PDF (LazyCollection)

            // Solo calcular categorías si se solicitan y hay registros (el cursor no está vacío).
            // getCategories_manual ahora itera, por lo que necesitamos pasarle una nueva instancia del cursor para cada llamada
            // o una colección si los datos se van a iterar múltiples veces y el cursor no se puede "rebobinar" fácilmente.
            if (isset($filters['include_categories']) && ($filters['include_categories'] == "true" || $filters['include_categories'] == "1")) {
                // Para verificar si el cursor tiene datos sin consumirlo completamente (lo cual haria un ->collect()->isEmpty()),
                // se puede intentar obtener el primer elemento. Si no hay, está vacío.
                // Sin embargo, simplemente pasar el cursor a getCategories_manual y dejar que itere es más simple
                // si getCategories_manual maneja correctamente un iterable vacío.
                // Para asegurar que cada llamada a getCategories_manual no interfiere con la otra o con la vista,
                // se le pasa una nueva instancia del cursor.
                $categories = $this->getCategories_manual($records_query->cursor(), false);
                $categories_services = $this->getCategories_manual($records_query->cursor(), true);
            }

            set_time_limit(1800);

            // Pasar $records_cursor a la vista. La vista debe usar foreach.
            // También pasar las categorías calculadas.
            $html = view('report::documents.report_pdf', compact("records_cursor", "company", "establishment", "filters", "columns", "categories", "categories_services"))->render();
            
            $base_template = $establishment->template_pdf ?? 'default';

            $defaultConfig = (new ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];
            $defaultFontConfig = (new FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];
            
            $pdf_font_regular = config('tenant.pdf_name_regular');
            $pdf_font_bold = config('tenant.pdf_name_bold');

            $mpdf_config = [
                'format' => 'A4-L',
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
                ],
                'margin_top' => 15, 
                'margin_right' => 15,
                'margin_bottom' => 15,
                'margin_left' => 15,
            ];

            $mpdf = new Mpdf($mpdf_config);

            $path_css = app_path('CoreFacturalo' . DIRECTORY_SEPARATOR . 'Templates' .
                DIRECTORY_SEPARATOR . 'pdf' .
                DIRECTORY_SEPARATOR . $base_template .
                DIRECTORY_SEPARATOR . 'style.css');

            if (file_exists($path_css)) {
                $stylesheet = file_get_contents($path_css);
                $mpdf->WriteHTML($stylesheet, HTMLParserMode::HEADER_CSS);
            } else {
                Log::warning("CSS file not found for PDF report: {$path_css}");
            }

            $mpdf->WriteHTML($html, HTMLParserMode::HTML_BODY);
            
            $filename_pdf = 'Reporte_Manual_' . Carbon::now()->format('YmdHis') . '.pdf';
            $mpdf->Output($filename_pdf, \Mpdf\Output\Destination::DOWNLOAD);
            exit;
        }

        // Esto es si no es ni xlsx ni pdf, o si la lógica de xlsx original se quiere mantener como fallback
        // Si la idea es que exportManual solo maneje xlsx por colección y pdf, este bloque podría eliminarse o ajustarse.
        // $documentExport = new DocumentExport(); 
        // ... (configuración para DocumentExport original)
        // return $documentExport->download($filename_excel_original);
        
        return response()->json(['message' => 'Formato no soportado o error en la generación del reporte.'], 400);
    }
    /**
     * 
     * Generar reportes en cola
     *
     * @param  Request $request
     * @return array
     */
    public function export(Request $request)
    {
        $host = $request->getHost();
        $columns = json_decode($request->columns);

        $website = $this->getTenantWebsite();
        $user = $this->getCurrentUser();
        $tray = $this->createDownloadTray($user->id, 'REPORT', $request->input('format'), 'Reporte Documentos - Ventas');

        $filters = $request->all();
        $this->setFiltersForJobReport($filters, $user, $request);

        ProcessDocumentReport::dispatch($tray->id, $website->id, $filters, $columns);

        return $this->getJobResponse();

        /*
        $tray = DownloadTray::create([
            'user_id' => auth()->user()->id,
            'module' => 'DOCUMENTS',
            'format' => $request->input('format'),
            'date_init' => date('Y-m-d H:i:s'),
            'type' => 'Reporte Ventas Documentos'
        ]);

        $trayId = $tray->id;

        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : auth()->user()->establishment;

        $documentTypeId = "01";
        if ($request->has('document_type_id')) {
            $documentTypeId = str_replace('"', '', $request->document_type_id);
        }
        $documentType = DocumentType::find($documentTypeId);
        if (null === $documentType) {
            $documentType = new DocumentType();
        }

        $classType = $documentType->getCurrentRelatiomClass();

        $records = $this->getRecords($request->all(), $classType);
        $records= $records->get();

        //get categories
        $categories = [];
        $categories_services = [];

        if($request->include_categories == "true"){
            $categories = $this->getCategories($records, false);
            $categories_services = $this->getCategories($records, true);
        }
        */

        /*
        return  [
            'success' => true,
            'message' => 'El reporte se esta procesando; puede ver el proceso en bandeja de descargas.'
        ];
        */
    }


    /**
     * 
     * Asignar datos para filtros en query
     *
     * @param  mixed $filters
     * @param  mixed $user
     * @param  mixed $request
     * @return void
     */
    private function setFiltersForJobReport(&$filters, $user, $request)
    {
        $filters['establishment_id_for_format'] = $filters['establishment_id'] ?? $user->establishment_id;
        $filters['class_type_records'] = $this->getFilterClassType($request);
        $filters['session_user_id'] = $user->id;
    }


    /**
     * 
     * Retorna modelo (transaccion) dependiendo del tipo de documento seleccionado
     *
     * @param  Request $request
     * @return string
     */
    private function getFilterClassType($request)
    {
        $documentTypeId = "01";
        if ($request->has('document_type_id')) {
            $value = $request->document_type_id;
            if (is_string($value)) {
                $documentTypeId = str_replace('"', '', $value);
            } else {
                $documentTypeId = $value; // Asumir que ya está limpio si no es string
            }
        }

        $documentType = DocumentType::find($documentTypeId);
        if (null === $documentType) {
            $documentType = new DocumentType();
        }

        return $documentType->getCurrentRelatiomClass();
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

    public function email(Request $request)
    {
        $request->validate(
            ['email' => 'required']
        );
        $data = $request->data;
        $columns = $request->columns;
        $company = Company::active();
        $email = $request->input('email');

        $mailable = new DocumentEmail($company, $this->getPdf($data, $columns), $this->getExcel($data, $columns));
        $sendIt = EmailController::SendMail($email, $mailable);

        return [
            'success' => true
        ];
    }

    private function getPdf($request, $columns, $format = 'ticket', $mm = null)
    {
        $reques = json_decode(json_encode($request, JSON_FORCE_OBJECT));
        set_time_limit(1800); // Maximo 30 minutos
        $columns = json_decode(json_encode($columns));
        $company = Company::first();
        $establishment = ($reques->establishment_id) ? Establishment::findOrFail($reques->establishment_id) : auth()->user()->establishment;
        $documentTypeId = "01";
        if ($reques->document_type_id) {
            $documentTypeId = str_replace('"', '', $reques->document_type_id);
        }
        $documentType = DocumentType::find($documentTypeId);
        if (null === $documentType) {
            $documentType = new DocumentType();
        }

        $classType = $documentType->getCurrentRelatiomClass();
        $records = $this->getRecords($request, $classType);
        $records = $records->get();

        $filters = $request;

        $quantity_rows = 30; //$cash->cash_documents()->count();

        $width = 78;
        if ($mm != null) {
            $width = $mm - 2;
        }

        $view = view('report::documents.report_pdf', compact("records", "company", "establishment", "filters", "columns"));
        $html = $view->render();
        $pdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
        ]);
        $pdf->WriteHTML($html);

        return $pdf->output('', 'S');
    }


    private function getExcel($request, $columns)
    {
        $reques = json_decode(json_encode($request, JSON_FORCE_OBJECT));
        set_time_limit(1800); // Maximo 30 minutos
        $columns = json_decode(json_encode($columns));
        $company = Company::first();
        $establishment = ($reques->establishment_id) ? Establishment::findOrFail($reques->establishment_id) : auth()->user()->establishment;
        $documentTypeId = "01";
        if ($reques->document_type_id) {
            $documentTypeId = str_replace('"', '', $reques->document_type_id);
        }
        $documentType = DocumentType::find($documentTypeId);
        if (null === $documentType) {
            $documentType = new DocumentType();
        }

        $classType = $documentType->getCurrentRelatiomClass();
        $records = $this->getRecords($request, $classType);
        $records = $records->get();

        $filters = $request;

        $categories = [];
        $categories_services = [];

        if ($reques->include_categories == "true") {
            $categories = $this->getCategories($records, false);
            $categories_services = $this->getCategories($records, true);
        }
        $documentExport = new DocumentExport();
        $documentExport
            ->records($records)
            ->company($company)
            ->establishment($establishment)
            ->filters($filters)
            ->categories($categories)
            ->categories_services($categories_services)
            ->columns($columns);
        $attachment = Excel::raw(
            $documentExport,
            BaseExcel::XLSX
        );

        return $attachment;
    }

    // --- Métodos _manual para exportManual ---

    private function getFilterClassType_manual(Request $request)
    {
        // Optimización: Esta función es bastante específica. 
        // Se mantiene la lógica original ya que es robusta para extraer y limpiar document_type_id.
        // El contexto de "manual" aquí es para aislar su uso por exportManual.
        $documentTypeId = "01"; // Valor por defecto, igual que en el original
        if ($request->has('document_type_id')) {
            $value = $request->document_type_id;
            if (is_string($value)) {
                $documentTypeId = str_replace('"', '', $value);
            } else {
                $documentTypeId = $value; // Asumir que ya está limpio si no es string
            }
        }

        $documentType = DocumentType::find($documentTypeId);
        if (null === $documentType) {
            // Considerar si para exportManual este default es siempre seguro
            // o si debería lanzar una excepción si el tipo de documento es crucial y no se encuentra.
            $documentType = new DocumentType(); 
        }

        return $documentType->getCurrentRelatiomClass();
    }

    private function getRecords_manual(array $filters, string $classType)
    {
        // Optimización: Este método actualmente actúa como un wrapper para $this->getRecords (del ReportTrait).
        // La optimización real de la consulta de registros requeriría modificar la lógica en ReportTrait
        // o redefinirla aquí si se conocen los requisitos exactos y simplificados para exportManual.
        // Por ejemplo, si exportManual siempre necesita ciertos campos o relaciones,
        // se podría construir una consulta más específica.
        // Por ahora, se delega al método genérico que devuelve el Query Builder.
        return $this->getRecords($filters, $classType);
    }

    private function getCategories_manual(iterable $records, $is_service) // Acepta iterable
    {
        // Optimización: La lógica original es eficiente para recolectar y obtener categorías únicas.
        // Se mantiene la lógica. El contexto "manual" es para aislar su uso.
        // Añadidas comprobaciones de nulidad para robustez y evitar consulta vacía.

        $aux_categories = collect([]);

        foreach ($records as $document) { // Funciona con Collection y LazyCollection
            if ($document && property_exists($document, 'items') && $document->items) {
                $id_categories = $document->items->filter(function ($row) use ($is_service) {
                    return $row && 
                           property_exists($row, 'relation_item') && $row->relation_item && 
                           property_exists($row, 'item') && $row->item &&
                           property_exists($row->relation_item, 'category_id') && // Verificar que category_id existe
                           (($is_service) ? (!is_null($row->relation_item->category_id) && $row->item->unit_type_id === 'ZZ') 
                                         : !is_null($row->relation_item->category_id));
                })->pluck('relation_item.category_id');

                foreach ($id_categories as $value) {
                    $aux_categories->push($value);
                }
            }
        }

        if ($aux_categories->isEmpty()) {
            return collect([]); // Evitar consulta innecesaria si no hay IDs.
        }

        return Category::whereIn('id', $aux_categories->unique()->toArray())->get();
    }
}
